package dtos

type UpdateUserRoleRequest struct {
	Role string `json:"role" binding:"required"`
}

type AvatarUploadResponse struct {
	AvatarURL string `json:"avatar_url"`
}
