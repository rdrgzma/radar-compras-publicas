<?php

namespace App\Filament\Portal\Pages;

use App\Models\AlertaHistorico;
use App\Models\Empresa;
use App\Models\FornecedorPerfil;
use App\Models\ItemComparacao;
use App\Services\GoApiClient;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class CentralAlertas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Central de Alertas';

    protected static ?string $title = 'Central de Alertas e Notificações (Modo Prévia)';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.portal.pages.central-alertas';

    public ?int $alertaSelecionadoId = null;
    public ?AlertaHistorico $alertaSelecionado = null;
    public string $abaAtiva = 'previa_email'; // previa_email, historico, resumo_diario

    public function mount(): void
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            $empresaId = Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id ?? Empresa::first()?->id;
        }

        $primeiro = AlertaHistorico::where('empresa_id', $empresaId)->latest()->first();
        if ($primeiro) {
            $this->alertaSelecionadoId = $primeiro->id;
            $this->alertaSelecionado = $primeiro;
        }
    }

    public function selecionarAlerta(int $id): void
    {
        $this->alertaSelecionadoId = $id;
        $this->alertaSelecionado = AlertaHistorico::find($id);
    }

    public function registrarFeedback(int $alertaId, string $feedback): void
    {
        $alerta = AlertaHistorico::find($alertaId);
        if ($alerta) {
            $alerta->update([
                'feedback_alerta' => $feedback,
            ]);

            if ($this->alertaSelecionadoId === $alertaId) {
                $this->alertaSelecionado = $alerta->fresh();
            }

            $label = match ($feedback) {
                'relevante'       => 'Relevante',
                'irrelevante'     => 'Irrelevante',
                'ja_participo'    => 'Já Participo',
                'tenho_interesse' => 'Tenho Interesse',
                default           => $feedback,
            };

            Notification::make()
                ->title('Feedback registrado!')
                ->body("Avaliação registrada como: {$label}.")
                ->success()
                ->send();
        }
    }

    public function gerarPreviaAlerta(string $numeroControle): void
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            $empresaId = Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id ?? Empresa::first()?->id;
        }

        // Controle para evitar duplicações
        $existente = AlertaHistorico::where('empresa_id', $empresaId)
            ->where('numero_controle_pncp', $numeroControle)
            ->first();

        if ($existente) {
            $this->selecionarAlerta($existente->id);
            Notification::make()
                ->title('Alerta já existente!')
                ->body('Exibindo a prévia existente para evitar duplicação.')
                ->info()
                ->send();
            return;
        }

        Notification::make()
            ->title('Alerta gerado em modo prévia')
            ->body('A prévia foi registrada no histórico interno sem envio externo.')
            ->success()
            ->send();
    }

    public function simularDisparoDemonstrativo(): void
    {
        Notification::make()
            ->title('Envio Real Bloqueado')
            ->body('Modo de Demonstração ativo: envio de e-mails bloqueado por segurança para a empresa demonstrativa Ecoclean.')
            ->warning()
            ->send();
    }

    public function getViewData(): array
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            $empresaId = Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id ?? Empresa::first()?->id;
        }

        $historicoAlertas = AlertaHistorico::where('empresa_id', $empresaId)
            ->latest()
            ->get();

        $empresa = Empresa::find($empresaId);
        $perfil = FornecedorPerfil::where('empresa_id', $empresaId)->where('ativo', true)->first();

        $comparacoes = ItemComparacao::with('editalItem')
            ->where('empresa_id', $empresaId)
            ->get();

        return [
            'historicoAlertas'  => $historicoAlertas,
            'alertaSelecionado' => $this->alertaSelecionado,
            'empresa'           => $empresa,
            'perfil'            => $perfil,
            'totalComparacoes'  => $comparacoes->count(),
            'valorTotalItens'   => $comparacoes->sum(fn ($c) => $c->editalItem->valor_total_estimado ?? 0),
        ];
    }
}
