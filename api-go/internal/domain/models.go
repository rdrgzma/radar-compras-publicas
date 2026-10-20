package domain

import (
	"time"

	"github.com/google/uuid"
)

// Oportunidade representa uma contratação pública canônica coletada do PNCP.
type Oportunidade struct {
	ID                       uuid.UUID  `json:"id"`
	NumeroControlePNCP       string     `json:"numero_controle_pncp"`
	AnoCompra                int        `json:"ano_compra"`
	SequencialCompra         int        `json:"sequencial_compra"`
	NumeroCompra             string     `json:"numero_compra"`
	Processo                 string     `json:"processo"`
	Objeto                   string     `json:"objeto"`
	InformacaoComplementar   string     `json:"informacao_complementar"`
	ModalidadeID             int        `json:"modalidade_id"`
	ModalidadeNome           string     `json:"modalidade_nome"`
	ModoDisputaID            int        `json:"modo_disputa_id"`
	ModoDisputaNome          string     `json:"modo_disputa_nome"`
	SituacaoID               int        `json:"situacao_id"`
	SituacaoNome             string     `json:"situacao_nome"`
	OrgaoCNPJ                string     `json:"orgao_cnpj"`
	OrgaoRazaoSocial         string     `json:"orgao_razao_social"`
	OrgaoEsfera              string     `json:"orgao_esfera"`
	OrgaoPoder               string     `json:"orgao_poder"`
	UnidadeNome              string     `json:"unidade_nome"`
	UnidadeUFSigla           string     `json:"unidade_uf_sigla"`
	UnidadeUFNome            string     `json:"unidade_uf_nome"`
	UnidadeMunicipioNome     string     `json:"unidade_municipio_nome"`
	UnidadeCodigoIBGE        string     `json:"unidade_codigo_ibge"`
	ValorTotalEstimado       *float64   `json:"valor_total_estimado"`
	ValorTotalHomologado     *float64   `json:"valor_total_homologado"`
	DataPublicacaoPNCP       time.Time  `json:"data_publicacao_pncp"`
	DataAberturaProposta     *time.Time `json:"data_abertura_proposta"`
	DataEncerramentoProposta *time.Time `json:"data_encerramento_proposta"`
	DataAtualizacaoPNCP      *time.Time `json:"data_atualizacao_pncp"`
	LinkSistemaOrigem        string     `json:"link_sistema_origem"`
	LinkProcessoEletronico   string     `json:"link_processo_eletronico"`
	LinkPNCP                 string     `json:"link_pncp"`
	DataColeta               time.Time  `json:"data_coleta"`
	IsDemonstrativo          bool       `json:"is_demonstrativo"`
	PrazoEncerrado           bool       `json:"prazo_encerrado"`
}

// VerificaPrazoEncerrado determina se a licitação já teve o prazo de envio encerrado frente a uma data de referência.
func (o *Oportunidade) VerificaPrazoEncerrado(agora time.Time) bool {
	if o.DataEncerramentoProposta != nil {
		return agora.After(*o.DataEncerramentoProposta)
	}
	return false
}

// Sincronizacao registra uma execução de coleta do conector.
type Sincronizacao struct {
	ID                  uuid.UUID  `json:"id"`
	Origem              string     `json:"origem"`
	Status              string     `json:"status"` // pendente, processando, sucesso, parcial, erro
	JanelaInicial       string     `json:"janela_inicial"`
	JanelaFinal         string     `json:"janela_final"`
	PaginasProcessadas  int        `json:"paginas_processadas"`
	TotalRegistrosFonte int        `json:"total_registros_fonte"`
	NovosRegistros      int        `json:"novos_registros"`
	RegistrosAtualizados int       `json:"registros_atualizados"`
	ErrosEncontrados    int        `json:"erros_encontrados"`
	MensagemErro        string     `json:"mensagem_erro,omitempty"`
	Modo                string     `json:"modo"` // real, demonstrativo
	IniciadoEm          time.Time  `json:"iniciado_em"`
	FinalizadoEm        *time.Time `json:"finalizado_em"`
}

// MatchRequest expressa os critérios de um perfil para matching.
type MatchRequest struct {
	TermosObrigatorios   []string `json:"termos_obrigatorios"`
	TermosExcluidos     []string `json:"termos_excluidos"`
	UFs                 []string `json:"ufs"`
	ApenasVigentes      bool     `json:"apenas_vigentes"`
	IncluirDemonstrativo bool     `json:"incluir_demonstrativos"`
	Pagina              int      `json:"pagina"`
	PorPagina           int      `json:"por_pagina"`
}

// OportunidadeMatch estende a oportunidade com o diagnóstico de correspondência.
type OportunidadeMatch struct {
	Oportunidade
	Correspondente        bool   `json:"correspondente"`
	MotivoCorrespondencia string `json:"motivo_correspondencia"`
	PontuacaoRelevancia   int    `json:"pontuacao_relevancia"`
}
