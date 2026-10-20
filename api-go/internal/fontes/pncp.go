package fontes

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"strings"
	"time"

	"github.com/google/uuid"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
)

const (
	PNCPBaseURL       = "https://pncp.gov.br/api/consulta/v1/contratacoes/publicacao"
	DefaultTimeout    = 15 * time.Second
	MaxRetries        = 3
	DefaultPageSize   = 20
	BaseBackoffMillis = 500
)

type PNCPClient struct {
	httpClient *http.Client
	baseURL    string
}

func NewPNCPClient(customBaseURL string) *PNCPClient {
	url := PNCPBaseURL
	if customBaseURL != "" {
		url = customBaseURL
	}
	return &PNCPClient{
		httpClient: &http.Client{
			Timeout: DefaultTimeout,
		},
		baseURL: url,
	}
}

// RespostaPNCP representa o envelope JSON retornado pelo endpoint oficial do PNCP.
type RespostaPNCP struct {
	Data            []ItemContratacaoPNCP `json:"data"`
	TotalRegistros  int                   `json:"totalRegistros"`
	TotalPaginas    int                   `json:"totalPaginas"`
	NumeroPagina    int                   `json:"numeroPagina"`
	PaginasRestantes int                  `json:"paginasRestantes"`
	Empty           bool                  `json:"empty"`
}

type ItemContratacaoPNCP struct {
	NumeroControlePNCP          string                `json:"numeroControlePNCP"`
	AnoCompra                   int                   `json:"anoCompra"`
	SequencialCompra            int                   `json:"sequencialCompra"`
	NumeroCompra                string                `json:"numeroCompra"`
	Processo                    string                `json:"processo"`
	ObjetoCompra                string                `json:"objetoCompra"`
	InformacaoComplementar      *string               `json:"informacaoComplementar"`
	ModalidadeID                int                   `json:"modalidadeId"`
	ModalidadeNome              string                `json:"modalidadeNome"`
	ModoDisputaID               *int                  `json:"modoDisputaId"`
	ModoDisputaNome             *string               `json:"modoDisputaNome"`
	SituacaoCompraID            int                   `json:"situacaoCompraId"`
	SituacaoCompraNome          string                `json:"situacaoCompraNome"`
	ValorTotalEstimado          *float64              `json:"valorTotalEstimado"`
	ValorTotalHomologado        *float64              `json:"valorTotalHomologado"`
	DataPublicacaoPNCP          string                `json:"dataPublicacaoPncp"`
	DataAberturaProposta        *string               `json:"dataAberturaProposta"`
	DataEncerramentoProposta    *string               `json:"dataEncerramentoProposta"`
	DataAtualizacao             *string               `json:"dataAtualizacao"`
	LinkSistemaOrigem           *string               `json:"linkSistemaOrigem"`
	LinkProcessoEletronico      *string               `json:"linkProcessoEletronico"`
	OrgaoEntidade               OrgaoEntidadePNCP     `json:"orgaoEntidade"`
	UnidadeOrgao                UnidadeOrgaoPNCP      `json:"unidadeOrgao"`
}

type OrgaoEntidadePNCP struct {
	CNPJ         string  `json:"cnpj"`
	RazaoSocial  string  `json:"razaoSocial"`
	PoderID      *string `json:"poderId"`
	EsferaID     *string `json:"esferaId"`
}

type UnidadeOrgaoPNCP struct {
	UfSigla        string  `json:"ufSigla"`
	UfNome         *string `json:"ufNome"`
	MunicipioNome  *string `json:"municipioNome"`
	NomeUnidade    *string `json:"nomeUnidade"`
	CodigoIBGE     *string `json:"codigoIbge"`
}

