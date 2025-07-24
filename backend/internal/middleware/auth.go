package middleware

import (
	"net/http"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/jmoiron/sqlx"
	"pointmarket/backend/internal/auth"
	"pointmarket/backend/internal/config"
	"pointmarket/backend/internal/response"
)

// AuthMiddleware authenticates requests using JWT
func AuthMiddleware(cfg *config.Config, db *sqlx.DB) gin.HandlerFunc {
	return func(c *gin.Context) {
		authHeader := c.GetHeader("Authorization")
		if authHeader == "" {
			response.Error(c, http.StatusUnauthorized, "Authorization header required")
			c.Abort()
			return
		}

		parts := strings.Split(authHeader, " ")
		if len(parts) != 2 || parts[0] != "Bearer" {
			response.Error(c, http.StatusUnauthorized, "Invalid Authorization header format")
			c.Abort()
			return
		}

		tokenString := parts[1]
		claims, err := auth.ValidateJWT(tokenString, cfg)
		if err != nil {
			response.Error(c, http.StatusUnauthorized, "Invalid or expired token")
			c.Abort()
			return
		}

		// Get user ID from username and set in context
		var userID int
		err = db.Get(&userID, "SELECT id FROM users WHERE username = ?", claims.Username)
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Failed to get user ID from token")
			c.Abort()
			return
		}

		c.Set("username", claims.Username)
		c.Set("role", claims.Role)
		c.Set("user_id", userID)
		c.Next()
	}
}