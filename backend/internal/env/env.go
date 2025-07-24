package env

import (
	"os"
	"strconv"
)

// GetString retrieves the value of the environment variable named by the key.
// It returns the value, which will be the default value if the variable is not present.
func GetString(key string, defaultValue string) string {
	if value, exists := os.LookupEnv(key); exists {
		return value
	}
	return defaultValue
}

// GetInt retrieves the value of the environment variable named by the key.
// It returns the value, which will be the default value if the variable is not present or not an integer.
func GetInt(key string, defaultValue int) int {
	valueStr := GetString(key, "")
	if value, err := strconv.Atoi(valueStr); err == nil {
		return value
	}
	return defaultValue
}
