package auth_test

import (
	"net/http"
	"net/http/httptest"
	"testing"

	"github.com/radar-compras/radar-compras-publicas/api-go/internal/auth"
)

func TestAuthMiddleware(t *testing.T) {
	tokenSecreto := "token-teste-ultra-seguro-123"
	mw := auth.Middleware(tokenSecreto)

	dummyHandler := mw(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
		w.Write([]byte("ok"))
	}))

	// 1. Sem token -> 401
	reqSem := httptest.NewRequest(http.MethodGet, "/privado", nil)
	recSem := httptest.NewRecorder()
	dummyHandler.ServeHTTP(recSem, reqSem)
	if recSem.Code != http.StatusUnauthorized {
		t.Errorf("Esperava 401, obteve %d", recSem.Code)
	}

	// 2. Token incorreto -> 401
	reqErr := httptest.NewRequest(http.MethodGet, "/privado", nil)
	reqErr.Header.Set("X-Internal-Token", "token-incorreto")
	recErr := httptest.NewRecorder()
	dummyHandler.ServeHTTP(recErr, reqErr)
	if recErr.Code != http.StatusUnauthorized {
		t.Errorf("Esperava 401, obteve %d", recErr.Code)
	}

	// 3. Token correto via header X-Internal-Token -> 200
	reqOk := httptest.NewRequest(http.MethodGet, "/privado", nil)
	reqOk.Header.Set("X-Internal-Token", tokenSecreto)
	recOk := httptest.NewRecorder()
	dummyHandler.ServeHTTP(recOk, reqOk)
	if recOk.Code != http.StatusOK {
		t.Errorf("Esperava 200, obteve %d", recOk.Code)
	}

	// 4. Token correto via Bearer -> 200
	reqBearer := httptest.NewRequest(http.MethodGet, "/privado", nil)
	reqBearer.Header.Set("Authorization", "Bearer "+tokenSecreto)
	recBearer := httptest.NewRecorder()
	dummyHandler.ServeHTTP(recBearer, reqBearer)
	if recBearer.Code != http.StatusOK {
		t.Errorf("Esperava 200, obteve %d", recBearer.Code)
	}
}