// FetchPagina consulta uma única página no PNCP aplicando retry e backoff progressivo.
func (c *PNCPClient) FetchPagina(ctx context.Context, dataInicial, dataFinal string, modalidadeID, pagina, tamanhoPagina int) (*RespostaPNCP, error) {
	if tamanhoPagina < 10 {
		tamanhoPagina = 10
	}

	url := fmt.Sprintf("%s?dataInicial=%s&dataFinal=%s&codigoModalidadeContratacao=%d&pagina=%d&tamanhoPagina=%d",
		c.baseURL, dataInicial, dataFinal, modalidadeID, pagina, tamanhoPagina)

	var lastErr error

	for tentativa := 1; tentativa <= MaxRetries; tentativa++ {
		req, err := http.NewRequestWithContext(ctx, http.MethodGet, url, nil)
		if err != nil {
			return nil, fmt.Errorf("falha ao criar requisição PNCP: %w", err)
		}
		req.Header.Set("User-Agent", "RadarComprasPublicas/1.0 (+https://radarcompras.local)")
		req.Header.Set("Accept", "application/json")

		resp, err := c.httpClient.Do(req)
		if err != nil {
			lastErr = err
			slog.Warn("Falha na tentativa de chamada ao PNCP", "tentativa", tentativa, "url", url, "erro", err)
			time.Sleep(time.Duration(tentativa*BaseBackoffMillis) * time.Millisecond)
			continue
		}

		corpo, err := io.ReadAll(resp.Body)
		resp.Body.Close()

		if resp.StatusCode >= 500 || resp.StatusCode == 429 {
			lastErr = fmt.Errorf("resposta HTTP com erro transitório %d: %s", resp.StatusCode, string(corpo))
			slog.Warn("Status transitório recebido do PNCP", "status", resp.StatusCode, "tentativa", tentativa)
			time.Sleep(time.Duration(tentativa*BaseBackoffMillis) * time.Millisecond)
			continue
		}

		if resp.StatusCode != http.StatusOK {
			return nil, fmt.Errorf("PNCP retornou erro definitivo %d: %s", resp.StatusCode, string(corpo))
		}

		var envelope RespostaPNCP
		if err := json.Unmarshal(corpo, &envelope); err != nil {
			return nil, fmt.Errorf("falha ao desserializar JSON do PNCP: %w", err)
		}

		return &envelope, nil
	}

	return nil, fmt.Errorf("todas as %d tentativas falharam: %w", MaxRetries, lastErr)
}

