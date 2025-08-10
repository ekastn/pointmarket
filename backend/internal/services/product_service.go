package services

import (
	"context"
	"pointmarket/backend/internal/dtos"
	"pointmarket/backend/internal/store/gen"
)

// ProductService provides business logic for products
type ProductService struct {
	q gen.Querier
}

// NewProductService creates a new ProductService
func NewProductService(q gen.Querier) *ProductService {
	return &ProductService{q: q}
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

// GetProducts retrieves a list of products
func (s *ProductService) GetProducts(ctx context.Context) ([]dtos.ProductDTO, error) {
	products, err := s.q.GetProducts(ctx)
	if err != nil {
		return nil, err
	}

	var productDTOs []dtos.ProductDTO
	for _, product := range products {
		var productDTO dtos.ProductDTO
		productDTO.FromProductModel(product)
		productDTOs = append(productDTOs, productDTO)
	}
	return productDTOs, nil
}
