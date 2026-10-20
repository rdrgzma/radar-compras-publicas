package service

import (
	"context"
	"fmt"
	"log/slog"
	"sync"
	"time"

	"github.com/google/uuid"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/domain"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/fontes"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/storage"
)

type SyncService struct {
	store      *storage.Storage
	pncpClient *fontes.PNCPClient
	mu         sync.Mutex
	isExecuting bool
}

func NewSyncService(store *storage.Storage, pncpClient *fontes.PNCPClient) *SyncService {
	return &SyncService{
		store:      store,
		pncpClient: pncpClient,
	}
}

type IniciarSyncParams struct {
	DataInicial string // YYYY-MM-DD
	DataFinal   string // YYYY-MM-DD
	Modalidades []int  // Ex: [6, 8]
	Modo        string // real ou demonstrativo
}

// IniciarSincronizacao agenda e inicia assincronamente o processo de coleta.
func (s *SyncService) IniciarSincronizacao(ctx context.Context, p IniciarSyncParams) (*domain.Sincronizacao, error) {
	s.mu.Lock()
	if s.isExecuting {
		s.mu.Unlock()
		return nil, fmt.Errorf("já existe uma sincronização em andamento")
	}

	ativo, err := s.store.HasActiveSync(ctx)
	if err != nil {
		s.mu.Unlock()
		return nil, fmt.Errorf("falha ao verificar lock no banco: %w", err)
	}
	if ativo {
		s.mu.Unlock()
		return nil, fmt.Errorf("já existe uma sincronização ativa registrada recentemente")
	}

	s.isExecuting = true
	s.mu.Unlock()

	if len(p.Modalidades) == 0 {
		p.Modalidades = []int{6, 8} // 6 = Pregão Eletrônico, 8 = Dispensa
	}
	if p.Modo == "" {
		p.Modo = "real"
	}

	syncJob := &domain.Sincronizacao{
		ID:            uuid.New(),
		Origem:        "PNCP",
		Status:        "processando",
		JanelaInicial: p.DataInicial,
		JanelaFinal:   p.DataFinal,
		Modo:          p.Modo,
		IniciadoEm:    time.Now(),
	}

	if err := s.store.CreateSincronizacao(ctx, syncJob); err != nil {
		s.mu.Lock()
		s.isExecuting = false
		s.mu.Unlock()
		return nil, fmt.Errorf("falha ao criar registro de sincronização: %w", err)
	}

	// Execução assíncrona em background
	go s.executarColeta(syncJob.ID, p)

	return syncJob, nil
}

func (s *SyncService) executarColeta(jobID uuid.UUID, p IniciarSyncParams) {
	defer func() {
		s.mu.Lock()
		s.isExecuting = false
		s.mu.Unlock()
	}()

	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Minute)
	defer cancel()

	slog.Info("Iniciando execução em background da sincronização", "job_id", jobID, "modo", p.Modo)

	syncRecord, err := s.store.GetSincronizacao(ctx, jobID)
	if err != nil || syncRecord == nil {
		slog.Error("Registro da sincronização não encontrado", "job_id", jobID)
		return
	}

	if p.Modo == "demonstrativo" {
		novos, err := s.store.SeedDemonstrativos(ctx)
		agora := time.Now()
		syncRecord.FinalizadoEm = &agora
		if err != nil {
			syncRecord.Status = "erro"
			syncRecord.MensagemErro = err.Error()
			syncRecord.ErrosEncontrados = 1
		} else {
			syncRecord.Status = "sucesso"
			syncRecord.TotalRegistrosFonte = novos
			syncRecord.NovosRegistros = novos
			syncRecord.PaginasProcessadas = 1
		}
		_ = s.store.UpdateSincronizacao(ctx, syncRecord)
		return
	}

	// Modo Real: coleta nas modalidades solicitadas do PNCP
	// Formato de data para a URL do PNCP: YYYYMMDD
	dtIni := formatarDataPNCP(p.DataInicial)
	dtFim := formatarDataPNCP(p.DataFinal)

	totalFonte := 0
	novosTotal := 0
	atualizadosTotal := 0
	paginasProcessadas := 0
	errosEncontrados := 0
	var mensagensErros []string

	for _, modID := range p.Modalidades {
		pagina := 1
		for {
			envelope, err := s.pncpClient.FetchPagina(ctx, dtIni, dtFim, modID, pagina, 20)
			if err != nil {
				slog.Error("Erro ao buscar página no PNCP", "modalidade", modID, "pagina", pagina, "erro", err)
				errosEncontrados++
				mensagensErros = append(mensagensErros, fmt.Sprintf("Mod %d Pag %d: %s", modID, pagina, err.Error()))
				break // Passa para a próxima modalidade preservando os dados já coletados
			}

			paginasProcessadas++
			totalFonte += len(envelope.Data)

			for _, item := range envelope.Data {
				op := fontes.NormalizarItem(item)
				isNovo, err := s.store.UpsertOportunidade(ctx, &op)
				if err != nil {
					slog.Warn("Falha ao salvar oportunidade do PNCP", "num_controle", op.NumeroControlePNCP, "erro", err)
					errosEncontrados++
				} else {
					if isNovo {
						novosTotal++
					} else {
						atualizadosTotal++
					}
				}
			}

			// Atualiza progresso intermediário
			syncRecord.PaginasProcessadas = paginasProcessadas
			syncRecord.TotalRegistrosFonte = totalFonte
			syncRecord.NovosRegistros = novosTotal
			syncRecord.RegistrosAtualizados = atualizadosTotal
			syncRecord.ErrosEncontrados = errosEncontrados
			_ = s.store.UpdateSincronizacao(ctx, syncRecord)

			if envelope.Empty || envelope.PaginasRestantes <= 0 || pagina >= envelope.TotalPaginas {
				break
			}

			// Limite prudente por execução para evitar estourar quota do PNCP em janelas grandes
			if pagina >= 50 {
				slog.Info("Atingido limite de 50 páginas para a modalidade", "modalidade", modID)
				break
			}

			pagina++
			time.Sleep(1000 * time.Millisecond) // Respeito à taxa de requisições da fonte oficial (evita HTTP 429)
		}
	}

	agora := time.Now()
	syncRecord.FinalizadoEm = &agora
	syncRecord.PaginasProcessadas = paginasProcessadas
	syncRecord.TotalRegistrosFonte = totalFonte
	syncRecord.NovosRegistros = novosTotal
	syncRecord.RegistrosAtualizados = atualizadosTotal
	syncRecord.ErrosEncontrados = errosEncontrados

	if errosEncontrados > 0 && totalFonte > 0 {
		syncRecord.Status = "parcial"
		syncRecord.MensagemErro = fmt.Sprintf("Concluído com %d falhas parciais. Erros: %v", errosEncontrados, mensagensErros)
	} else if errosEncontrados > 0 && totalFonte == 0 {
		syncRecord.Status = "erro"
		syncRecord.MensagemErro = fmt.Sprintf("Falha total na coleta. Erros: %v", mensagensErros)
	} else {
		syncRecord.Status = "sucesso"
	}

	_ = s.store.UpdateSincronizacao(ctx, syncRecord)
	slog.Info("Sincronização concluída", "job_id", jobID, "status", syncRecord.Status, "total", totalFonte)
}

func formatarDataPNCP(dataISO string) string {
	// Se já estiver em YYYYMMDD
	limpo := ""
	for _, c := range dataISO {
		if c >= '0' && c <= '9' {
			limpo += string(c)
		}
	}
	if len(limpo) == 8 {
		return limpo
	}
	// Fallback para hoje
	return time.Now().Format("20060102")
}
