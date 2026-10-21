<?php

namespace App\Filament\Pages;

use App\Services\GoApiClient;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms;

class Sincronizacoes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Monitor de Sincronizações';

    protected static ?string $title = 'Sincronizações do PNCP';

    protected static string|\UnitEnum|null $navigationGroup = 'Operações & Coleta';

    protected string $view = 'filament.pages.sincronizacoes';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public array $historico = [];
    public array $saudeApi = [];
    public ?string $filtroStatus = null;
    public int $paginaAtual = 1;
    public int $totalRegistros = 0;

    public function mount(GoApiClient $client): void
    {
        $this->carregarDados($client);
    }

    public function carregarDados(GoApiClient $client): void
    {
        $this->saudeApi = $client->checkSaude();
        $resp = $client->listarSincronizacoes($this->paginaAtual, 15, $this->filtroStatus);
        $this->historico = $resp['dados'] ?? [];
        $this->totalRegistros = $resp['total'] ?? 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('carregarDemonstracao')
                ->label('Carregar Dados Demonstrativos')
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Carregar Modo Demonstrativo')
                ->modalDescription('Deseja popular o banco de dados com oportunidades fictícias realistas identificadas como [DEMONSTRAÇÃO]?')
                ->action(function (GoApiClient $client) {
                    $res = $client->carregarDemonstracao();
                    if ($res['sucesso'] ?? false) {
                        Notification::make()
                            ->title('Dados Demonstrativos Carregados!')
                            ->body($res['mensagem'] ?? 'Oportunidades simuladas inseridas.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Erro ao carregar demonstração')
                            ->body($res['mensagem'] ?? 'Falha na requisição.')
                            ->danger()
                            ->send();
                    }
                    $this->carregarDados($client);
                }),

            Action::make('dispararColeta')
                ->label('Disparar Coleta Manual')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('primary')
                ->form([
                    Forms\Components\DatePicker::make('data_inicial')
                        ->label('Data Inicial')
                        ->default(now()->subDays(1)->format('Y-m-d'))
                        ->required(),
                    Forms\Components\DatePicker::make('data_final')
                        ->label('Data Final')
                        ->default(now()->format('Y-m-d'))
                        ->required(),
                    Forms\Components\Select::make('modalidades')
                        ->label('Modalidades de Contratação')
                        ->multiple()
                        ->options([
                            6 => '6 - Pregão Eletrônico',
                            8 => '8 - Dispensa de Licitação',
                        ])
                        ->default([6, 8])
                        ->required(),
                ])
                ->action(function (array $data, GoApiClient $client) {
                    $res = $client->iniciarSincronizacao(
                        $data['data_inicial'],
                        $data['data_final'],
                        array_map('intval', $data['modalidades']),
                        'real'
                    );

                    if ($res['sucesso'] ?? false) {
                        Notification::make()
                            ->title('Sincronização Iniciada!')
                            ->body('A coleta está sendo executada em segundo plano pela API Go.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Não foi possível iniciar')
                            ->body($res['mensagem'] ?? 'Erro desconhecido.')
                            ->warning()
                            ->send();
                    }
                    $this->carregarDados($client);
                }),
        ];
    }
}
