package config

import (
	"fmt"
	"log"
	"pointmarket/backend/internal/env"

	"github.com/joho/godotenv"
)

// Config holds the application configuration
type Config struct {
	DBHost                   string
	DBPort                   int
	DBUser                   string
	DBPassword               string
	DBName                   string
	JWTSecret                string
	JWTExpiration            int
	ServerPort               int
	AllowedOrigins           string
	AIServiceURL             string
	RecommendationServiceURL string
	GCSBucket                string
	GCSPublicBaseURL         string
	MaxAvatarMB              int
	GeminiAPIKey             string
	GeminiModel              string
}

// Init loads the configuration from environment variables
func Init() *Config {
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found, using environment variables")
	}

	return &Config{
		DBHost:                   env.GetString("DB_HOST", "localhost"),
		DBPort:                   env.GetInt("DB_PORT", 3306),
		DBUser:                   env.GetString("DB_USER", "root"),
		DBPassword:               env.GetString("DB_PASSWORD", ""),
		DBName:                   env.GetString("DB_NAME", "pointmarket"),
		JWTSecret:                env.GetString("JWT_SECRET", "your-secret-key"),
		JWTExpiration:            env.GetInt("JWT_EXPIRATION_HOURS", 72),
		ServerPort:               env.GetInt("SERVER_PORT", 8080),
		AllowedOrigins:           env.GetString("ALLOWED_ORIGINS", "http://localhost:8081"),
		AIServiceURL:             env.GetString("AI_SERVICE_URL", "http://localhost:5000"),
		RecommendationServiceURL: env.GetString("RECOMMENDATION_SERVICE_URL", "http://localhost:5000"),
		GCSBucket:                env.GetString("GCS_BUCKET", ""),
		GCSPublicBaseURL:         env.GetString("GCS_PUBLIC_BASE_URL", ""),
		MaxAvatarMB:              env.GetInt("MAX_AVATAR_MB", 5),
		GeminiAPIKey:             env.GetString("GEMINI_API_KEY", ""),
        GeminiModel:              env.GetString("GEMINI_MODEL", "gemini-2.5-flash"),
	}
}

// DSN returns the data source name for connecting to the database
func (c *Config) DSN() string {
	return fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?parseTime=true", c.DBUser, c.DBPassword, c.DBHost, c.DBPort, c.DBName)
}
