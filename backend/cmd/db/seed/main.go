package main

import (
	"bufio"
	"context"
	"flag"
	"fmt"
	"io"
	"os"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/database"
	"strings"
	"unicode/utf8"
)

func main() {
	var filePath string
	var continueOnError bool
	flag.StringVar(&filePath, "file", "backend/cmd/db/scripts/dummy_data.sql", "Path to SQL file to seed")
	flag.BoolVar(&continueOnError, "continue-on-error", false, "Continue executing statements after an error")
	flag.Parse()

	// Load config and connect to DB
	cfg := config.Init()
	db := database.Connect(cfg)
	defer db.Close()

	// Read SQL file
	f, err := os.Open(filePath)
	if err != nil {
		fatalf("open file: %v", err)
	}
	defer f.Close()

	content, err := io.ReadAll(bufio.NewReader(f))
	if err != nil {
		fatalf("read file: %v", err)
	}

	statements := splitSQL(string(content))
	if len(statements) == 0 {
		fmt.Println("No SQL statements found.")
		return
	}

	ctx := context.Background()
	tx, err := db.BeginTxx(ctx, nil)
	if err != nil {
		fatalf("begin transaction: %v", err)
	}

	succeeded := 0
	for i, stmt := range statements {
		fmt.Printf("\rSeeding %d/%d", i+1, len(statements))
		s := strings.TrimSpace(stmt)
		if s == "" {
			continue
		}
		if _, err := tx.ExecContext(ctx, s); err != nil {
			// Show a small snippet for context
			snippet := s
			if len(snippet) > 200 {
				snippet = snippet[:200] + "..."
			}
			fmt.Fprintf(os.Stderr, "\nError on statement %d: %v\nSQL: %s\n", i+1, err, snippet)
			if !continueOnError {
				_ = tx.Rollback()
				fatalf("aborted at statement %d", i+1)
			}
		} else {
			succeeded++
		}
	}

	if err := tx.Commit(); err != nil {
		fatalf("commit: %v", err)
	}
	fmt.Printf("\nSeed completed. Executed: %d, Succeeded: %d, Failed: %d\n", len(statements), succeeded, len(statements)-succeeded)
}

// splitSQL splits SQL text into individual statements by ';' while respecting
// strings and comments. It supports -- line comments and /* */ block comments.
func splitSQL(s string) []string {
	// strip UTF-8 BOM if present
	s = strings.TrimPrefix(s, "\uFEFF")

	var out []string
	var b strings.Builder

	// Use rune iteration for safety with unicode
	rs := []rune(s)
	inSingle := false
	inDouble := false
	inLineComment := false
	inBlockComment := false

	for i := 0; i < len(rs); i++ {
		r := rs[i]

		// End of line comment
		if inLineComment {
			if r == '\n' {
				inLineComment = false
				b.WriteRune(r)
			}
			continue
		}

		// End of block comment
		if inBlockComment {
			if r == '*' && i+1 < len(rs) && rs[i+1] == '/' {
				inBlockComment = false
				i++ // skip '/'
			}
			continue
		}

		// Start of comments (only when not in string)
		if !inSingle && !inDouble {
			if r == '-' && i+1 < len(rs) && rs[i+1] == '-' {
				inLineComment = true
				i++
				continue
			}
			if r == '/' && i+1 < len(rs) && rs[i+1] == '*' {
				inBlockComment = true
				i++
				continue
			}
		}

		// Toggle string states
		if r == '\'' && !inDouble {
			inSingle = !inSingle
			b.WriteRune(r)
			continue
		}
		if r == '"' && !inSingle {
			inDouble = !inDouble
			b.WriteRune(r)
			continue
		}

		// Statement delimiter
		if r == ';' && !inSingle && !inDouble {
			stmt := strings.TrimSpace(b.String())
			if stmt != "" {
				out = append(out, stmt)
			}
			b.Reset()
			continue
		}

		// Regular rune
		b.WriteRune(r)
	}

	// Tail
	tail := strings.TrimSpace(b.String())
	if tail != "" {
		out = append(out, tail)
	}
	return out
}

func fatalf(format string, a ...any) {
	msg := fmt.Sprintf(format+"\n", a...)
	if !utf8.ValidString(msg) {
		msg = strings.ToValidUTF8(msg, "?")
	}
	fmt.Fprint(os.Stderr, msg)
	os.Exit(1)
}
