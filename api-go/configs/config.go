package configs

import (
	"os"
)

type Config struct {
	Port                string
	AppEnv              string
	LogLevel            string
	DBHost              string
	DBPort              string
	DBUser              string
	DBPassword          string
	DBName              string
	DBSchema            string
	DBSSLMode           string
	RadarInternalToken  string
	TimeZone            string
}

func Load() *Config {
	return &Config{
		Port:               getEnv("PORT", "8000"),
		AppEnv:             getEnv("APP_ENV", "development"),
		LogLevel:           getEnv("LOG_LEVEL", "info"),
		DBHost:             getEnv("DB_HOST", "127.0.0.1"),
		DBPort:             getEnv("DB_PORT", "5432"),
		DBUser:             getEnv("DB_USER", "postgres"),
		DBPassword:         getEnv("DB_PASSWORD", "postgres"),
		DBName:             getEnv("DB_NAME", "radar_compras"),
		DBSchema:           getEnv("DB_SCHEMA", "api_go"),
		DBSSLMode:          getEnv("DB_SSLMODE", "disable"),
		RadarInternalToken: getEnv("RADAR_INTERNAL_TOKEN", "radar-token-interno-ultra-seguro-compartilhado-com-painel"),
		TimeZone:           getEnv("TZ", "America/Sao_Paulo"),
	}
}

func getEnv(key, fallback string) string {
	if val := os.Getenv(key); val != "" {
		return val
	}
	return fallback
}
