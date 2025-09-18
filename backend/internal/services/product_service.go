package services

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	mysql "github.com/go-sql-driver/mysql"
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

// GetProducts retrieves a list of products with pagination and filters
func (s *ProductService) GetProducts(ctx context.Context, page, limit int, search string, categoryID *int32, onlyActive bool) ([]dtos.ProductDTO, int64, error) {
	offset := (page - 1) * limit

	// Get total count of products
	var cIDParams sql.NullInt32
	if categoryID != nil {
		cIDParams = sql.NullInt32{Int32: *categoryID, Valid: true}
	}

	totalProducts, err := s.q.CountProducts(ctx, gen.CountProductsParams{
		CategoryID: cIDParams,
		OnlyActive: onlyActive,
		Search:     search,
	})
	if err != nil {
		return nil, 0, err
	}

	// Get the paginated list of products
	products, err := s.q.GetProducts(ctx, gen.GetProductsParams{
		Limit:      int32(limit),
		Offset:     int32(offset),
		CategoryID: cIDParams,
		OnlyActive: onlyActive,
		Search:     search,
	})
	if err != nil {
		return nil, 0, err
	}

	var productDTOs []dtos.ProductDTO
	for _, p := range products {
		var dto dtos.ProductDTO
		dto.ID = p.ID
		if p.CategoryID.Valid {
			v := p.CategoryID.Int32
			dto.CategoryID = &v
		}
		if p.StockQuantity.Valid {
			v := p.StockQuantity.Int32
			dto.StockQuantity = &v
		}
		dto.Name = p.Name
		if p.Description.Valid {
			s := p.Description.String
			dto.Description = &s
		}
		dto.PointsPrice = p.PointsPrice
		dto.Type = p.Type
		dto.IsActive = p.IsActive
		dto.Metadata = p.Metadata
		dto.CreatedAt = p.CreatedAt
		dto.UpdatedAt = p.UpdatedAt
		if p.CategoryName.Valid {
			s := p.CategoryName.String
			dto.CategoryName = &s
		}
		productDTOs = append(productDTOs, dto)
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
	userStats, err := s.q.GetUserStats(ctx, userID)
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
	orderRes, err := qtx.CreateOrder(ctx, gen.CreateOrderParams{
		UserID:      userID,
		ProductID:   productID,
		PointsSpent: product.PointsPrice,
		Status:      "completed", // Assuming immediate completion
	})
	if err != nil {
		return fmt.Errorf("failed to create order: %w", err)
	}

	// Get order ID for ledger reference
	orderID, err := orderRes.LastInsertId()
	if err != nil {
		return fmt.Errorf("failed to get order id: %w", err)
	}

	// 5. Ledgered points deduction inside tx (idempotent by order reference)
	statsInside, err := qtx.GetUserStats(ctx, userID)
	if err != nil {
		return fmt.Errorf("failed to get user stats in tx: %w", err)
	}
	if statsInside.TotalPoints < int64(product.PointsPrice) {
		return errors.New("not enough points to purchase this product")
	}

	if _, err := qtx.CreatePointsTransaction(ctx, gen.CreatePointsTransactionParams{
		UserID:        userID,
		Amount:        int32(-product.PointsPrice),
		Reason:        sql.NullString{},
		ReferenceType: sql.NullString{String: "order", Valid: true},
		ReferenceID:   sql.NullInt64{Int64: orderID, Valid: true},
	}); err != nil {
		if me, ok := err.(*mysql.MySQLError); ok && me.Number == 1062 {
			// duplicate transaction (idempotent) → continue
		} else {
			return fmt.Errorf("failed to record points transaction: %w", err)
		}
	}

	newTotal := statsInside.TotalPoints - int64(product.PointsPrice)
	if err := qtx.UpdateUserStatsPoints(ctx, gen.UpdateUserStatsPointsParams{
		TotalPoints: newTotal,
		UserID:      userID,
	}); err != nil {
		return fmt.Errorf("failed to update user points: %w", err)
	}

	// 6. Decrement product stock atomically if stock is managed
	if product.StockQuantity.Valid {
		res, err := qtx.DecrementProductStockIfAvailable(ctx, productID)
		if err != nil {
			return fmt.Errorf("failed to decrement product stock: %w", err)
		}
		if rows, _ := res.RowsAffected(); rows == 0 {
			return errors.New("out of stock")
		}
	}

	// 7. If the product is a course, enroll the user in the linked course via product_course_details
	if product.Type == "course" {
		detail, err := qtx.GetProductCourseDetailByProductID(ctx, productID)
		if err != nil && err != sql.ErrNoRows {
			return fmt.Errorf("failed to fetch product course detail: %w", err)
		}
		if err == nil { // detail found
			_, err = qtx.EnrollStudentInCourse(ctx, gen.EnrollStudentInCourseParams{
				UserID:   userID,
				CourseID: detail.CourseID,
			})
			if err != nil {
				if me, ok := err.(*mysql.MySQLError); ok && me.Number == 1062 {
					// already enrolled; ignore
				} else {
					return fmt.Errorf("failed to enroll student in course: %w", err)
				}
			}
		}
	}

	return tx.Commit() // Commit the transaction
}

// CreateProductCategory creates a new product category
func (s *ProductService) CreateProductCategory(ctx context.Context, req dtos.CreateProductCategoryRequestDTO) (dtos.ProductCategoryDTO, error) {
	result, err := s.q.CreateProductCategory(ctx, gen.CreateProductCategoryParams{
		Name:        req.Name,
		Description: sql.NullString{String: *req.Description, Valid: req.Description != nil},
	})
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	id, err := result.LastInsertId()
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	category, err := s.q.GetProductCategoryByID(ctx, int32(id))
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	var categoryDTO dtos.ProductCategoryDTO
	categoryDTO.ID = category.ID
	categoryDTO.Name = category.Name
	if category.Description.Valid {
		categoryDTO.Description = &category.Description.String
	} else {
		categoryDTO.Description = nil
	}
	return categoryDTO, nil
}

// GetProductCategoryByID retrieves a single product category by its ID
func (s *ProductService) GetProductCategoryByID(ctx context.Context, id int32) (dtos.ProductCategoryDTO, error) {
	category, err := s.q.GetProductCategoryByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.ProductCategoryDTO{}, nil // Category not found
	}
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	var categoryDTO dtos.ProductCategoryDTO
	categoryDTO.ID = category.ID
	categoryDTO.Name = category.Name
	if category.Description.Valid {
		categoryDTO.Description = &category.Description.String
	} else {
		categoryDTO.Description = nil
	}
	return categoryDTO, nil
}

