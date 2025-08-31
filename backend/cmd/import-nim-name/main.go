package main

import (
	"bufio"
	"context"
	"database/sql"
	"encoding/csv"
	"errors"
	"flag"
	"fmt"
	"io"
	"os"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"pointmarket/backend/internal/store/gen"
	"pointmarket/backend/internal/utils"
	"strings"
)

type rowData struct {
	StudentID string
	Name      string
}

func main() {
	var (
		filePath      string
		emailDomain   string
		programName   string
		programIDFlag int64
		cohortYear    int
		role          string
		status        string
		defaultPass   string
		dryRun        bool
	)

	flag.StringVar(&filePath, "file", "", "Path to CSV file with header (NIM/NPM and NAMA)")
	flag.StringVar(&emailDomain, "email-domain", "", "Email domain for generated emails (e.g., student.example.ac.id)")
	flag.StringVar(&programName, "program-name", "", "Program name to assign (e.g., \"D4 Teknik Informatika\")")
	flag.Int64Var(&programIDFlag, "program-id", 0, "Program ID to assign (overrides -program-name if set)")
	flag.IntVar(&cohortYear, "cohort-year", 0, "Cohort year to set for all rows (optional)")
	flag.StringVar(&role, "role", "siswa", "Role for created users")
	flag.StringVar(&status, "status", "active", "Student status (active/leave/graduated/dropped)")
	flag.StringVar(&defaultPass, "password", "", "Default password. If empty, uses NIM.")
	flag.BoolVar(&dryRun, "dry-run", false, "Validate and print actions; do not write to DB")
	flag.Parse()

	if filePath == "" {
		fatalf("-file is required")
	}
	if emailDomain == "" {
		fatalf("-email-domain is required (e.g., student.example.ac.id)")
	}

	cfg := config.Init()
	db := database.Connect(cfg)
	defer db.Close()
	q := gen.New(db)

	// Resolve program ID
	var programID int64
	if programIDFlag > 0 {
		programID = programIDFlag
	} else {
		if programName == "" {
			fatalf("provide -program-id or -program-name")
		}
		pid, err := resolveProgramID(context.Background(), q, programName)
		if err != nil {
			fatalf("program not found: %v", err)
		}
		programID = pid
	}

	// Open CSV
	f, err := os.Open(filePath)
	if err != nil {
		fatalf("open CSV: %v", err)
	}
	defer f.Close()

	r := csv.NewReader(bufio.NewReader(f))
	r.TrimLeadingSpace = true

	headers, err := r.Read()
	if err != nil {
		fatalf("read header: %v", err)
	}
	idx := buildHeaderIndex(headers)
	if _, ok := idx["student_id"]; !ok {
		toks := make([]string, 0, len(headers))
		for _, h := range headers {
			toks = append(toks, normalizeHeader(h))
		}
		fatalf("missing NIM/NPM header (normalized headers: %v)", toks)
	}
	if _, ok := idx["name"]; !ok {
		toks := make([]string, 0, len(headers))
		for _, h := range headers {
			toks = append(toks, normalizeHeader(h))
		}
		fatalf("missing NAMA header (normalized headers: %v)", toks)
	}

	// Read all remaining rows first to know total count for progress display
	var rows [][]string
	for {
		rec, err := r.Read()
		if errors.Is(err, io.EOF) {
			break
		}
		if err != nil {
			fatalf("read row: %v", err)
		}
		rows = append(rows, rec)
	}

	total := len(rows)
	usersCreated, studentsInserted, studentsUpdated, skipped := 0, 0, 0, 0

	for i, rec := range rows {
		// progress line
		fmt.Printf("\rProcessing %d/%d", i+1, total)

		rd := rowData{
			StudentID: get(rec, idx["student_id"]),
			Name:      get(rec, idx["name"]),
		}
		rd.StudentID = strings.TrimSpace(rd.StudentID)
		rd.Name = strings.TrimSpace(rd.Name)

		rowNo := i + 2 // header is row 1
		if rd.StudentID == "" || rd.Name == "" {
			fmt.Fprintf(os.Stderr, "\nrow %d skipped: missing NIM or NAMA\n", rowNo)
			skipped++
			continue
		}

		username := rd.StudentID
		email := rd.StudentID + "@" + emailDomain

		if dryRun {
			fmt.Printf("\rProcessing %d/%d - DRY-RUN ok                                   ", i+1, total)
			continue
		}

		// Ensure user
		uid, created, err := ensureUser(context.Background(), q, username, email, rd.Name, role, passOrNIM(defaultPass, rd.StudentID))
		if err != nil {
			fmt.Fprintf(os.Stderr, "\nrow %d error ensureUser: %v\n", rowNo, err)
			skipped++
			continue
		}
		if created {
			usersCreated++
		}

		// Upsert student
		existed := studentExistsByUser(context.Background(), q, uid)
		if existed {
			// Update existing student
			err = q.UpdateStudentByUserID(context.Background(), gen.UpdateStudentByUserIDParams{
				StudentID:  rd.StudentID,
				ProgramID:  programID,
				CohortYear: toNullInt32(cohortYear),
				Status:     gen.StudentsStatus(strings.ToLower(status)),
				BirthDate:  sql.NullTime{},
				Gender:     gen.NullStudentsGender{},
				Phone:      sql.NullString{},
				UserID:     uid,
			})
			if err != nil {
				fmt.Fprintf(os.Stderr, "\nrow %d update student error: %v\n", rowNo, err)
				skipped++
				continue
			}
			studentsUpdated++
		} else {
			// Insert
			err = q.InsertStudent(context.Background(), gen.InsertStudentParams{
				UserID:     uid,
				StudentID:  rd.StudentID,
				ProgramID:  programID,
				CohortYear: toNullInt32(cohortYear),
				Status:     gen.StudentsStatus(strings.ToLower(status)),
				BirthDate:  sql.NullTime{},
				Gender:     gen.NullStudentsGender{},
				Phone:      sql.NullString{},
			})
			if err != nil {
				fmt.Fprintf(os.Stderr, "\nrow %d insert student error: %v\n", rowNo, err)
				skipped++
				continue
			}
			studentsInserted++
		}
	}

	fmt.Printf("\nDone. rows=%d users_created=%d students_inserted=%d students_updated=%d skipped=%d\n",
		total, usersCreated, studentsInserted, studentsUpdated, skipped)
}

