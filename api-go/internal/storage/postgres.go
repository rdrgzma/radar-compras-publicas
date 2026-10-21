package storage

import (
	"context"
	"database/sql"
	"fmt"
	"strings"
	"time"

	"github.com/google/uuid"
	_ "github.com/lib/pq"
	"github.com/radar-compras/radar-compras-publicas/api-go/configs"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
)

type Storage struct {
	db     *sql.DB
	schema string
}

func New(cfg *configs.Config) (*Storage, error) {
	connStr := fmt.Sprintf(
		"host=%s port=%s user=%s password=%s dbname=%s sslmode=%s search_path=%s",
		cfg.DBHost, cfg.DBPort, cfg.DBUser, cfg.DBPassword, cfg.DBName, cfg.DBSSLMode, cfg.DBSchema,
	)

	db, err := sql.Open("postgres", connStr)
	if err != nil {
		return nil, fmt.Errorf("falha ao abrir conexao com postgres: %w", err)
	}

	db.SetMaxOpenConns(25)
	db.SetMaxIdleConns(5)
	db.SetConnMaxLifetime(5 * time.Minute)

	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	if err := db.PingContext(ctx); err != nil {
		return nil, fmt.Errorf("falha ao verificar ping com postgres: %w", err)
	}

	s := &Storage{
		db:     db,
		schema: cfg.DBSchema,
	}

	if err := s.Migrate(ctx); err != nil {
		return nil, fmt.Errorf("falha ao rodar migracoes do schema %s: %w", cfg.DBSchema, err)
	}

	return s, nil
}

func (s *Storage) DB() *sql.DB {
	return s.db
}

func (s *Storage) Close() error {
	return s.db.Close()
}

func (s *Storage) Migrate(ctx context.Context) error {
	queries := []string{
		fmt.Sprintf("CREATE SCHEMA IF NOT EXISTS %s;", s.schema),
		fmt.Sprintf(`
		CREATE TABLE IF NOT EXISTS %s.oportunidades (
			id UUID PRIMARY KEY,
			numero_controle_pncp VARCHAR(80) UNIQUE NOT NULL,
			ano_compra INT NOT NULL,
			sequencial_compra INT NOT NULL,
			numero_compra VARCHAR(50),
			processo VARCHAR(100),
			objeto TEXT NOT NULL,
			informacao_complementar TEXT,
			modalidade_id INT NOT NULL,
			modalidade_nome VARCHAR(100) NOT NULL,
			modo_disputa_id INT,
			modo_disputa_nome VARCHAR(100),
			situacao_id INT NOT NULL,
			situacao_nome VARCHAR(100) NOT NULL,
			orgao_cnpj VARCHAR(20) NOT NULL,
			orgao_razao_social VARCHAR(255) NOT NULL,
			orgao_esfera VARCHAR(10),
			orgao_poder VARCHAR(10),
			unidade_nome VARCHAR(255),
			unidade_uf_sigla VARCHAR(5) NOT NULL,
			unidade_uf_nome VARCHAR(100),
			unidade_municipio_nome VARCHAR(150),
			unidade_codigo_ibge VARCHAR(20),
			valor_total_estimado NUMERIC(15,2),
			valor_total_homologado NUMERIC(15,2),
			data_publicacao_pncp TIMESTAMPTZ NOT NULL,
			data_abertura_proposta TIMESTAMPTZ,
			data_encerramento_proposta TIMESTAMPTZ,
			data_atualizacao_pncp TIMESTAMPTZ,
			link_sistema_origem TEXT,
			link_processo_eletronico TEXT,
			link_pncp TEXT NOT NULL,
			data_coleta TIMESTAMPTZ NOT NULL DEFAULT NOW(),
			is_demonstrativo BOOLEAN NOT NULL DEFAULT FALSE,
			created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
			updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
		);
		`, s.schema),
		fmt.Sprintf(`
		CREATE INDEX IF NOT EXISTS idx_oportunidades_uf ON %s.oportunidades (unidade_uf_sigla);
		CREATE INDEX IF NOT EXISTS idx_oportunidades_data_pub ON %s.oportunidades (data_publicacao_pncp DESC);
		CREATE INDEX IF NOT EXISTS idx_oportunidades_data_enc ON %s.oportunidades (data_encerramento_proposta);
		CREATE INDEX IF NOT EXISTS idx_oportunidades_demo ON %s.oportunidades (is_demonstrativo);
		`, s.schema, s.schema, s.schema, s.schema),
		fmt.Sprintf(`
		CREATE TABLE IF NOT EXISTS %s.sincronizacoes (
			id UUID PRIMARY KEY,
			origem VARCHAR(50) NOT NULL DEFAULT 'PNCP',
			status VARCHAR(30) NOT NULL DEFAULT 'pendente',
			janela_inicial DATE NOT NULL,
			janela_final DATE NOT NULL,
			paginas_processadas INT NOT NULL DEFAULT 0,
			total_registros_fonte INT NOT NULL DEFAULT 0,
			novos_registros INT NOT NULL DEFAULT 0,
			registros_atualizados INT NOT NULL DEFAULT 0,
			erros_encontrados INT NOT NULL DEFAULT 0,
			mensagem_erro TEXT,
			modo VARCHAR(30) NOT NULL DEFAULT 'real',
			iniciado_em TIMESTAMPTZ NOT NULL DEFAULT NOW(),
			finalizado_em TIMESTAMPTZ
		);
		`, s.schema),
		fmt.Sprintf(`
		CREATE INDEX IF NOT EXISTS idx_sincronizacoes_status ON %s.sincronizacoes (status);
		CREATE INDEX IF NOT EXISTS idx_sincronizacoes_iniciado ON %s.sincronizacoes (iniciado_em DESC);
		`, s.schema, s.schema),
	}

	for _, q := range queries {
		if _, err := s.db.ExecContext(ctx, q); err != nil {
			return err
		}
	}

	return nil
}