// GetProductCategories retrieves a list of all product categories with pagination
func (s *ProductService) GetProductCategories(ctx context.Context, page, limit int, search string) ([]dtos.ProductCategoryDTO, int64, error) {
	offset := (page - 1) * limit

	// Get total count of product categories
	totalCategories, err := s.q.CountProductCategories(ctx)
	if err != nil {
		return nil, 0, err
	}

	// Get the paginated list of product categories
	categories, err := s.q.GetProductCategories(ctx, gen.GetProductCategoriesParams{
		Limit:  int32(limit),
		Offset: int32(offset),
	})
	if err != nil {
		return nil, 0, err
	}

	var categoryDTOs []dtos.ProductCategoryDTO
	for _, category := range categories {
		var categoryDTO dtos.ProductCategoryDTO
		categoryDTO.ID = category.ID
		categoryDTO.Name = category.Name
		if category.Description.Valid {
			categoryDTO.Description = &category.Description.String
		} else {
			categoryDTO.Description = nil
		}
		categoryDTOs = append(categoryDTOs, categoryDTO)
	}
	return categoryDTOs, totalCategories, nil
}

// UpdateProductCategory updates an existing product category
func (s *ProductService) UpdateProductCategory(ctx context.Context, id int32, req dtos.UpdateProductCategoryRequestDTO) (dtos.ProductCategoryDTO, error) {
	// Get existing category to apply partial updates
	existingCategory, err := s.q.GetProductCategoryByID(ctx, id)
	if err == sql.ErrNoRows {
		return dtos.ProductCategoryDTO{}, nil // Category not found
	}
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	// Apply updates
	name := existingCategory.Name
	if req.Name != nil {
		name = *req.Name
	}

	description := existingCategory.Description
	if req.Description != nil {
		description = sql.NullString{String: *req.Description, Valid: true}
	}

	err = s.q.UpdateProductCategory(ctx, gen.UpdateProductCategoryParams{
		ID:          id,
		Name:        name,
		Description: description,
	})
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	updatedCategory, err := s.q.GetProductCategoryByID(ctx, id)
	if err != nil {
		return dtos.ProductCategoryDTO{}, err
	}

	var categoryDTO dtos.ProductCategoryDTO
	categoryDTO.ID = updatedCategory.ID
	categoryDTO.Name = updatedCategory.Name
	if updatedCategory.Description.Valid {
		categoryDTO.Description = &updatedCategory.Description.String
	} else {
		categoryDTO.Description = nil
	}
	return categoryDTO, nil
}

// DeleteProductCategory deletes a product category by its ID
func (s *ProductService) DeleteProductCategory(ctx context.Context, id int32) error {
	return s.q.DeleteProductCategory(ctx, id)
}
