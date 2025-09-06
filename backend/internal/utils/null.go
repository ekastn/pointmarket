package utils

import (
	"database/sql"
	"pointmarket/backend/internal/store/gen"
	"time"
)

// helpers for nullable types
func NullInt32(v *int32) sql.NullInt32 {
	if v == nil {
		return sql.NullInt32{}
	}
	return sql.NullInt32{Int32: *v, Valid: true}
}

func NullInt64(v *int64) sql.NullInt64 {
	if v == nil {
		return sql.NullInt64{}
	}
	return sql.NullInt64{Int64: *v, Valid: true}
}

func NullString(v *string) sql.NullString {
	if v == nil {
		return sql.NullString{}
	}
	return sql.NullString{String: *v, Valid: true}
}

func NullTime(v *time.Time) sql.NullTime {
	if v == nil {
		return sql.NullTime{}
	}
	return sql.NullTime{Time: *v, Valid: true}
}

func StatusOrDefault(v *string) gen.StudentsStatus {
	if v == nil || *v == "" {
		return gen.StudentsStatusActive
	}
	return gen.StudentsStatus(*v)
}

func NullGender(v *string) gen.NullStudentsGender {
	if v == nil || *v == "" {
		return gen.NullStudentsGender{}
	}
	return gen.NullStudentsGender{StudentsGender: gen.StudentsGender(*v), Valid: true}
}
