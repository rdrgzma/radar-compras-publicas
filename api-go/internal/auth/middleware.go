package auth

import (
	"crypto/subtle"
	"encoding/json"
	"net/http"
	"strings"
)

// Middleware valida o cabeçalho X-Internal-Token para comunicação privada entre serviços.
func Middleware(tokenEsperado string) func(http.Handler) http.Handler {
	return func(next http.Handler) http.Handler {
		return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
			tokenRecebido := r.Header.Get("X-Internal-Token")
			if tokenRecebido == "" {
				authHeader := r.Header.Get("Authorization")
				if strings.HasPrefix(authHeader, "Bearer ") {
					tokenRecebido = strings.TrimPrefix(authHeader, "Bearer ")
				}
			}

			// Comparação com proteção contra timing attack
			if subtle.ConstantTimeCompare([]byte(tokenRecebido), []byte(tokenEsperado)) != 1 {
				w.Header().Set("Content-Type", "application/json; charset=utf-8")
				w.WriteHeader(http.StatusUnauthorized)
				json.NewEncoder(w).Encode(map[string]interface{}{
					"erro":     "nao_autorizado",
					"mensagem": "Token interno de integração ausente ou inválido.",
				})
				return
			}

			next.ServeHTTP(w, r)
		})
	}
}
