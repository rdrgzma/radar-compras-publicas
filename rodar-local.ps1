# ==============================================================================
# Script de Execução Local — Radar de Compras Públicas
# Uso: .\rodar-local.ps1
# ==============================================================================

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   RADAR DE COMPRAS PÚBLICAS (PNCP) — INICIALIZAÇÃO LOCAL" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# 1. Verificar binário da API Go
$apiGoExe = Join-Path $baseDir "api-go\radar-api.exe"
if (-not (Test-Path $apiGoExe)) {
    Write-Host "[1/3] Compilando API Go..." -ForegroundColor Yellow
    Push-Location (Join-Path $baseDir "api-go")
    go build -o radar-api.exe ./cmd/api
    Pop-Location
} else {
    Write-Host "[1/3] API Go compilada encontrada." -ForegroundColor Green
}

# 2. Configurações de Ambiente
$env:DB_PORT = "5433"
$env:DB_USER = "postgres"
$env:DB_PASSWORD = ""
$env:DB_NAME = "radar_compras"
$env:DB_SCHEMA = "api_go"
$env:PORT = "8000"
$env:RADAR_INTERNAL_TOKEN = "radar-token-interno-ultra-seguro-compartilhado-com-painel"

$cssPath = Join-Path $baseDir "painel\public\css\filament\filament\app.css"
if (-not (Test-Path $cssPath)) {
    Write-Host "[2/3] Publicando assets estáticos do Filament..." -ForegroundColor Yellow
    Push-Location (Join-Path $baseDir "painel")
    if (Test-Path "C:\Users\00026108\.config\herd\bin\php84.bat") {
        & "C:\Users\00026108\.config\herd\bin\php84.bat" artisan filament:assets --no-interaction
    } else {
        php artisan filament:assets --no-interaction
    }
    Pop-Location
}

Write-Host "[2/3] Verificando serviços em execução..." -ForegroundColor Yellow
$procGo = Get-Process -Name "radar-api" -ErrorAction SilentlyContinue
if ($null -eq $procGo) {
    Write-Host "Iniciando API Go em segundo plano..." -ForegroundColor Yellow
    Start-Process -FilePath $apiGoExe -WorkingDirectory (Join-Path $baseDir "api-go")
    Start-Sleep -Seconds 2
} else {
    Write-Host "API Go já está em execução (PID: $($procGo.Id))." -ForegroundColor Green
}

# 3. Teste de Healthcheck
try {
    $saude = Invoke-RestMethod -Uri "http://localhost:8000/saude" -TimeoutSec 3
    Write-Host "[3/3] API Go operacional: Banco=$($saude.banco_de_dados), Conector=$($saude.conector_pncp)" -ForegroundColor Green
} catch {
    Write-Host "[3/3] Aviso: API Go ainda inicializando ou na porta 8000." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   SISTEMA PRONTO PARA USO E DEMONSTRACAO" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "Painel Administrativo:  http://localhost:8080/admin" -ForegroundColor White
Write-Host "  Login:  admin@radar.com.br | Senha: admin123" -ForegroundColor Gray
Write-Host "Portal do Fornecedor:   http://localhost:8080/portal" -ForegroundColor White
Write-Host "  Login:  fornecedor@alphaepi.com.br | Senha: fornecedor123" -ForegroundColor Gray
Write-Host "API Go Healthcheck:     http://localhost:8000/saude" -ForegroundColor White
Write-Host "==========================================================" -ForegroundColor Cyan
