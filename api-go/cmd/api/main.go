package main

import (
	"context"
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/radar-compras/radar-compras-publicas/api-go/configs"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/fontes"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/service"
	"github.com/radar-compras/radar-compras-publicas/api-go/internal/storage"
	transportHttp "github.com/radar-compras/radar-compras-publicas/api-go/internal/transport/http"
)

func main() {
	cfg := configs.Load()

	slog.Info("Inicializando Radar de Compras Públicas (API Go)",
		"porta", cfg.Port,
		"env", cfg.AppEnv,
		"schema", cfg.DBSchema,
		"timezone", cfg.TimeZone,
	)

	store, err := storage.New(cfg)
	if err != nil {
		slog.Error("Falha crítica ao inicializar conexão com o banco de dados", "erro", err)
		os.Exit(1)
	}
	defer store.Close()

	pncpClient := fontes.NewPNCPClient("")
	syncService := service.NewSyncService(store, pncpClient)
	opService := service.NewOportunidadeService(store)

	handler := transportHttp.NewHandler(store, syncService, opService)
	router := transportHttp.NewRouter(handler, cfg.RadarInternalToken)

	srv := &http.Server{
		Addr:         ":" + cfg.Port,
		Handler:      router,
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 30 * time.Second,
		IdleTimeout:  60 * time.Second,
	}

	go func() {
		slog.Info(fmt.Sprintf("Servidor HTTP ouvindo na porta %s", cfg.Port))
		if err := srv.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
			slog.Error("Falha inesperada no servidor HTTP", "erro", err)
			os.Exit(1)
		}
	}()

	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	slog.Info("Sinal de desligamento recebido. Encerrando graciosamente...")

	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	if err := srv.Shutdown(ctx); err != nil {
		slog.Error("Falha ao encerrar servidor de forma graciosa", "erro", err)
	}

	slog.Info("Radar API Go encerrada com sucesso.")
}
