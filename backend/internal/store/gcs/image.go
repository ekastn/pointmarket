package gcs

import (
	"bufio"
	"context"
	"crypto/rand"
	"encoding/hex"
	"fmt"
	"io"
	"net/http"
	"strings"
	"time"

	"cloud.google.com/go/storage"
)

type GCSImageStore struct {
	client        *storage.Client
	bucket        string
	publicBaseURL string
	maxSizeBytes  int64
}

func NewGCSImageStore(client *storage.Client, bucket, publicBaseURL string, maxAvatarMB int) (*GCSImageStore, error) {
	if bucket == "" {
		return nil, fmt.Errorf("gcs: bucket must not be empty")
	}
	if publicBaseURL == "" {
		publicBaseURL = fmt.Sprintf("https://storage.googleapis.com/%s", bucket)
	}
	if maxAvatarMB <= 0 {
		maxAvatarMB = 5
	}
	return &GCSImageStore{
		client:        client,
		bucket:        bucket,
		publicBaseURL: strings.TrimRight(publicBaseURL, "/"),
		maxSizeBytes:  int64(maxAvatarMB) * 1024 * 1024,
	}, nil
}

func (s *GCSImageStore) PutUserAvatar(ctx context.Context, userID int64, r io.Reader) (string, string, error) {
	// Limit reader to max+1 to detect overflow
	limited := &io.LimitedReader{R: r, N: s.maxSizeBytes + 1}
	br := bufio.NewReader(limited)

	// Peek first 512 bytes for content type detection
	header, err := br.Peek(512)
	if err != nil && err != bufio.ErrBufferFull && err != io.EOF {
		return "", "", fmt.Errorf("gcs: peek failed: %w", err)
	}
	ct := http.DetectContentType(header)
	ext, err := extForContentType(ct)
	if err != nil {
		return "", "", err
	}

	objName := fmt.Sprintf("%d/%s.%s", userID, randHex(16), ext)
	w := s.client.Bucket(s.bucket).Object(objName).NewWriter(ctx)
	w.ObjectAttrs.ContentType = ct
	w.ObjectAttrs.CacheControl = "public, max-age=3600"

	if _, err := io.Copy(w, br); err != nil {
		_ = w.Close()
		return "", "", fmt.Errorf("gcs: copy failed: %w", err)
	}
	if err := w.Close(); err != nil {
		return "", "", fmt.Errorf("gcs: close failed: %w", err)
	}

	// Detect overflow: if LimitedReader hit exactly N==0, data was larger than max
	if limited.N == 0 {
		// Best-effort cleanup
		_ = s.client.Bucket(s.bucket).Object(objName).Delete(ctx)
		return "", "", fmt.Errorf("image too large (>%d bytes)", s.maxSizeBytes)
	}

	publicURL := fmt.Sprintf("%s/%s", s.publicBaseURL, objName)
	return publicURL, objName, nil
}

func (s *GCSImageStore) Delete(ctx context.Context, objectPath string) error {
	if strings.TrimSpace(objectPath) == "" {
		return nil
	}
	err := s.client.Bucket(s.bucket).Object(objectPath).Delete(ctx)
	if err != nil && err != storage.ErrObjectNotExist {
		return fmt.Errorf("gcs: delete failed: %w", err)
	}
	return nil
}

func extForContentType(ct string) (string, error) {
	switch ct {
	case "image/jpeg", "image/jpg":
		return "jpg", nil
	case "image/png":
		return "png", nil
	case "image/webp":
		return "webp", nil
	default:
		return "", fmt.Errorf("unsupported content type: %s", ct)
	}
}

func randHex(n int) string {
	b := make([]byte, n)
	if _, err := rand.Read(b); err != nil {
		// Fallback to timestamp
		return fmt.Sprintf("%d", time.Now().UnixNano())
	}
	return hex.EncodeToString(b)
}
