package matching

import (
	"fmt"
	"strings"
	"unicode"

	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
	"golang.org/x/text/runes"
	"golang.org/x/text/transform"
	"golang.org/x/text/unicode/norm"
)

// Matcher é o motor de correspondência semântica e contextual.
type Matcher struct{}

func New() *Matcher {
	return &Matcher{}
}

// Avaliar avalia se uma oportunidade atende ao perfil do fornecedor e constrói a justificativa explícita.
func (m *Matcher) Avaliar(o domain.Oportunidade, req domain.MatchRequest) domain.OportunidadeMatch {
	textoCompleto := strings.ToLower(removerAcentos(o.Objeto + " " + o.InformacaoComplementar + " " + o.OrgaoRazaoSocial))

	// 1. Verificação de UF
	ufCompativeis := false
	if len(req.UFs) == 0 {
		ufCompativeis = true
	} else {
		for _, uf := range req.UFs {
			if strings.EqualFold(strings.TrimSpace(uf), strings.TrimSpace(o.UnidadeUFSigla)) {
				ufCompativeis = true
				break
			}
		}
	}

	if !ufCompativeis {
		return domain.OportunidadeMatch{
			Oportunidade:          o,
			Correspondente:        false,
			MotivoCorrespondencia: fmt.Sprintf("UF '%s' não está contemplada nas regiões de interesse do perfil (%s).", o.UnidadeUFSigla, strings.Join(req.UFs, ", ")),
			PontuacaoRelevancia:   0,
		}
	}

	// 2. Verificação de Termos Excluídos (veto imediato)
	for _, excluido := range req.TermosExcluidos {
		termLimpo := strings.ToLower(removerAcentos(strings.TrimSpace(excluido)))
		if termLimpo != "" && strings.Contains(textoCompleto, termLimpo) {
			return domain.OportunidadeMatch{
				Oportunidade:          o,
				Correspondente:        false,
				MotivoCorrespondencia: fmt.Sprintf("Contém termo de exclusão '%s' configurado no perfil.", excluido),
				PontuacaoRelevancia:   0,
			}
		}
	}

	// 3. Verificação de Termos Obrigatórios / Incluídos
	var termosEncontrados []string
	if len(req.TermosObrigatorios) > 0 {
		for _, obrigatorio := range req.TermosObrigatorios {
			termLimpo := strings.ToLower(removerAcentos(strings.TrimSpace(obrigatorio)))
			if termLimpo != "" && strings.Contains(textoCompleto, termLimpo) {
				termosEncontrados = append(termosEncontrados, obrigatorio)
			}
		}

		if len(termosEncontrados) == 0 {
			return domain.OportunidadeMatch{
				Oportunidade:          o,
				Correspondente:        false,
				MotivoCorrespondencia: fmt.Sprintf("Nenhum dos termos obrigatórios (%s) foi identificado no objeto ou edital.", strings.Join(req.TermosObrigatorios, ", ")),
				PontuacaoRelevancia:   10,
			}
		}
	}

	// 4. Construção da Justificativa Transparente
	var partesJustificativa []string
	if len(termosEncontrados) > 0 {
		partesJustificativa = append(partesJustificativa, fmt.Sprintf("Termo(s) correspondente(s) identificado(s): '%s'", strings.Join(termosEncontrados, "', '")))
	} else {
		partesJustificativa = append(partesJustificativa, "Perfil sem restrição de palavras-chave")
	}

	if len(req.UFs) > 0 {
		partesJustificativa = append(partesJustificativa, fmt.Sprintf("UF '%s' compatível com a abrangência configurada", o.UnidadeUFSigla))
	} else {
		partesJustificativa = append(partesJustificativa, "Abrangência nacional aceita")
	}

	if len(req.TermosExcluidos) > 0 {
		partesJustificativa = append(partesJustificativa, "Nenhum termo de exclusão detectado")
	}

	motivoFinal := strings.Join(partesJustificativa, "; ") + "."

	// Cálculo de score
	score := 50
	if len(termosEncontrados) > 0 {
		score += len(termosEncontrados) * 20
		if score > 100 {
			score = 100
		}
	} else {
		score = 60
	}

	return domain.OportunidadeMatch{
		Oportunidade:          o,
		Correspondente:        true,
		MotivoCorrespondencia: motivoFinal,
		PontuacaoRelevancia:   score,
	}
}

// removerAcentos remove diacríticos para comparação textual insensível a acentuação.
func removerAcentos(s string) string {
	t := transform.Chain(norm.NFD, runes.Remove(runes.In(unicode.Mn)), norm.NFC)
	saida, _, err := transform.String(t, s)
	if err != nil {
		return s
	}
	return saida
}
