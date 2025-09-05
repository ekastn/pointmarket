package compress

import (
	"bytes"
	"errors"
	"image"
	"image/color"
	"image/jpeg"
	"io"

	"github.com/disintegration/imaging"

	_ "image/gif"
	_ "image/png"

	_ "golang.org/x/image/bmp"
	_ "golang.org/x/image/tiff"
	"golang.org/x/image/webp"
)

// Sentinel errors.
var (
	ErrDecodeFailed   = errors.New("decode image: failed")
	ErrEncodeFailed   = errors.New("encode image: failed")
	ErrSizeConstraint = errors.New("cannot meet size constraint within quality bounds")
)

type Options struct {
	MaxEdge      int // default 360
	MaxBytes     int // default 300*1024
	MinQuality   int // default 10
	MaxQuality   int // default 95
	StartQuality int // default 85 (if <=0 then MaxQuality)
}

func DefaultOptions() Options {
	return Options{
		MaxEdge:      360,
		MaxBytes:     300 * 1024,
		MinQuality:   10,
		MaxQuality:   95,
		StartQuality: 85,
	}
}

// CompressSquareJPEG: decode (+EXIF auto-orient), fit, center-crop square, JPEG <= MaxBytes.
func CompressSquareJPEG(r io.Reader, w io.Writer, opt Options) error {
	opt = withDefaults(opt)

	img, err := decodeWithAutoOrientation(r)
	if err != nil {
		return ErrDecodeFailed
	}

	img = normalizeToOpaqueNRGBA(img) // flattens any alpha to white
	img = fitWithin(img, opt.MaxEdge) // no upscaling
	img = cropCenterSquare(img)

	buf, err := encodeWithinSizeJPEG(img, opt)
	if err != nil {
		return err
	}
	_, err = io.Copy(w, bytes.NewReader(buf))
	return err
}

// decodeWithAutoOrientation tries imaging.Decode with AutoOrientation, then WebP fallback.
func decodeWithAutoOrientation(r io.Reader) (image.Image, error) {
	src, err := io.ReadAll(r)
	if err != nil {
		return nil, err
	}
	// imaging.Decode supports JPEG/PNG/GIF/TIFF/BMP and can apply AutoOrientation.
	if img, err := imaging.Decode(bytes.NewReader(src), imaging.AutoOrientation(true)); err == nil {
		return img, nil
	}
	// Explicit WebP fallback (EXIF orientation generally not applicable to WebP).
	if img, err := webp.Decode(bytes.NewReader(src)); err == nil {
		return img, nil
	}
	// As a last resort, try stdlib image.Decode (no EXIF auto-orient).
	if img, _, err := image.Decode(bytes.NewReader(src)); err == nil {
		return img, nil
	}
	return nil, ErrDecodeFailed
}

// normalizeToOpaqueNRGBA paints the image onto a white canvas (alpha -> white).
func normalizeToOpaqueNRGBA(img image.Image) *image.NRGBA {
	canvas := imaging.New(img.Bounds().Dx(), img.Bounds().Dy(), color.White)
	over := imaging.Overlay(canvas, img, image.Point{}, 1.0)
	return imaging.Clone(over)
}

// fitWithin resizes to fit within maxEdge using Lanczos, without upscaling.
func fitWithin(img image.Image, maxEdge int) *image.NRGBA {
	w, h := img.Bounds().Dx(), img.Bounds().Dy()
	if w <= maxEdge && h <= maxEdge {
		return imaging.Clone(img)
	}
	return imaging.Fit(img, maxEdge, maxEdge, imaging.Lanczos)
}

// cropCenterSquare crops the largest centered square from the image.
func cropCenterSquare(img image.Image) *image.NRGBA {
	w, h := img.Bounds().Dx(), img.Bounds().Dy()
	side := w
	if h < w {
		side = h
	}
	return imaging.CropCenter(img, side, side)
}

// encodeWithinSizeJPEG finds the highest JPEG quality that fits within MaxBytes.
func encodeWithinSizeJPEG(img image.Image, opt Options) ([]byte, error) {
	encodeJPEG := func(q int) ([]byte, int, error) {
		var b bytes.Buffer
		if err := jpeg.Encode(&b, img, &jpeg.Options{Quality: q}); err != nil {
			return nil, 0, ErrEncodeFailed
		}
		return b.Bytes(), b.Len(), nil
	}

	// First attempt at StartQuality (or MaxQuality if <=0).
	q := opt.StartQuality
	if q <= 0 {
		q = opt.MaxQuality
	}
	if buf, size, err := encodeJPEG(q); err == nil && size <= opt.MaxBytes {
		return buf, nil
	}

	// Binary search in [MinQuality, q].
	lo, hi := opt.MinQuality, q
	var best []byte
	found := false

	for lo <= hi {
		mid := (lo + hi) / 2
		buf, size, err := encodeJPEG(mid)
		if err != nil {
			return nil, err
		}
		if size <= opt.MaxBytes {
			best = buf
			found = true
			lo = mid + 1 // try higher quality
		} else {
			hi = mid - 1 // need lower quality
		}
	}

	if found {
		return best, nil
	}
	// Last-chance check at MinQuality (normally covered).
	if buf, size, err := encodeJPEG(opt.MinQuality); err == nil && size <= opt.MaxBytes {
		return buf, nil
	}
	return nil, ErrSizeConstraint
}

func withDefaults(o Options) Options {
	def := DefaultOptions()
	if o.MaxEdge <= 0 {
		o.MaxEdge = def.MaxEdge
	}
	if o.MaxBytes <= 0 {
		o.MaxBytes = def.MaxBytes
	}
	if o.MinQuality <= 0 {
		o.MinQuality = def.MinQuality
	}
	if o.MaxQuality <= 0 {
		o.MaxQuality = def.MaxQuality
	}
	if o.StartQuality <= 0 {
		o.StartQuality = def.StartQuality
	}
	if o.MinQuality > o.MaxQuality {
		o.MinQuality, o.MaxQuality = o.MaxQuality, o.MinQuality
	}
	return o
}
