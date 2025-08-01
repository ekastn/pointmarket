package dtos

// ==================
//     Requests
// ==================

type RegisterRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
	Name     string `json:"name" binding:"required"`
	Email    string `json:"email" binding:"required,email"`
	Role     string `json:"role" binding:"required"`
}

type LoginRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
}

// ==================
//     Responses
// ==================

type LoginResponse struct {
	Token string  `json:"token"`
	User  UserDTO `json:"user"`
}
