package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

// ProductService provides business logic for products
type ProductService struct {
	q  *gen.Queries // Change to concrete type
	db *sql.DB      // Add direct access to DB for transactions
}

// NewProductService creates a new ProductService
func NewProductService(db *sql.DB, q *gen.Queries) *ProductService { // Accept *sql.DB and *gen.Queries
	return &ProductService{q: q, db: db}
}

// GetProductByID retrieves a single product by its ID
func (s *ProductService) GetProductByID(ctx context.Context, id int64) (dtos.ProductDTO, error) {
	product, err := s.q.GetProductByID(ctx, id)
	if err != nil {
		return dtos.ProductDTO{}, err
	}

	var productDTO dtos.ProductDTO
	productDTO.FromProductModel(product)
	return productDTO, nil
}

// GetProducts retrieves a list of products with pagination
func (s *ProductService) GetProducts(ctx context.Context, page, limit int, search string) ([]dtos.ProductDTO, int64, error) {
	offset := (page - 1) * limit

	// Get total count of products
	totalProducts, err := s.q.CountProducts(ctx)
	if err != nil {
		return nil, 0, err
	}

	// Get the paginated list of products
	products, err := s.q.GetProducts(ctx, gen.GetProductsParams{
		Limit:  int32(limit),
		Offset: int32(offset),
	})
	if err != nil {
		return nil, 0, err
	}

	var productDTOs []dtos.ProductDTO
	for _, product := range products {
		var productDTO dtos.ProductDTO
		productDTO.FromProductModel(product)
		productDTOs = append(productDTOs, productDTO)
	}
	return productDTOs, totalProducts, nil
}

// CreateProduct creates a new product
func (s *ProductService) CreateProduct(ctx context.Context, req dtos.CreateProductRequestDTO) (dtos.ProductDTO, error) {
	var categoryID sql.NullInt32
	if req.CategoryID != nil {
		categoryID = sql.NullInt32{Int32: *req.CategoryID, Valid: true}
	}

	var description sql.NullString
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	var stockQuantity sql.NullInt32
	if req.StockQuantity != nil {
		stockQuantity = sql.NullInt32{Int32: *req.StockQuantity, Valid: true}
	}

	result, err := s.q.CreateProduct(ctx, gen.CreateProductParams{
		CategoryID:    categoryID,
		Name:          req.Name,
		Description:   description,
		PointsPrice:   req.PointsPrice,
		Type:          req.Type,
		StockQuantity: stockQuantity,
		IsActive:      req.IsActive,
		Metadata:      req.Metadata,
	})
	if err != nil {
		return dtos.ProductDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.ProductDTO{}, err
	}

	product, err := s.q.GetProductByID(ctx, id)
	if err != nil {
		return dtos.ProductDTO{}, err
	}

	var productDTO dtos.ProductDTO
	productDTO.FromProductModel(product)
	return productDTO, nil
}

// UpdateProduct updates an existing product
func (s *ProductService) UpdateProduct(ctx context.Context, id int64, req dtos.UpdateProductRequestDTO) (dtos.ProductDTO, error) {
	var categoryID sql.NullInt32
	if req.CategoryID != nil {
		categoryID = sql.NullInt32{Int32: *req.CategoryID, Valid: true}
	}

	var description sql.NullString
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	var stockQuantity sql.NullInt32
	if req.StockQuantity != nil {
		stockQuantity = sql.NullInt32{Int32: *req.StockQuantity, Valid: true}
	}

	err := s.q.UpdateProduct(ctx, gen.UpdateProductParams{
		ID:            id,
		CategoryID:    categoryID,
		Name:          req.Name,
		Description:   description,
		PointsPrice:   req.PointsPrice,
		Type:          req.Type,
		StockQuantity: stockQuantity,
		IsActive:      req.IsActive,
		Metadata:      req.Metadata,
	})
	if err != nil {
		return dtos.ProductDTO{}, err
	}

	product, err := s.q.GetProductByID(ctx, id)
	if err != nil {
		return dtos.ProductDTO{}, err
	}

	var productDTO dtos.ProductDTO
	productDTO.FromProductModel(product)
	return productDTO, nil
}

// DeleteProduct deletes a product by its ID
func (s *ProductService) DeleteProduct(ctx context.Context, id int64) error {
	return s.q.DeleteProduct(ctx, id)
}

// PurchaseProduct handles the purchase of a product by a user
func (s *ProductService) PurchaseProduct(ctx context.Context, userID, productID int64) error {
	// 1. Get product details
	product, err := s.q.GetProductByID(ctx, productID)
	if err != nil {
		return fmt.Errorf("failed to get product: %w", err)
	}

	// 2. Get user's current points
	userStats, err := s.q.GetUserStats(ctx, userID) // Assuming GetUserStats exists
	if err != nil {
		return fmt.Errorf("failed to get user stats: %w", err)
	}

	// 3. Check if user has enough points
	if userStats.TotalPoints < int64(product.PointsPrice) {
		return errors.New("not enough points to purchase this product")
	}

	// Start a transaction for atomicity
	tx, err := s.db.BeginTx(ctx, nil) // Use s.db
	if err != nil {
		return fmt.Errorf("failed to begin transaction: %w", err)
	}
	defer tx.Rollback() // Rollback on error

	qtx := gen.New(tx) // Create new querier with transaction

	// 4. Create an order record
	_, err = qtx.CreateOrder(ctx, gen.CreateOrderParams{
		UserID:      userID,
		ProductID:   productID,
		PointsSpent: product.PointsPrice,
		Status:      "completed", // Assuming immediate completion
	})
	if err != nil {
		return fmt.Errorf("failed to create order: %w", err)
	}

	// 5. Decrement user's points
	err = qtx.UpdateUserStatsPoints(ctx, gen.UpdateUserStatsPointsParams{ // Assuming UpdateUserStatsPoints exists
		UserID:      userID,
		TotalPoints: userStats.TotalPoints - int64(product.PointsPrice),
	})
	if err != nil {
		return fmt.Errorf("failed to update user points: %w", err)
	}

	// 6. Decrement product stock if applicable
	if product.StockQuantity.Valid && product.StockQuantity.Int32 > 0 {
		err = qtx.UpdateProductStock(ctx, gen.UpdateProductStockParams{ // Assuming UpdateProductStock exists
			ID:            productID,
			StockQuantity: sql.NullInt32{Int32: product.StockQuantity.Int32 - 1, Valid: true},
		})
		if err != nil {
			return fmt.Errorf("failed to update product stock: %w", err)
		}
	}

	// 7. If the product is a course, enrolls the user in the course
	if product.Type == "course" && product.CategoryID.Valid {
		_, err = qtx.EnrollStudentInCourse(ctx, gen.EnrollStudentInCourseParams{ // Assuming EnrollStudentInCourse exists
			StudentID: userID,
			CourseID:  int64(product.CategoryID.Int32),
		})
		if err != nil {
			return fmt.Errorf("failed to enroll student in course: %w", err)
		}
	}

	return tx.Commit() // Commit the transaction
}