// UpsertOportunidade insere ou atualiza uma oportunidade baseando-se no numero_controle_pncp.
// Retorna isNew=true se foi inserção, ou false se foi atualização.
func (s *Storage) UpsertOportunidade(ctx context.Context, o *domain.Oportunidade) (bool, error) {
	if o.ID == uuid.Nil {
		o.ID = uuid.New()
	}

	query := fmt.Sprintf(`
		INSERT INTO %s.oportunidades (
			id, numero_controle_pncp, ano_compra, sequencial_compra, numero_compra, processo,
			objeto, informacao_complementar, modalidade_id, modalidade_nome, modo_disputa_id,
			modo_disputa_nome, situacao_id, situacao_nome, orgao_cnpj, orgao_razao_social,
			orgao_esfera, orgao_poder, unidade_nome, unidade_uf_sigla, unidade_uf_nome,
			unidade_municipio_nome, unidade_codigo_ibge, valor_total_estimado, valor_total_homologado,
			data_publicacao_pncp, data_abertura_proposta, data_encerramento_proposta,
			data_atualizacao_pncp, link_sistema_origem, link_processo_eletronico, link_pncp,
			data_coleta, is_demonstrativo, updated_at
		) VALUES (
			$1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16,
			$17, $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30, $31, $32,
			$33, $34, NOW()
		)
		ON CONFLICT (numero_controle_pncp) DO UPDATE SET
			objeto = EXCLUDED.objeto,
			informacao_complementar = EXCLUDED.informacao_complementar,
			situacao_id = EXCLUDED.situacao_id,
			situacao_nome = EXCLUDED.situacao_nome,
			valor_total_estimado = EXCLUDED.valor_total_estimado,
			valor_total_homologado = EXCLUDED.valor_total_homologado,
			data_abertura_proposta = EXCLUDED.data_abertura_proposta,
			data_encerramento_proposta = EXCLUDED.data_encerramento_proposta,
			data_atualizacao_pncp = EXCLUDED.data_atualizacao_pncp,
			link_sistema_origem = EXCLUDED.link_sistema_origem,
			link_processo_eletronico = EXCLUDED.link_processo_eletronico,
			link_pncp = EXCLUDED.link_pncp,
			data_coleta = EXCLUDED.data_coleta,
			updated_at = NOW()
		RETURNING (xmax = 0);
	`, s.schema)

	var isNew bool
	err := s.db.QueryRowContext(ctx, query,
		o.ID, o.NumeroControlePNCP, o.AnoCompra, o.SequencialCompra, o.NumeroCompra, o.Processo,
		o.Objeto, o.InformacaoComplementar, o.ModalidadeID, o.ModalidadeNome, o.ModoDisputaID,
		o.ModoDisputaNome, o.SituacaoID, o.SituacaoNome, o.OrgaoCNPJ, o.OrgaoRazaoSocial,
		o.OrgaoEsfera, o.OrgaoPoder, o.UnidadeNome, o.UnidadeUFSigla, o.UnidadeUFNome,
		o.UnidadeMunicipioNome, o.UnidadeCodigoIBGE, o.ValorTotalEstimado, o.ValorTotalHomologado,
		o.DataPublicacaoPNCP, o.DataAberturaProposta, o.DataEncerramentoProposta,
		o.DataAtualizacaoPNCP, o.LinkSistemaOrigem, o.LinkProcessoEletronico, o.LinkPNCP,
		o.DataColeta, o.IsDemonstrativo,
	).Scan(&isNew)

	if err != nil {
		return false, fmt.Errorf("falha no upsert da oportunidade %s: %w", o.NumeroControlePNCP, err)
	}

	return isNew, nil
}

