package config

import (
	"fmt"
	"log"
	"pointmarket/backend/internal/env"

	"github.com/joho/godotenv"
)

// Config holds the application configuration
type Config struct {
	DBHost         string
	DBPort         int
	DBUser         string
	DBPassword     string
	DBName         string
	JWTSecret      string
	JWTExpiration  int
	ServerPort     int
}

// Init loads the configuration from environment variables
func Init() *Config {
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found, using environment variables")
	}

	return &Config{
		DBHost:         env.GetString("DB_HOST", "localhost"),
		DBPort:         env.GetInt("DB_PORT", 3306),
		DBUser:         env.GetString("DB_USER", "root"),
		DBPassword:     env.GetString("DB_PASSWORD", ""),
		DBName:         env.GetString("DB_NAME", "pointmarket"),
		JWTSecret:      env.GetString("JWT_SECRET", "your-secret-key"),
		JWTExpiration:  env.GetInt("JWT_EXPIRATION_HOURS", 72),
		ServerPort:     env.GetInt("SERVER_PORT", 8080),
	}
}

// DSN returns the data source name for connecting to the database
func (c *Config) DSN() string {
	return fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?parseTime=true", c.DBUser, c.DBPassword, c.DBHost, c.DBPort, c.DBName)
}
