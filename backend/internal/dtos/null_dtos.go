package dtos

// NullData is a marker model for endpoints whose `data` field is `null`.
//
// Note: Swagger 2.0 has no first-class `null` type; this model is used to give
// the `data` field a stable named schema in docs:
//
//	dtos.APIResponse{data=dtos.NullData}
//
// The runtime response still returns JSON `null` when handlers pass `nil`.
type NullData struct{}
