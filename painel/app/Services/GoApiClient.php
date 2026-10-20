<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoApiClient
{
    protected string $baseUrl;
    protected string $internalToken;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.radar_go.base_url', 'http://127.0.0.1:8000'), '/');
        $this->internalToken = (string) config('services.radar_go.internal_token', 'radar-token-interno-ultra-seguro-compartilhado-com-painel');
        $this->timeout = (int) config('services.radar_go.timeout', 15);
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'X-Internal-Token' => $this->internalToken,
                'Accept'           => 'application/json',
            ]);
    }

    public function checkSaude(): array
    {
        try {
            $resp = Http::baseUrl($this->baseUrl)
                ->timeout(3)
                ->get('/saude');

            if ($resp->successful()) {
                return $resp->json();
            }

            return [
                'status'         => 'erro',
                'mensagem'       => 'API Go respondeu com status ' . $resp->status(),
                'banco_de_dados' => 'desconhecido',
            ];
        } catch (Exception $e) {
            Log::warning('Falha ao checar saúde da API Go: ' . $e->getMessage());
            return [
                'status'         => 'indisponivel',
                'mensagem'       => 'API Go inacessível: ' . $e->getMessage(),
                'banco_de_dados' => 'indisponivel',
            ];
        }
    }

    public function iniciarSincronizacao(string $dataInicio, string $dataFim, array $modalidades = [6, 8], string $modo = 'real'): array
    {
        try {
            $resp = $this->client()->post('/api/v1/sync', [
                'data_inicial' => $dataInicio,
                'data_final'   => $dataFim,
                'modalidades'  => $modalidades,
                'modo'         => $modo,
            ]);

            if ($resp->status() === 409) {
                return [
                    'sucesso'  => false,
                    'conflito' => true,
                    'mensagem' => 'Já existe uma sincronização do PNCP em execução no momento.',
                ];
            }

            if ($resp->successful()) {
                $dados = $resp->json();
                return [
                    'sucesso'  => true,
                    'job_id'   => $dados['id'] ?? null,
                    'status'   => $dados['status'] ?? 'processando',
                    'mensagem' => $dados['mensagem'] ?? 'Sincronização iniciada com sucesso.',
                ];
            }

            return [
                'sucesso'  => false,
                'mensagem' => 'Erro da API Go: ' . ($resp->json('mensagem') ?? $resp->body()),
            ];
        } catch (Exception $e) {
            Log::error('Erro ao disparar sincronização Go: ' . $e->getMessage());
            return [
                'sucesso'  => false,
                'mensagem' => 'Falha de comunicação com a API de coleta: ' . $e->getMessage(),
            ];
        }
    }

    public function consultarSincronizacao(string $id): ?array
    {
        try {
            $resp = $this->client()->get("/api/v1/sync/{$id}");
            return $resp->successful() ? $resp->json() : null;
        } catch (Exception $e) {
            Log::error("Erro ao consultar job {$id}: " . $e->getMessage());
            return null;
        }
    }

    public function listarSincronizacoes(int $pagina = 1, int $porPagina = 15, ?string $status = null): array
    {
        try {
            $params = [
                'pagina'     => $pagina,
                'por_pagina' => $porPagina,
            ];
            if ($status) {
                $params['status'] = $status;
            }

            $resp = $this->client()->get('/api/v1/sync', $params);
            return $resp->successful() ? $resp->json() : ['dados' => [], 'total' => 0];
        } catch (Exception $e) {
            Log::error('Erro ao listar sincronizações: ' . $e->getMessage());
            return ['dados' => [], 'total' => 0, 'erro' => $e->getMessage()];
        }
    }

    public function listarOportunidades(array $filtros = []): array
    {
        try {
            $resp = $this->client()->get('/api/v1/oportunidades', $filtros);
            return $resp->successful() ? $resp->json() : ['dados' => [], 'total' => 0];
        } catch (Exception $e) {
            Log::error('Erro ao listar oportunidades na API Go: ' . $e->getMessage());
            return ['dados' => [], 'total' => 0, 'erro' => $e->getMessage()];
        }
    }

    public function obterOportunidade(string $numeroControle): ?array
    {
        try {
            $resp = $this->client()->get("/api/v1/oportunidades/{$numeroControle}");
            return $resp->successful() ? $resp->json() : null;
        } catch (Exception $e) {
            Log::error("Erro ao obter oportunidade {$numeroControle}: " . $e->getMessage());
            return null;
        }
    }

    public function avaliarMatch(array $criterios): array
    {
        try {
            $resp = $this->client()->post('/api/v1/oportunidades/match', $criterios);
            return $resp->successful() ? $resp->json() : ['dados' => [], 'total' => 0];
        } catch (Exception $e) {
            Log::error('Erro ao avaliar match na API Go: ' . $e->getMessage());
            return ['dados' => [], 'total' => 0, 'erro' => $e->getMessage()];
        }
    }

    public function carregarDemonstracao(): array
    {
        try {
            $resp = $this->client()->post('/api/v1/demonstracao/carregar');
            return $resp->successful() ? $resp->json() : ['sucesso' => false, 'mensagem' => $resp->body()];
        } catch (Exception $e) {
            return ['sucesso' => false, 'mensagem' => $e->getMessage()];
        }
    }
}
