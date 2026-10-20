<?php

use App\Services\GoApiClient;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Comando Artisan para disparar a sincronização via CLI ou Scheduler
Artisan::command('radar:sincronizar {--modo=real} {--dias=1}', function (GoApiClient $client) {
    $modo = (string) $this->option('modo');
    $dias = (int) $this->option('dias');

    $dtInicio = now()->subDays($dias)->format('Y-m-d');
    $dtFim = now()->format('Y-m-d');

    $this->info("Disparando sincronização do PNCP ({$dtInicio} a {$dtFim}, modo: {$modo})...");

    $res = $client->iniciarSincronizacao($dtInicio, $dtFim, [6, 8], $modo);

    if ($res['sucesso'] ?? false) {
        $this->info("Sincronização aceita! Job ID: " . ($res['job_id'] ?? 'N/A'));
        Log::info("Scheduler disparou sincronização PNCP com sucesso. Job ID: " . ($res['job_id'] ?? 'N/A'));
    } else {
        $this->error("Falha ao disparar sincronização: " . ($res['mensagem'] ?? 'Erro desconhecido'));
        Log::warning("Falha no disparo do Scheduler PNCP: " . ($res['mensagem'] ?? 'Erro desconhecido'));
    }
})->purpose('Dispara a sincronização de contratações do PNCP via API Go');

// Agendamento diário automático às 06:00 (fuso America/Sao_Paulo)
Schedule::command('radar:sincronizar --modo=real --dias=1')
    ->dailyAt('06:00')
    ->timezone('America/Sao_Paulo')
    ->withoutOverlapping()
    ->onOneServer();