// GetOportunidadeByNumeroControle busca oportunidade pelo identificador oficial.
func (s *Storage) GetOportunidadeByNumeroControle(ctx context.Context, numeroControle string) (*domain.Oportunidade, error) {
	query := fmt.Sprintf(`
		SELECT id, numero_controle_pncp, ano_compra, sequencial_compra, numero_compra, processo,
			objeto, informacao_complementar, modalidade_id, modalidade_nome, modo_disputa_id,
			modo_disputa_nome, situacao_id, situacao_nome, orgao_cnpj, orgao_razao_social,
			orgao_esfera, orgao_poder, unidade_nome, unidade_uf_sigla, unidade_uf_nome,
			unidade_municipio_nome, unidade_codigo_ibge, valor_total_estimado, valor_total_homologado,
			data_publicacao_pncp, data_abertura_proposta, data_encerramento_proposta,
			data_atualizacao_pncp, link_sistema_origem, link_processo_eletronico, link_pncp,
			data_coleta, is_demonstrativo
		FROM %s.oportunidades
		WHERE numero_controle_pncp = $1
	`, s.schema)

	var o domain.Oportunidade
	var numCompra, proc, infoComp, modoNome, esfera, poder, unidNome, ufNome, munNome, codIbge sql.NullString
	var linkOrigem, linkProc sql.NullString
	var modoId sql.NullInt32
	var valEst, valHom sql.NullFloat64
	var dtAber, dtEnc, dtAtu sql.NullTime

	err := s.db.QueryRowContext(ctx, query, numeroControle).Scan(
		&o.ID, &o.NumeroControlePNCP, &o.AnoCompra, &o.SequencialCompra, &numCompra, &proc,
		&o.Objeto, &infoComp, &o.ModalidadeID, &o.ModalidadeNome, &modoId,
		&modoNome, &o.SituacaoID, &o.SituacaoNome, &o.OrgaoCNPJ, &o.OrgaoRazaoSocial,
		&esfera, &poder, &unidNome, &o.UnidadeUFSigla, &ufNome,
		&munNome, &codIbge, &valEst, &valHom,
		&o.DataPublicacaoPNCP, &dtAber, &dtEnc,
		&dtAtu, &linkOrigem, &linkProc, &o.LinkPNCP,
		&o.DataColeta, &o.IsDemonstrativo,
	)

	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}

	o.NumeroCompra = numCompra.String
	o.Processo = proc.String
	o.InformacaoComplementar = infoComp.String
	o.ModoDisputaNome = modoNome.String
	o.OrgaoEsfera = esfera.String
	o.OrgaoPoder = poder.String
	o.UnidadeNome = unidNome.String
	o.UnidadeUFNome = ufNome.String
	o.UnidadeMunicipioNome = munNome.String
	o.UnidadeCodigoIBGE = codIbge.String
	o.LinkSistemaOrigem = linkOrigem.String
	o.LinkProcessoEletronico = linkProc.String

	if modoId.Valid {
		o.ModoDisputaID = int(modoId.Int32)
	}
	if valEst.Valid {
		v := valEst.Float64
		o.ValorTotalEstimado = &v
	}
	if valHom.Valid {
		v := valHom.Float64
		o.ValorTotalHomologado = &v
	}
	if dtAber.Valid {
		o.DataAberturaProposta = &dtAber.Time
	}
	if dtEnc.Valid {
		o.DataEncerramentoProposta = &dtEnc.Time
	}
	if dtAtu.Valid {
		o.DataAtualizacaoPNCP = &dtAtu.Time
	}

	o.PrazoEncerrado = o.VerificaPrazoEncerrado(time.Now())
	return &o, nil
}

