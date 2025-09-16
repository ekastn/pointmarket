package middleware

import (
	"net/http"
	"pointmarket/backend/internal/response"

	"github.com/gin-gonic/gin"
)

// Authz checks if the authenticated user has the required role.
func Authz(requiredRole string) gin.HandlerFunc {
	return func(c *gin.Context) {
		userRoleVal, exists := c.Get("role")
		if !exists {
			response.Error(c, http.StatusForbidden, "User role not found in context")
			c.Abort()
			return
		}

		// Normalize to string
		userRole, _ := userRoleVal.(string)

		// Admin bypass: admin can access routes guarded by any role
		if userRole == "admin" {
			c.Next()
			return
		}

		if userRole != requiredRole {
			response.Error(c, http.StatusForbidden, "Forbidden: Insufficient role privileges")
			c.Abort()
			return
		}
		c.Next()
	}
}
