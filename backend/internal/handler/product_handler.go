package handler

import (
	"net/http"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/response"
	"pointmarket/backend/internal/services"
	"strconv"

	"github.com/gin-gonic/gin"
)

// ProductHandler handles product-related HTTP requests
type ProductHandler struct {
	productService services.ProductService
}

// NewProductHandler creates a new ProductHandler
func NewProductHandler(productService services.ProductService) *ProductHandler {
	return &ProductHandler{productService: productService}
}

// GetProductByID handles fetching a single product by ID
func (h *ProductHandler) GetProductByID(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid product ID")
		return
	}

	product, err := h.productService.GetProductByID(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve product: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product retrieved successfully", product)
}

// GetProducts handles fetching a list of products
func (h *ProductHandler) GetProducts(c *gin.Context) {
	products, err := h.productService.GetProducts(c.Request.Context())
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve products: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Products retrieved successfully", dtos.ListProductsResponseDTO{
		Products: products,
		Total:    len(products),
	})
}