// ListOportunidadesParams agrupa os parâmetros de consulta e paginação.
type ListOportunidadesParams struct {
	Pagina               int
	PorPagina            int
	Busca                string
	UF                   string
	UFs                  []string
	TermosQualquer       []string
	ModalidadeID         int
	ApenasVigentes       bool
	IncluirDemonstrativo bool
}

// ListOportunidades consulta oportunidades no schema api_go.
func (s *Storage) ListOportunidades(ctx context.Context, p ListOportunidadesParams) ([]domain.Oportunidade, int, error) {
	if p.Pagina < 1 {
		p.Pagina = 1
	}
	if p.PorPagina < 1 {
		p.PorPagina = 15
	}
	if p.PorPagina > 5000 {
		p.PorPagina = 5000
	}

	var where []string
	var args []interface{}
	argIdx := 1

	if !p.IncluirDemonstrativo {
		where = append(where, fmt.Sprintf("is_demonstrativo = $%d", argIdx))
		args = append(args, false)
		argIdx++
	}

	if p.ApenasVigentes {
		where = append(where, fmt.Sprintf("(data_encerramento_proposta IS NULL OR data_encerramento_proposta >= $%d)", argIdx))
		args = append(args, time.Now())
		argIdx++
	}

	if p.UF != "" {
		where = append(where, fmt.Sprintf("UPPER(unidade_uf_sigla) = $%d", argIdx))
		args = append(args, strings.ToUpper(p.UF))
		argIdx++
	} else if len(p.UFs) > 0 {
		var placeholders []string
		for _, uf := range p.UFs {
			ufTrim := strings.ToUpper(strings.TrimSpace(uf))
			if ufTrim != "" {
				placeholders = append(placeholders, fmt.Sprintf("$%d", argIdx))
				args = append(args, ufTrim)
				argIdx++
			}
		}
		if len(placeholders) > 0 {
			where = append(where, fmt.Sprintf("UPPER(unidade_uf_sigla) IN (%s)", strings.Join(placeholders, ", ")))
		}
	}

	if len(p.TermosQualquer) > 0 {
		var termOrs []string
		for _, t := range p.TermosQualquer {
			tLimpo := strings.TrimSpace(t)
			if tLimpo != "" {
				termOrs = append(termOrs, fmt.Sprintf("(objeto ILIKE $%d OR informacao_complementar ILIKE $%d OR orgao_razao_social ILIKE $%d)", argIdx, argIdx, argIdx))
				args = append(args, "%"+tLimpo+"%")
				argIdx++
			}
		}
		if len(termOrs) > 0 {
			where = append(where, "("+strings.Join(termOrs, " OR ")+")")
		}
	}

	if p.ModalidadeID > 0 {
		where = append(where, fmt.Sprintf("modalidade_id = $%d", argIdx))
		args = append(args, p.ModalidadeID)
		argIdx++
	}

	if p.Busca != "" {
		where = append(where, fmt.Sprintf("(objeto ILIKE $%d OR orgao_razao_social ILIKE $%d OR numero_controle_pncp ILIKE $%d)", argIdx, argIdx, argIdx))
		args = append(args, "%"+p.Busca+"%")
		argIdx++
	}

	whereClause := ""
	if len(where) > 0 {
		whereClause = "WHERE " + strings.Join(where, " AND ")
	}

	countQuery := fmt.Sprintf("SELECT COUNT(*) FROM %s.oportunidades %s", s.schema, whereClause)
	var total int
	if err := s.db.QueryRowContext(ctx, countQuery, args...).Scan(&total); err != nil {
		return nil, 0, err
	}

	offset := (p.Pagina - 1) * p.PorPagina
	query := fmt.Sprintf(`
		SELECT id, numero_controle_pncp, ano_compra, sequencial_compra, numero_compra, processo,
			objeto, informacao_complementar, modalidade_id, modalidade_nome, modo_disputa_id,
			modo_disputa_nome, situacao_id, situacao_nome, orgao_cnpj, orgao_razao_social,
			orgao_esfera, orgao_poder, unidade_nome, unidade_uf_sigla, unidade_uf_nome,
			unidade_municipio_nome, unidade_codigo_ibge, valor_total_estimado, valor_total_homologado,
			data_publicacao_pncp, data_abertura_proposta, data_encerramento_proposta,
			data_atualizacao_pncp, link_sistema_origem, link_processo_eletronico, link_pncp,
			data_coleta, is_demonstrativo
		FROM %s.oportunidades
		%s
		ORDER BY data_publicacao_pncp DESC
		LIMIT $%d OFFSET $%d
	`, s.schema, whereClause, argIdx, argIdx+1)

	args = append(args, p.PorPagina, offset)

	rows, err := s.db.QueryContext(ctx, query, args...)
	if err != nil {
		return nil, 0, err
	}
	defer rows.Close()

	var lista []domain.Oportunidade
	agora := time.Now()

	for rows.Next() {
		var o domain.Oportunidade
		var numCompra, proc, infoComp, modoNome, esfera, poder, unidNome, ufNome, munNome, codIbge sql.NullString
		var linkOrigem, linkProc sql.NullString
		var modoId sql.NullInt32
		var valEst, valHom sql.NullFloat64
		var dtAber, dtEnc, dtAtu sql.NullTime

		if err := rows.Scan(
			&o.ID, &o.NumeroControlePNCP, &o.AnoCompra, &o.SequencialCompra, &numCompra, &proc,
			&o.Objeto, &infoComp, &o.ModalidadeID, &o.ModalidadeNome, &modoId,
			&modoNome, &o.SituacaoID, &o.SituacaoNome, &o.OrgaoCNPJ, &o.OrgaoRazaoSocial,
			&esfera, &poder, &unidNome, &o.UnidadeUFSigla, &ufNome,
			&munNome, &codIbge, &valEst, &valHom,
			&o.DataPublicacaoPNCP, &dtAber, &dtEnc,
			&dtAtu, &linkOrigem, &linkProc, &o.LinkPNCP,
			&o.DataColeta, &o.IsDemonstrativo,
		); err != nil {
			return nil, 0, err
		}

		o.NumeroCompra = numCompra.String
		o.Processo = proc.String
		o.InformacaoComplementar = infoComp.String
		o.ModoDisputaNome = modoNome.String
		o.OrgaoEsfera = esfera.String
		o.OrgaoPoder = poder.String
		o.UnidadeNome = unidNome.String
		o.UnidadeUFNome = ufNome.String
		o.UnidadeMunicipioNome = munNome.String
		o.UnidadeCodigoIBGE = codIbge.String
		o.LinkSistemaOrigem = linkOrigem.String
		o.LinkProcessoEletronico = linkProc.String

		if modoId.Valid {
			o.ModoDisputaID = int(modoId.Int32)
		}
		if valEst.Valid {
			v := valEst.Float64
			o.ValorTotalEstimado = &v
		}
		if valHom.Valid {
			v := valHom.Float64
			o.ValorTotalHomologado = &v
		}
		if dtAber.Valid {
			o.DataAberturaProposta = &dtAber.Time
		}
		if dtEnc.Valid {
			o.DataEncerramentoProposta = &dtEnc.Time
		}
		if dtAtu.Valid {
			o.DataAtualizacaoPNCP = &dtAtu.Time
		}

		o.PrazoEncerrado = o.VerificaPrazoEncerrado(agora)
		lista = append(lista, o)
	}

	return lista, total, nil
}

