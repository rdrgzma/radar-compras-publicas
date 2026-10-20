package service

import (
	"context"

	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/matching"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/storage"
)

type OportunidadeService struct {
	store   *storage.Storage
	matcher *matching.Matcher
}

func NewOportunidadeService(store *storage.Storage) *OportunidadeService {
	return &OportunidadeService{
		store:   store,
		matcher: matching.New(),
	}
}

// ListarOportunidades consulta oportunidades sem aplicar regras de perfil.
func (s *OportunidadeService) ListarOportunidades(ctx context.Context, p storage.ListOportunidadesParams) ([]domain.Oportunidade, int, error) {
	return s.store.ListOportunidades(ctx, p)
}

// ObterPorNumeroControle retorna uma oportunidade por seu número oficial.
func (s *OportunidadeService) ObterPorNumeroControle(ctx context.Context, numeroControle string) (*domain.Oportunidade, error) {
	return s.store.GetOportunidadeByNumeroControle(ctx, numeroControle)
}

// AvaliarMatch avalia as oportunidades frente aos critérios do perfil do fornecedor e retorna com a justificativa de correspondência.
func (s *OportunidadeService) AvaliarMatch(ctx context.Context, req domain.MatchRequest) ([]domain.OportunidadeMatch, int, error) {
	if req.Pagina < 1 {
		req.Pagina = 1
	}
	if req.PorPagina < 1 || req.PorPagina > 100 {
		req.PorPagina = 15
	}

	// Busca oportunidades candidatas vigentes
	params := storage.ListOportunidadesParams{
		Pagina:               1,
		PorPagina:            500, // Janela ampla para filtragem e matching em memória
		ApenasVigentes:       req.ApenasVigentes,
		IncluirDemonstrativo: req.IncluirDemonstrativo,
	}

	todas, _, err := s.store.ListOportunidades(ctx, params)
	if err != nil {
		return nil, 0, err
	}

	var correspondentes []domain.OportunidadeMatch
	for _, op := range todas {
		resultado := s.matcher.Avaliar(op, req)
		if resultado.Correspondente {
			correspondentes = append(correspondentes, resultado)
		}
	}

	total := len(correspondentes)
	inicio := (req.Pagina - 1) * req.PorPagina
	fim := inicio + req.PorPagina

	if inicio >= total {
		return []domain.OportunidadeMatch{}, total, nil
	}
	if fim > total {
		fim = total
	}

	paginadas := correspondentes[inicio:fim]
	return paginadas, total, nil
}
