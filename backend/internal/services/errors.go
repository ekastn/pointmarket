package services

import "errors"

// Shared service-level sentinel errors
var (
	ErrAlreadyStarted   = errors.New("already started")
	ErrDuplicateOrdinal = errors.New("duplicate ordinal")
	ErrForbidden        = errors.New("forbidden")
)
