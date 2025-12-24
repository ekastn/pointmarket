package dtos

// APIResponse is the standard success envelope for API responses.
//
// Note: `Data` is intentionally `interface{}` and overridden per endpoint in
// swagger annotations using the swag model composition syntax:
//
//	@Success 200 {object} dtos.APIResponse{data=dtos.SomeDTO}
//
// This mirrors the runtime behavior of response.Success.
type APIResponse struct {
	Success bool        `json:"success"`
	Message string      `json:"message"`
	Data    interface{} `json:"data"`
}

// APIError is the standard error envelope for API errors.
// This mirrors the runtime behavior of response.Error.
type APIError struct {
	Success bool   `json:"success"`
	Message string `json:"message"`
}

// ErrorResponse is kept for backward-compatibility with existing swagger
// annotations in other handlers.
type ErrorResponse struct {
	Success bool   `json:"success"`
	Message string `json:"message"`
}

type PaginationMeta struct {
	TotalRecords int64 `json:"total_records"`
	Page         int   `json:"page"`
	Limit        int   `json:"limit"`
	TotalPages   int64 `json:"total_pages"`
}

// PaginatedResponse is the standard success envelope for paginated responses.
// This mirrors the runtime behavior of response.Paginated.
type PaginatedResponse struct {
	Success bool           `json:"success"`
	Message string         `json:"message"`
	Data    interface{}    `json:"data"`
	Meta    PaginationMeta `json:"meta"`
}
