package http

import (
	"context"
	"encoding/json"
	"net/http"
	"strconv"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/google/uuid"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/service"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/storage"
)

type Handler struct {
	store       *storage.Storage
	syncService *service.SyncService
	opService   *service.OportunidadeService
}

func NewHandler(store *storage.Storage, syncService *service.SyncService, opService *service.OportunidadeService) *Handler {
	return &Handler{
		store:       store,
		syncService: syncService,
		opService:   opService,
	}
}

func (h *Handler) Healthcheck(w http.ResponseWriter, r *http.Request) {
	ctx, cancel := contextWithTimeout(r.Context(), 2*time.Second)
	defer cancel()

	dbStatus := "conectado"
	statusCode := http.StatusOK

	if err := h.store.DB().PingContext(ctx); err != nil {
		dbStatus = "indisponivel: " + err.Error()
		statusCode = http.StatusServiceUnavailable
	}

	responderJSON(w, statusCode, map[string]interface{}{
		"status":         "ok",
		"versao":         "1.0.0",
		"banco_de_dados": dbStatus,
		"conector_pncp":  "disponivel",
		"fuso_horario":   "America/Sao_Paulo",
		"timestamp":      time.Now().Format(time.RFC3339),
	})
}

func (h *Handler) IniciarSincronizacao(w http.ResponseWriter, r *http.Request) {
	var body struct {
		DataInicial string `json:"data_inicial"`
		DataFinal   string `json:"data_final"`
		Modalidades []int  `json:"modalidades"`
		Modo        string `json:"modo"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		responderErro(w, http.StatusBadRequest, "requisicao_invalida", "Corpo JSON malformado.")
		return
	}

	if body.DataInicial == "" {
		body.DataInicial = time.Now().Format("2006-01-02")
	}
	if body.DataFinal == "" {
		body.DataFinal = time.Now().Format("2006-01-02")
	}

	syncJob, err := h.syncService.IniciarSincronizacao(r.Context(), service.IniciarSyncParams{
		DataInicial: body.DataInicial,
		DataFinal:   body.DataFinal,
		Modalidades: body.Modalidades,
		Modo:        body.Modo,
	})

	if err != nil {
		responderErro(w, http.StatusConflict, "concorrencia_ou_erro", err.Error())
		return
	}

	responderJSON(w, http.StatusAccepted, map[string]interface{}{
		"id":       syncJob.ID,
		"status":   syncJob.Status,
		"mensagem": "Sincronização iniciada com sucesso em segundo plano.",
	})
}

func (h *Handler) GetSincronizacao(w http.ResponseWriter, r *http.Request) {
	idStr := chi.URLParam(r, "id")
	id, err := uuid.Parse(idStr)
	if err != nil {
		responderErro(w, http.StatusBadRequest, "id_invalido", "O identificador informado não é um UUID válido.")
		return
	}

	syncJob, err := h.store.GetSincronizacao(r.Context(), id)
	if err != nil {
		responderErro(w, http.StatusInternalServerError, "erro_banco", err.Error())
		return
	}
	if syncJob == nil {
		responderErro(w, http.StatusNotFound, "nao_encontrado", "Execução de sincronização não localizada.")
		return
	}

	responderJSON(w, http.StatusOK, syncJob)
}

func (h *Handler) ListSincronizacoes(w http.ResponseWriter, r *http.Request) {
	pagina, _ := strconv.Atoi(r.URL.Query().Get("pagina"))
	porPagina, _ := strconv.Atoi(r.URL.Query().Get("por_pagina"))
	statusFiltro := r.URL.Query().Get("status")

	if pagina < 1 {
		pagina = 1
	}
	if porPagina < 1 {
		porPagina = 15
	}

	dados, total, err := h.store.ListSincronizacoes(r.Context(), pagina, porPagina, statusFiltro)
	if err != nil {
		responderErro(w, http.StatusInternalServerError, "erro_banco", err.Error())
		return
	}

	responderJSON(w, http.StatusOK, map[string]interface{}{
		"dados":      dados,
		"total":      total,
		"pagina":     pagina,
		"por_pagina": porPagina,
	})
}

func (h *Handler) ListOportunidades(w http.ResponseWriter, r *http.Request) {
	pagina, _ := strconv.Atoi(r.URL.Query().Get("pagina"))
	porPagina, _ := strconv.Atoi(r.URL.Query().Get("por_pagina"))
	busca := r.URL.Query().Get("busca")
	uf := r.URL.Query().Get("uf")
	modalidadeID, _ := strconv.Atoi(r.URL.Query().Get("modalidade_id"))
	apenasVigentes := r.URL.Query().Get("apenas_vigentes") != "false"
	incluirDemo := r.URL.Query().Get("incluir_demonstrativos") != "false"

	dados, total, err := h.opService.ListarOportunidades(r.Context(), storage.ListOportunidadesParams{
		Pagina:               pagina,
		PorPagina:            porPagina,
		Busca:                busca,
		UF:                   uf,
		ModalidadeID:         modalidadeID,
		ApenasVigentes:       apenasVigentes,
		IncluirDemonstrativo: incluirDemo,
	})

	if err != nil {
		responderErro(w, http.StatusInternalServerError, "erro_banco", err.Error())
		return
	}

	responderJSON(w, http.StatusOK, map[string]interface{}{
		"dados":      dados,
		"total":      total,
		"pagina":     pagina,
		"por_pagina": porPagina,
	})
}

func (h *Handler) GetOportunidade(w http.ResponseWriter, r *http.Request) {
	numControle := chi.URLParam(r, "numero_controle_pncp")
	op, err := h.opService.ObterPorNumeroControle(r.Context(), numControle)
	if err != nil {
		responderErro(w, http.StatusInternalServerError, "erro_banco", err.Error())
		return
	}
	if op == nil {
		responderErro(w, http.StatusNotFound, "nao_encontrado", "Oportunidade não localizada no radar.")
		return
	}

	responderJSON(w, http.StatusOK, op)
}

func (h *Handler) MatchOportunidades(w http.ResponseWriter, r *http.Request) {
	var req domain.MatchRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		responderErro(w, http.StatusBadRequest, "requisicao_invalida", "Critérios de perfil malformados.")
		return
	}

	dados, total, err := h.opService.AvaliarMatch(r.Context(), req)
	if err != nil {
		responderErro(w, http.StatusInternalServerError, "erro_matching", err.Error())
		return
	}

	responderJSON(w, http.StatusOK, map[string]interface{}{
		"dados":      dados,
		"total":      total,
		"pagina":     req.Pagina,
		"por_pagina": req.PorPagina,
	})
}

func (h *Handler) CarregarDemonstracao(w http.ResponseWriter, r *http.Request) {
	qtd, err := h.store.SeedDemonstrativos(r.Context())
	if err != nil {
		responderErro(w, http.StatusInternalServerError, "erro_seed", err.Error())
		return
	}

	responderJSON(w, http.StatusOK, map[string]interface{}{
		"sucesso":    true,
		"mensagem":   strconv.Itoa(qtd) + " oportunidades demonstrativas inseridas com sucesso.",
		"quantidade": qtd,
	})
}

func responderJSON(w http.ResponseWriter, status int, payload interface{}) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(payload)
}

func responderErro(w http.ResponseWriter, status int, codigo, mensagem string) {
	responderJSON(w, status, map[string]interface{}{
		"erro":     codigo,
		"mensagem": mensagem,
	})
}

func contextWithTimeout(ctx context.Context, d time.Duration) (context.Context, context.CancelFunc) {
	return context.WithTimeout(ctx, d)
}
