package http

import (
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/auth"
)

func NewRouter(h *Handler, internalToken string) http.Handler {
	r := chi.NewRouter()

	// Middlewares padrão
	r.Use(middleware.RequestID)
	r.Use(middleware.RealIP)
	r.Use(middleware.Logger)
	r.Use(middleware.Recoverer)

	// Rota pública de healthcheck
	r.Get("/saude", h.Healthcheck)

	// Rotas privadas com autenticação server-to-server
	r.Route("/api/v1", func(r chi.Router) {
		r.Use(auth.Middleware(internalToken))

		// Sincronizações
		r.Post("/sync", h.IniciarSincronizacao)
		r.Get("/sync/{id}", h.GetSincronizacao)
		r.Get("/sync", h.ListSincronizacoes)

		// Oportunidades
		r.Get("/oportunidades", h.ListOportunidades)
		r.Get("/oportunidades/{numero_controle_pncp}", h.GetOportunidade)
		r.Post("/oportunidades/match", h.MatchOportunidades)

		// Modo Demonstrativo
		r.Post("/demonstracao/carregar", h.CarregarDemonstracao)
	})

	return r
}
