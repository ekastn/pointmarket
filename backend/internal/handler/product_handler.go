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
    page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
    limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
    search := c.Query("search") // Assuming search by name or description

    var categoryID *int32
    categoryIDStr := c.Query("category_id")
    if categoryIDStr != "" {
        parsedCategoryID, err := strconv.ParseInt(categoryIDStr, 10, 32)
        if err != nil {
            response.Error(c, http.StatusBadRequest, "Invalid category ID parameter")
            return
        }
        val := int32(parsedCategoryID)
        categoryID = &val
    }

    // only_active: default true for non-admin; allow override via query param
    onlyActiveParam := c.Query("only_active")
    roleVal, _ := c.Get("role")
    role := ""
    if roleVal != nil {
        role = roleVal.(string)
    }
    onlyActive := true
    if role == "admin" {
        onlyActive = false
    }
    if onlyActiveParam != "" {
        // accept 1/0/true/false
        switch onlyActiveParam {
        case "0", "false", "False", "FALSE":
            onlyActive = false
        case "1", "true", "True", "TRUE":
            onlyActive = true
        }
    }

    products, totalProducts, err := h.productService.GetProducts(c.Request.Context(), page, limit, search, categoryID, onlyActive)
    if err != nil {
        response.Error(c, http.StatusInternalServerError, "Failed to retrieve products: "+err.Error())
        return
    }

    response.Paginated(c, http.StatusOK, "Products retrieved successfully", products, totalProducts, page, limit)
}

// CreateProduct handles creating a new product
func (h *ProductHandler) CreateProduct(c *gin.Context) {
	var req dtos.CreateProductRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	product, err := h.productService.CreateProduct(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to create product: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Product created successfully", product)
}

// UpdateProduct handles updating an existing product
func (h *ProductHandler) UpdateProduct(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid product ID")
		return
	}

	var req dtos.UpdateProductRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	product, err := h.productService.UpdateProduct(c.Request.Context(), id, req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to update product: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product updated successfully", product)
}

// DeleteProduct handles deleting a product by its ID
func (h *ProductHandler) DeleteProduct(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid product ID")
		return
	}

	err = h.productService.DeleteProduct(c.Request.Context(), id)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to delete product: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product deleted successfully", nil)
}

// PurchaseProduct handles purchasing a product
func (h *ProductHandler) PurchaseProduct(c *gin.Context) {
	productIDParam := c.Param("id")
	productID, err := strconv.ParseInt(productIDParam, 10, 64)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid product ID")
		return
	}

	userID, exists := c.Get("userID")
	if !exists {
		response.Error(c, http.StatusUnauthorized, "User ID not found in context")
		return
	}

	err = h.productService.PurchaseProduct(c.Request.Context(), userID.(int64), productID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to purchase product: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product purchased successfully", nil)
}

// CreateProductCategory handles creating a new product category
func (h *ProductHandler) CreateProductCategory(c *gin.Context) {
	var req dtos.CreateProductCategoryRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	category, err := h.productService.CreateProductCategory(c.Request.Context(), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to create product category: "+err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Product category created successfully", category)
}

// GetProductCategoryByID handles fetching a single product category by ID
func (h *ProductHandler) GetProductCategoryByID(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 32) // Category ID is INT in DB
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid category ID")
		return
	}

	category, err := h.productService.GetProductCategoryByID(c.Request.Context(), int32(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve product category: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product category retrieved successfully", category)
}

// GetProductCategories handles fetching a list of product categories
func (h *ProductHandler) GetProductCategories(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	search := c.Query("search") // Assuming search by name

	categories, totalCategories, err := h.productService.GetProductCategories(c.Request.Context(), page, limit, search)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to retrieve product categories: "+err.Error())
		return
	}

	response.Paginated(c, http.StatusOK, "Product categories retrieved successfully", categories, totalCategories, page, limit)
}

// UpdateProductCategory handles updating an existing product category
func (h *ProductHandler) UpdateProductCategory(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 32) // Category ID is INT in DB
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid category ID")
		return
	}

	var req dtos.UpdateProductCategoryRequestDTO
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request body: "+err.Error())
		return
	}

	category, err := h.productService.UpdateProductCategory(c.Request.Context(), int32(id), req)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to update product category: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product category updated successfully", category)
}

// DeleteProductCategory handles deleting a product category by its ID
func (h *ProductHandler) DeleteProductCategory(c *gin.Context) {
	idParam := c.Param("id")
	id, err := strconv.ParseInt(idParam, 10, 32) // Category ID is INT in DB
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid category ID")
		return
	}

	err = h.productService.DeleteProductCategory(c.Request.Context(), int32(id))
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to delete product category: "+err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Product category deleted successfully", nil)
}
