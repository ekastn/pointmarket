package store

import (
	"database/sql"
	"pointmarket/backend/internal/models"

	"github.com/jmoiron/sqlx"
)

// MaterialStore handles database operations for materials
type MaterialStore struct {
	db *sqlx.DB
}

// NewMaterialStore creates a new MaterialStore
func NewMaterialStore(db *sqlx.DB) *MaterialStore {
	return &MaterialStore{db: db}
}

// GetAllMaterials retrieves all materials
func (s *MaterialStore) GetAllMaterials() ([]models.Material, error) {
	var materials []models.Material
	err := s.db.Select(&materials, "SELECT id, title, description, subject, teacher_id, file_path, file_type, status, created_at, updated_at FROM materials WHERE status = 'active'")
	if err != nil {
		return nil, err
	}
	return materials, nil
}

// GetMaterialByID retrieves a material by its ID
func (s *MaterialStore) GetMaterialByID(id int) (*models.Material, error) {
	var material models.Material
	err := s.db.Get(&material, "SELECT id, title, description, subject, teacher_id, file_path, file_type, status, created_at, updated_at FROM materials WHERE id = ?", id)
	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	return &material, nil
}

// CreateMaterial inserts a new material into the database
func (s *MaterialStore) CreateMaterial(material *models.Material) error {
	query := `INSERT INTO materials (title, description, subject, teacher_id, file_path, file_type, status) VALUES (?, ?, ?, ?, ?, ?, ?)`
	result, err := s.db.Exec(query, material.Title, material.Description, material.Subject, material.TeacherID, material.FilePath, material.FileType, material.Status)
	if err != nil {
		return err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return err
	}
	material.ID = int(id)
	return nil
}

// UpdateMaterial updates an existing material in the database
func (s *MaterialStore) UpdateMaterial(material *models.Material) error {
	query := `UPDATE materials SET title = ?, description = ?, subject = ?, file_path = ?, file_type = ?, status = ?, updated_at = NOW() WHERE id = ?`
	_, err := s.db.Exec(query, material.Title, material.Description, material.Subject, material.FilePath, material.FileType, material.Status, material.ID)
	return err
}

// DeleteMaterial deletes a material from the database (sets status to inactive)
func (s *MaterialStore) DeleteMaterial(id int) error {
	query := `UPDATE materials SET status = 'inactive', updated_at = NOW() WHERE id = ?`
	_, err := s.db.Exec(query, id)
	return err
}
