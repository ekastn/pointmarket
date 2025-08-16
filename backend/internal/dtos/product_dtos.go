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
}

// FromProductModel converts a gen.Product model to a ProductDTO
func (dto *ProductDTO) FromProductModel(m gen.Product) {
	dto.ID = m.ID
	if m.CategoryID.Valid {
		dto.CategoryID = &m.CategoryID.Int32
	} else {
		dto.CategoryID = nil
	}
	if m.StockQuantity.Valid {
		dto.StockQuantity = &m.StockQuantity.Int32
	} else {
		dto.StockQuantity = nil
	}
	dto.Name = m.Name
	if m.Description.Valid {
		dto.Description = &m.Description.String
	} else {
		dto.Description = nil
	}
	dto.PointsPrice = m.PointsPrice
	dto.Type = m.Type
	dto.IsActive = m.IsActive
	dto.Metadata = m.Metadata
	dto.CreatedAt = m.CreatedAt
	dto.UpdatedAt = m.UpdatedAt
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
