package dtos

import (
	"encoding/json"
	"pointmarket/backend/internal/store/gen"
	"time"
)

// ProductDTO represents a product for API responses
type ProductDTO struct {
	ID            int64           `json:"id"`
	CategoryID    *int32          `json:"category_id"`
	Name          string          `json:"name"`
	Description   *string         `json:"description"`
	PointsPrice   int32           `json:"points_price"`
	Type          string          `json:"type"`
	StockQuantity *int32          `json:"stock_quantity"`
	IsActive      bool            `json:"is_active"`
	Metadata      json.RawMessage `json:"metadata"`
	CreatedAt     time.Time       `json:"created_at"`
	UpdatedAt     time.Time       `json:"updated_at"`
	CategoryName  *string         `json:"category_name"`
}

// FromProductModel converts a gen.Product model to a ProductDTO
func (dto *ProductDTO) FromProductModel(m interface{}) {
	switch p := m.(type) {
	case gen.Product:
		dto.ID = p.ID
		if p.CategoryID.Valid {
			dto.CategoryID = &p.CategoryID.Int32
		} else {
			dto.CategoryID = nil
		}
		if p.StockQuantity.Valid {
			dto.StockQuantity = &p.StockQuantity.Int32
		} else {
			dto.StockQuantity = nil
		}
		dto.Name = p.Name
		if p.Description.Valid {
			dto.Description = &p.Description.String
		} else {
			dto.Description = nil
		}
		dto.PointsPrice = p.PointsPrice
		dto.Type = p.Type
		dto.IsActive = p.IsActive
		dto.Metadata = p.Metadata
		dto.CreatedAt = p.CreatedAt
		dto.UpdatedAt = p.UpdatedAt
		dto.CategoryName = nil // Default for gen.Product
	case gen.GetProductByIDRow:
		dto.ID = p.ID
		if p.CategoryID.Valid {
			dto.CategoryID = &p.CategoryID.Int32
		} else {
			dto.CategoryID = nil
		}
		if p.StockQuantity.Valid {
			dto.StockQuantity = &p.StockQuantity.Int32
		} else {
			dto.StockQuantity = nil
		}
		dto.Name = p.Name
		if p.Description.Valid {
			dto.Description = &p.Description.String
		} else {
			dto.Description = nil
		}
		dto.PointsPrice = p.PointsPrice
		dto.Type = p.Type
		dto.IsActive = p.IsActive
		dto.Metadata = p.Metadata
		dto.CreatedAt = p.CreatedAt
		dto.UpdatedAt = p.UpdatedAt
		if p.CategoryName.Valid {
			dto.CategoryName = &p.CategoryName.String
		} else {
			dto.CategoryName = nil
		}
	case gen.GetProductsRow:
		dto.ID = p.ID
		if p.CategoryID.Valid {
			dto.CategoryID = &p.CategoryID.Int32
		} else {
			dto.CategoryID = nil
		}
		if p.StockQuantity.Valid {
			dto.StockQuantity = &p.StockQuantity.Int32
		} else {
			dto.StockQuantity = nil
		}
		dto.Name = p.Name
		if p.Description.Valid {
			dto.Description = &p.Description.String
		} else {
			dto.Description = nil
		}
		dto.PointsPrice = p.PointsPrice
		dto.Type = p.Type
		dto.IsActive = p.IsActive
		dto.Metadata = p.Metadata
		dto.CreatedAt = p.CreatedAt
		dto.UpdatedAt = p.UpdatedAt
		if p.CategoryName.Valid {
			dto.CategoryName = &p.CategoryName.String
		} else {
			dto.CategoryName = nil
		}
	}
}

type ListProductsResponseDTO struct {
	Products []ProductDTO `json:"products"`
	Total    int          `json:"total"`
}

// CreateProductRequestDTO for creating a new product
type CreateProductRequestDTO struct {
	CategoryID    *int32          `json:"category_id"`
	Name          string          `json:"name" binding:"required"`
	Description   *string         `json:"description"`
	PointsPrice   int32           `json:"points_price" binding:"required"`
	Type          string          `json:"type" binding:"required"`
	StockQuantity *int32          `json:"stock_quantity"`
	IsActive      bool            `json:"is_active"`
	Metadata      json.RawMessage `json:"metadata"`
}

// UpdateProductRequestDTO for updating an existing product
type UpdateProductRequestDTO struct {
	CategoryID    *int32          `json:"category_id"`
	Name          string          `json:"name"`
	Description   *string         `json:"description"`
	PointsPrice   int32           `json:"points_price"`
	Type          string          `json:"type"`
	StockQuantity *int32          `json:"stock_quantity"`
	IsActive      bool            `json:"is_active"`
	Metadata      json.RawMessage `json:"metadata"`
}

// --- Product Category DTOs ---

// ProductCategoryDTO represents a product category for API responses
type ProductCategoryDTO struct {
	ID          int32   `json:"id"`
	Name        string  `json:"name"`
	Description *string `json:"description"`
}

// CreateProductCategoryRequestDTO for creating a new product category
type CreateProductCategoryRequestDTO struct {
	Name        string  `json:"name" binding:"required"`
	Description *string `json:"description"`
}

// UpdateProductCategoryRequestDTO for updating an existing product category
type UpdateProductCategoryRequestDTO struct {
	Name        *string `json:"name"`
	Description *string `json:"description"`
}

// ListProductCategoriesResponseDTO contains a list of ProductCategoryDTOs
type ListProductCategoriesResponseDTO struct {
	Categories []ProductCategoryDTO `json:"categories"`
	Total      int                  `json:"total"`
}