// Sincronizações: gerenciamento de status e jobs assíncronos.

func (s *Storage) CreateSincronizacao(ctx context.Context, sync *domain.Sincronizacao) error {
	if sync.ID == uuid.Nil {
		sync.ID = uuid.New()
	}
	if sync.IniciadoEm.IsZero() {
		sync.IniciadoEm = time.Now()
	}

	query := fmt.Sprintf(`
		INSERT INTO %s.sincronizacoes (
			id, origem, status, janela_inicial, janela_final,
			paginas_processadas, total_registros_fonte, novos_registros,
			registros_atualizados, erros_encontrados, mensagem_erro, modo,
			iniciado_em
		) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)
	`, s.schema)

	_, err := s.db.ExecContext(ctx, query,
		sync.ID, sync.Origem, sync.Status, sync.JanelaInicial, sync.JanelaFinal,
		sync.PaginasProcessadas, sync.TotalRegistrosFonte, sync.NovosRegistros,
		sync.RegistrosAtualizados, sync.ErrosEncontrados, sync.MensagemErro, sync.Modo,
		sync.IniciadoEm,
	)
	return err
}

func (s *Storage) UpdateSincronizacao(ctx context.Context, sync *domain.Sincronizacao) error {
	query := fmt.Sprintf(`
		UPDATE %s.sincronizacoes SET
			status = $1,
			paginas_processadas = $2,
			total_registros_fonte = $3,
			novos_registros = $4,
			registros_atualizados = $5,
			erros_encontrados = $6,
			mensagem_erro = $7,
			finalizado_em = $8
		WHERE id = $9
	`, s.schema)

	_, err := s.db.ExecContext(ctx, query,
		sync.Status, sync.PaginasProcessadas, sync.TotalRegistrosFonte,
		sync.NovosRegistros, sync.RegistrosAtualizados, sync.ErrosEncontrados,
		sync.MensagemErro, sync.FinalizadoEm, sync.ID,
	)
	return err
}

