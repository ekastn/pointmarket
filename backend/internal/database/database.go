package database

import (
	"log"
	"pointmarket/backend/internal/config"

	_ "github.com/go-sql-driver/mysql"
	"github.com/jmoiron/sqlx"
)

// Connect connects to the database and returns a sqlx.DB instance
func Connect(cfg *config.Config) *sqlx.DB {
	db, err := sqlx.Connect("mysql", cfg.DSN())
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}

	if err = db.Ping(); err != nil {
		log.Fatalf("Failed to ping database: %v", err)
	}

	log.Println("Successfully connected to database")
	return db
}
