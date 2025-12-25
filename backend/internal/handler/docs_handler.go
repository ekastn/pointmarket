package handler

import (
	"pointmarket/backend/internal/dtos"

	"github.com/gin-gonic/gin"
)

// ServeSwaggerDocs godoc
// @Summary Swagger UI
// @Description Serves Swagger UI assets
// @Tags docs
// @Produce html
// @Success 200 {object} dtos.SwaggerDocsResponse
// @Router /docs/{any} [get]
func ServeSwaggerDocs(c *gin.Context) {
	// This handler is never used at runtime; swagger is served directly by ginSwagger.
	_ = dtos.SwaggerDocsResponse{}
	c.Status(200)
}
