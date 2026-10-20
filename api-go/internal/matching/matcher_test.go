package matching_test

import (
	"testing"
	"time"

	"github.com/google/uuid"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/matching"
)

func TestMatcherCorrespondenciaValida(t *testing.T) {
	m := matching.New()

	val := 50000.0
	op := domain.Oportunidade{
		ID:                 uuid.New(),
		NumeroControlePNCP: "12345678000199-1-000100/2026",
		Objeto:             "Aquisição de Equipamentos de Proteção Individual (EPI) e botinas de segurança",
		UnidadeUFSigla:     "SP",
		ValorTotalEstimado: &val,
		DataPublicacaoPNCP: time.Now(),
	}

	req := domain.MatchRequest{
		TermosObrigatorios: []string{"equipamentos de protecao", "botinas"},
		TermosExcluidos:    []string{"asfalto", "obras"},
		UFs:                []string{"SP", "RJ"},
	}

	res := m.Avaliar(op, req)

	if !res.Correspondente {
		t.Fatalf("Esperava correspondente=true, obteve false. Motivo: %s", res.MotivoCorrespondencia)
	}
	if res.PontuacaoRelevancia <= 0 {
		t.Errorf("Pontuacao deve ser maior que 0, obteve %d", res.PontuacaoRelevancia)
	}
	if res.MotivoCorrespondencia == "" {
		t.Error("Motivo da correspondência não pode ser vazio")
	}
}

func TestMatcherExclusaoDeTermo(t *testing.T) {
	m := matching.New()

	op := domain.Oportunidade{
		ID:                 uuid.New(),
		NumeroControlePNCP: "12345678000199-1-000101/2026",
		Objeto:             "Contratação de serviços de tecnologia com obras civis e pavimentação de asfalto",
		UnidadeUFSigla:     "SP",
	}

	req := domain.MatchRequest{
		TermosObrigatorios: []string{"tecnologia"},
		TermosExcluidos:    []string{"asfalto"},
		UFs:                []string{"SP"},
	}

	res := m.Avaliar(op, req)

	if res.Correspondente {
		t.Fatal("Esperava correspondente=false devido ao termo de exclusão 'asfalto'")
	}
}

func TestMatcherFiltroUFIncompativel(t *testing.T) {
	m := matching.New()

	op := domain.Oportunidade{
		ID:                 uuid.New(),
		NumeroControlePNCP: "12345678000199-1-000102/2026",
		Objeto:             "Aquisição de computadores e notebooks",
		UnidadeUFSigla:     "BA",
	}

	req := domain.MatchRequest{
		TermosObrigatorios: []string{"computadores"},
		UFs:                []string{"RS", "SC"},
	}

	res := m.Avaliar(op, req)

	if res.Correspondente {
		t.Fatal("Esperava correspondente=false devido a UF 'BA' não estar em RS, SC")
	}
}
