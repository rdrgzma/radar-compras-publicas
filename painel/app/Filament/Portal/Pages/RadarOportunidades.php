<?php

namespace App\Filament\Portal\Pages;

use App\Models\FornecedorPerfil;
use App\Models\OportunidadeSelecao;
use App\Services\GoApiClient;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class RadarOportunidades extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'Radar de Oportunidades';

    protected static ?string $title = 'Oportunidades Encontradas no PNCP';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.portal.pages.radar-oportunidades';

    public array $oportunidades = [];
    public ?FornecedorPerfil $perfilAtivo = null;
    public int $totalEncontradas = 0;
    public int $paginaAtual = 1;
    public bool $apenasVigentes = true;
    public string $avisoApi = '';

    public function mount(GoApiClient $client): void
    {
        $this->carregarOportunidades($client);
    }

    public function carregarOportunidades(GoApiClient $client): void
    {
        $empresaId = auth()->user()?->empresa_id;
        $this->perfilAtivo = FornecedorPerfil::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->first();

        if (!$this->perfilAtivo) {
            $this->oportunidades = [];
            $this->totalEncontradas = 0;
            return;
        }

        $criterios = [
            'termos_obrigatorios'   => $this->perfilAtivo->termos_obrigatorios ?? [],
            'termos_excluidos'     => $this->perfilAtivo->termos_excluidos ?? [],
            'ufs'                  => $this->perfilAtivo->ufs ?? [],
            'apenas_vigentes'       => $this->apenasVigentes,
            'incluir_demonstrativos'=> true,
            'pagina'               => $this->paginaAtual,
            'por_pagina'           => 15,
        ];

        $resp = $client->avaliarMatch($criterios);

        if (isset($resp['erro'])) {
            $this->avisoApi = 'API Go temporariamente indisponível. Detalhes: ' . $resp['erro'];
            $this->oportunidades = [];
            $this->totalEncontradas = 0;
            return;
        }

        $this->avisoApi = '';
        $this->oportunidades = $resp['dados'] ?? [];
        $this->totalEncontradas = $resp['total'] ?? 0;
    }

    public function alternarFiltroVigentes(GoApiClient $client): void
    {
        $this->apenasVigentes = !$this->apenasVigentes;
        $this->paginaAtual = 1;
        $this->carregarOportunidades($client);
    }

    public function salvarSelecao(string $numeroControle, string $status, ?string $observacoes): void
    {
        $empresaId = auth()->user()->empresa_id;

        OportunidadeSelecao::updateOrCreate(
            [
                'empresa_id'           => $empresaId,
                'numero_controle_pncp' => $numeroControle,
            ],
            [
                'status'            => $status,
                'observacoes'       => $observacoes,
                'atualizado_por_id' => auth()->id(),
            ]
        );

        Notification::make()
            ->title('Oportunidade Salva na Seleção!')
            ->body("Status atualizado para: {$status}")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sincronizarAgora')
                ->label('Sincronizar PNCP Agora')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Iniciar Sincronização com o PNCP')
                ->modalDescription('Deseja consultar contratações recentes no Portal Nacional de Contratações Públicas?')
                ->action(function (GoApiClient $client) {
                    $res = $client->iniciarSincronizacao(
                        now()->subDays(1)->format('Y-m-d'),
                        now()->format('Y-m-d'),
                        [6, 8],
                        'real'
                    );

                    if ($res['sucesso'] ?? false) {
                        Notification::make()
                            ->title('Sincronização Iniciada!')
                            ->body('A coleta está em andamento pela API Go.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Aviso de Sincronização')
                            ->body($res['mensagem'] ?? 'Não foi possível disparar.')
                            ->warning()
                            ->send();
                    }
                    $this->carregarOportunidades($client);
                }),
        ];
    }
}