// NormalizarConverte converte o item da resposta oficial do PNCP para o modelo canônico do domínio.
func NormalizarItem(item ItemContratacaoPNCP) domain.Oportunidade {
	agora := time.Now()
	pubTime := parseDataHora(item.DataPublicacaoPNCP, agora)

	var aberTime, encTime, atuTime *time.Time
	if item.DataAberturaProposta != nil && *item.DataAberturaProposta != "" {
		t := parseDataHora(*item.DataAberturaProposta, pubTime)
		aberTime = &t
	}
	if item.DataEncerramentoProposta != nil && *item.DataEncerramentoProposta != "" {
		t := parseDataHora(*item.DataEncerramentoProposta, pubTime)
		encTime = &t
	}
	if item.DataAtualizacao != nil && *item.DataAtualizacao != "" {
		t := parseDataHora(*item.DataAtualizacao, pubTime)
		atuTime = &t
	}

	// Normalização de campos nulos para apresentação clara
	infoComp := "não informado"
	if item.InformacaoComplementar != nil && strings.TrimSpace(*item.InformacaoComplementar) != "" {
		infoComp = strings.TrimSpace(*item.InformacaoComplementar)
	}

	modoDisputaNome := "não informado"
	if item.ModoDisputaNome != nil && strings.TrimSpace(*item.ModoDisputaNome) != "" {
		modoDisputaNome = strings.TrimSpace(*item.ModoDisputaNome)
	}
	modoDisputaID := 0
	if item.ModoDisputaID != nil {
		modoDisputaID = *item.ModoDisputaID
	}

	esfera := "não informado"
	if item.OrgaoEntidade.EsferaID != nil && strings.TrimSpace(*item.OrgaoEntidade.EsferaID) != "" {
		esfera = *item.OrgaoEntidade.EsferaID
	}

	poder := "não informado"
	if item.OrgaoEntidade.PoderID != nil && strings.TrimSpace(*item.OrgaoEntidade.PoderID) != "" {
		poder = *item.OrgaoEntidade.PoderID
	}

	unidadeNome := "não informado"
	if item.UnidadeOrgao.NomeUnidade != nil && strings.TrimSpace(*item.UnidadeOrgao.NomeUnidade) != "" {
		unidadeNome = *item.UnidadeOrgao.NomeUnidade
	}

	ufNome := "não informado"
	if item.UnidadeOrgao.UfNome != nil && strings.TrimSpace(*item.UnidadeOrgao.UfNome) != "" {
		ufNome = *item.UnidadeOrgao.UfNome
	}

	municipio := "não informado"
	if item.UnidadeOrgao.MunicipioNome != nil && strings.TrimSpace(*item.UnidadeOrgao.MunicipioNome) != "" {
		municipio = *item.UnidadeOrgao.MunicipioNome
	}

	ibge := "não informado"
	if item.UnidadeOrgao.CodigoIBGE != nil && strings.TrimSpace(*item.UnidadeOrgao.CodigoIBGE) != "" {
		ibge = *item.UnidadeOrgao.CodigoIBGE
	}

	linkOrigem := ""
	if item.LinkSistemaOrigem != nil {
		linkOrigem = *item.LinkSistemaOrigem
	}

	linkProc := ""
	if item.LinkProcessoEletronico != nil {
		linkProc = *item.LinkProcessoEletronico
	}

	// Link oficial de consulta no portal web do PNCP
	cnpjLimpo := strings.ReplaceAll(strings.ReplaceAll(strings.ReplaceAll(item.OrgaoEntidade.CNPJ, ".", ""), "-", ""), "/", "")
	linkPncp := fmt.Sprintf("https://pncp.gov.br/app/editais/%s/%d/%d", cnpjLimpo, item.AnoCompra, item.SequencialCompra)

	objeto := strings.TrimSpace(item.ObjetoCompra)
	if objeto == "" {
		objeto = "não informado"
	}

	return domain.Oportunidade{
		ID:                       uuid.New(),
		NumeroControlePNCP:       item.NumeroControlePNCP,
		AnoCompra:                item.AnoCompra,
		SequencialCompra:         item.SequencialCompra,
		NumeroCompra:             item.NumeroCompra,
		Processo:                 item.Processo,
		Objeto:                   objeto,
		InformacaoComplementar:   infoComp,
		ModalidadeID:             item.ModalidadeID,
		ModalidadeNome:           item.ModalidadeNome,
		ModoDisputaID:            modoDisputaID,
		ModoDisputaNome:          modoDisputaNome,
		SituacaoID:               item.SituacaoCompraID,
		SituacaoNome:             item.SituacaoCompraNome,
		OrgaoCNPJ:                item.OrgaoEntidade.CNPJ,
		OrgaoRazaoSocial:         item.OrgaoEntidade.RazaoSocial,
		OrgaoEsfera:              esfera,
		OrgaoPoder:               poder,
		UnidadeNome:              unidadeNome,
		UnidadeUFSigla:           strings.ToUpper(strings.TrimSpace(item.UnidadeOrgao.UfSigla)),
		UnidadeUFNome:            ufNome,
		UnidadeMunicipioNome:     municipio,
		UnidadeCodigoIBGE:        ibge,
		ValorTotalEstimado:       item.ValorTotalEstimado,
		ValorTotalHomologado:     item.ValorTotalHomologado,
		DataPublicacaoPNCP:       pubTime,
		DataAberturaProposta:     aberTime,
		DataEncerramentoProposta: encTime,
		DataAtualizacaoPNCP:      atuTime,
		LinkSistemaOrigem:        linkOrigem,
		LinkProcessoEletronico:   linkProc,
		LinkPNCP:                 linkPncp,
		DataColeta:               agora,
		IsDemonstrativo:          false,
	}
}

func parseDataHora(val string, fallback time.Time) time.Time {
	layouts := []string{
		"2006-01-02T15:04:05",
		"2006-01-02T15:04:05-07:00",
		"2006-01-02T15:04:05.000-07:00",
		"2006-01-02T15:04:05.000Z07:00",
		"2006-01-02",
	}
	for _, l := range layouts {
		if t, err := time.Parse(l, val); err == nil {
			return t
		}
	}
	return fallback
}