func resolveProgramID(ctx context.Context, q gen.Querier, name string) (int64, error) {
	rows, err := q.ListPrograms(ctx)
	if err != nil {
		return 0, err
	}
	wanted := strings.ToLower(strings.TrimSpace(name))
	for _, p := range rows {
		if strings.ToLower(strings.TrimSpace(p.Name)) == wanted {
			return p.ID, nil
		}
	}
	return 0, fmt.Errorf("program %q not found", name)
}

func buildHeaderIndex(headers []string) map[string]int {
	idx := map[string]int{}
	for i, h := range headers {
		key := normalizeHeader(h)
		switch key {
		case "nim", "npm", "nrp", "studentid", "student_id":
			idx["student_id"] = i
			continue
		case "nama", "name", "namamahasiswa", "namamhs":
			idx["name"] = i
			continue
		}
		// Fuzzy contains to catch variants like "NIM (NPM)" or "Nama Mahasiswa"
		if _, ok := idx["student_id"]; !ok {
			if strings.Contains(key, "nim") || strings.Contains(key, "npm") || strings.Contains(key, "nrp") {
				idx["student_id"] = i
				continue
			}
		}
		if _, ok := idx["name"]; !ok {
			if strings.Contains(key, "nama") || strings.Contains(key, "name") {
				idx["name"] = i
				continue
			}
		}
	}
	return idx
}

func normalizeHeader(h string) string {
	// Trim and strip UTF-8 BOM if present
	s := strings.TrimSpace(h)
	s = strings.TrimPrefix(s, "\uFEFF")
	s = strings.ToLower(s)
	// Remove non-alphanumeric except underscore
	b := make([]rune, 0, len(s))
	for _, r := range s {
		if (r >= 'a' && r <= 'z') || (r >= '0' && r <= '9') || r == '_' {
			b = append(b, r)
		}
	}
	return string(b)
}

func get(rec []string, i int) string {
	if i >= 0 && i < len(rec) {
		return rec[i]
	}
	return ""
}

func passOrNIM(pass string, nim string) string {
	if pass != "" {
		return pass
	}
	return nim
}

func ensureUser(ctx context.Context, q gen.Querier, username, email, name, role, password string) (int64, bool, error) {
	if u, err := q.GetUserByEmail(ctx, email); err == nil && u.ID != 0 {
		return u.ID, false, nil
	}
	if u, err := q.GetUserByUsername(ctx, username); err == nil && u.ID != 0 {
		return u.ID, false, nil
	}

	hashed, err := utils.HashPassword(password)
	if err != nil {
		return 0, false, err
	}
	res, err := q.CreateUser(ctx, gen.CreateUserParams{
		Email:       email,
		Username:    username,
		Password:    hashed,
		DisplayName: name,
		Role:        gen.UsersRole(role),
	})
	if err != nil {
		return 0, false, err
	}
	id, err := res.LastInsertId()
	if err != nil {
		return 0, false, err
	}
	return id, true, nil
}

func studentExistsByUser(ctx context.Context, q gen.Querier, userID int64) bool {
	_, err := q.GetStudentByUserID(ctx, userID)
	return err == nil
}

func toNullInt32(v int) sql.NullInt32 {
	if v == 0 {
		return sql.NullInt32{}
	}
	return sql.NullInt32{Int32: int32(v), Valid: true}
}

func nullableInt(v int) any {
	if v == 0 {
		return nil
	}
	return v
}

func fatalf(format string, a ...any) {
	fmt.Fprintf(os.Stderr, format+"\n", a...)
	os.Exit(1)
}
