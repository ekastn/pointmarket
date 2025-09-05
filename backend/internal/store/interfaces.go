package store

import (
	"context"
	"io"
)

// ImageStore abstracts image storage operations (e.g., GCS, S3, local).
// Implementations must validate size and content type internally.
type ImageStore interface {
	// PutUserAvatar stores the avatar image for a user and returns a public URL
	// and the provider-specific object path for potential deletion.
	PutUserAvatar(ctx context.Context, userID int64, r io.Reader) (publicURL string, objectPath string, err error)

	// Delete removes an object by its storage-specific path. Best-effort.
	Delete(ctx context.Context, objectPath string) error
}