func (s *Storage) GetSincronizacao(ctx context.Context, id uuid.UUID) (*domain.Sincronizacao, error) {
	query := fmt.Sprintf(`
		SELECT id, origem, status, janela_inicial, janela_final, paginas_processadas,
			total_registros_fonte, novos_registros, registros_atualizados, erros_encontrados,
			mensagem_erro, modo, iniciado_em, finalizado_em
		FROM %s.sincronizacoes
		WHERE id = $1
	`, s.schema)

	var sync domain.Sincronizacao
	var msgErro sql.NullString
	var finEm sql.NullTime

	err := s.db.QueryRowContext(ctx, query, id).Scan(
		&sync.ID, &sync.Origem, &sync.Status, &sync.JanelaInicial, &sync.JanelaFinal,
		&sync.PaginasProcessadas, &sync.TotalRegistrosFonte, &sync.NovosRegistros,
		&sync.RegistrosAtualizados, &sync.ErrosEncontrados, &msgErro, &sync.Modo,
		&sync.IniciadoEm, &finEm,
	)

	if err == sql.ErrNoRows {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}

	sync.MensagemErro = msgErro.String
	if finEm.Valid {
		sync.FinalizadoEm = &finEm.Time
	}
	return &sync, nil
}

func (s *Storage) ListSincronizacoes(ctx context.Context, pagina, porPagina int, statusFiltro string) ([]domain.Sincronizacao, int, error) {
	if pagina < 1 {
		pagina = 1
	}
	if porPagina < 1 || porPagina > 100 {
		porPagina = 15
	}

	where := ""
	var args []interface{}
	if statusFiltro != "" {
		where = "WHERE status = $1"
		args = append(args, statusFiltro)
	}

	countQuery := fmt.Sprintf("SELECT COUNT(*) FROM %s.sincronizacoes %s", s.schema, where)
	var total int
	if err := s.db.QueryRowContext(ctx, countQuery, args...).Scan(&total); err != nil {
		return nil, 0, err
	}

	offset := (pagina - 1) * porPagina
	limitIdx := len(args) + 1
	offsetIdx := len(args) + 2

	query := fmt.Sprintf(`
		SELECT id, origem, status, janela_inicial, janela_final, paginas_processadas,
			total_registros_fonte, novos_registros, registros_atualizados, erros_encontrados,
			mensagem_erro, modo, iniciado_em, finalizado_em
		FROM %s.sincronizacoes
		%s
		ORDER BY iniciado_em DESC
		LIMIT $%d OFFSET $%d
	`, s.schema, where, limitIdx, offsetIdx)

	args = append(args, porPagina, offset)

	rows, err := s.db.QueryContext(ctx, query, args...)
	if err != nil {
		return nil, 0, err
	}
	defer rows.Close()

	var lista []domain.Sincronizacao
	for rows.Next() {
		var sync domain.Sincronizacao
		var msgErro sql.NullString
		var finEm sql.NullTime

		if err := rows.Scan(
			&sync.ID, &sync.Origem, &sync.Status, &sync.JanelaInicial, &sync.JanelaFinal,
			&sync.PaginasProcessadas, &sync.TotalRegistrosFonte, &sync.NovosRegistros,
			&sync.RegistrosAtualizados, &sync.ErrosEncontrados, &msgErro, &sync.Modo,
			&sync.IniciadoEm, &finEm,
		); err != nil {
			return nil, 0, err
		}

		sync.MensagemErro = msgErro.String
		if finEm.Valid {
			sync.FinalizadoEm = &finEm.Time
		}
		lista = append(lista, sync)
	}

	return lista, total, nil
}

// HasActiveSync verifica se já existe uma sincronização em execução no momento.
func (s *Storage) HasActiveSync(ctx context.Context) (bool, error) {
	query := fmt.Sprintf(`
		SELECT EXISTS(
			SELECT 1 FROM %s.sincronizacoes
			WHERE status IN ('pendente', 'processando')
			AND iniciado_em > NOW() - INTERVAL '30 minutes'
		)
	`, s.schema)

	var existe bool
	err := s.db.QueryRowContext(ctx, query).Scan(&existe)
	return existe, err
}
