package utils

import (
	"net/http"
	"pointmarket/backend/internal/response"
	"strconv"

	"github.com/gin-gonic/gin"
)

// GetIDFromParam parses an ID from Gin context parameters.
// returns the parsed ID as uint and a boolean indicating if parsing was successful.
// If parsing fails, it sends an error response and aborts the context.
func GetIDFromParam(c *gin.Context, paramName string) (uint, bool) {
	idStr := c.Param(paramName)
	id, err := strconv.ParseUint(idStr, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid ID format")
		c.Abort()
		return 0, false
	}
	return uint(id), true
}
