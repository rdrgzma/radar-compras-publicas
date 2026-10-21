<?php

namespace App\Filament\Portal\Pages;

use App\Models\EditalItem;
use App\Models\ItemComparacao;
use App\Models\ProdutoCatalogo;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ComparacaoItens extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Comparação de Itens';

    protected static ?string $title = 'Comparação Técnica de Itens vs. Catálogo';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.portal.pages.comparacao-itens';

    public string $filtroEdital = '88577416000118-1-000066/2026';
    public string $filtroClassificacao = 'todos';
    public string $buscaTermo = '';

    public function mount(): void
    {
        // Garante que o seeder já foi executado caso a tabela de comparações esteja vazia
        $empresaId = auth()->user()?->empresa_id ?? \App\Models\Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id;
        if ($empresaId && ItemComparacao::where('empresa_id', $empresaId)->count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\EcocleanDemonstracaoSeeder',
                '--force' => true,
            ]);
        }
    }

    public function atualizarClassificacao(int $comparacaoId, string $novaClassificacao): void
    {
        $comp = ItemComparacao::find($comparacaoId);
        if ($comp) {
            $comp->update([
                'classificacao'   => $novaClassificacao,
                'revisado_por_id' => auth()->id(),
                'revisado_em'     => now('America/Sao_Paulo'),
            ]);

            Notification::make()
                ->title('Classificação atualizada!')
                ->body("Item classificado como: {$novaClassificacao}.")
                ->success()
                ->send();
        }
    }

    public function registrarFeedback(int $comparacaoId, string $feedback): void
    {
        $comp = ItemComparacao::find($comparacaoId);
        if ($comp) {
            $comp->update([
                'feedback_usuario' => $feedback,
                'revisado_por_id'  => auth()->id(),
                'revisado_em'      => now('America/Sao_Paulo'),
            ]);

            $label = match ($feedback) {
                'relevante'       => 'Relevante',
                'irrelevante'     => 'Irrelevante',
                'ja_participo'    => 'Já Participo',
                'tenho_interesse' => 'Tenho Interesse',
                default           => $feedback,
            };

            Notification::make()
                ->title('Feedback registrado com sucesso!')
                ->body("Interesse marcado como: {$label}.")
                ->success()
                ->send();
        }
    }

    public function vincularProduto(int $comparacaoId, int $produtoId): void
    {
        $comp = ItemComparacao::find($comparacaoId);
        $produto = ProdutoCatalogo::find($produtoId);

        if ($comp && $produto) {
            $comp->update([
                'produto_catalogo_id'    => $produto->id,
                'classificacao'          => 'confirmada',
                'motivo_correspondencia' => "Vinculação confirmada pelo usuário com '{$produto->nome}'.",
                'revisado_por_id'        => auth()->id(),
                'revisado_em'            => now('America/Sao_Paulo'),
            ]);

            Notification::make()
                ->title('Produto vinculado!')
                ->body("Item associado a {$produto->nome}.")
                ->success()
                ->send();
        }
    }

    public function getViewData(): array
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            $empresaId = \App\Models\Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id ?? \App\Models\Empresa::first()?->id;
        }

        $query = ItemComparacao::with(['editalItem', 'produtoCatalogo', 'revisadoPor'])
            ->where('empresa_id', $empresaId);

        if ($this->filtroEdital) {
            $query->whereHas('editalItem', function ($q) {
                $q->where('numero_controle_pncp', $this->filtroEdital);
            });
        }

        if ($this->filtroClassificacao !== 'todos') {
            $query->where('classificacao', $this->filtroClassificacao);
        }

        if (!empty($this->buscaTermo)) {
            $termo = '%' . strtolower($this->buscaTermo) . '%';
            $query->where(function ($q) use ($termo) {
                $q->whereHas('editalItem', function ($sub) use ($termo) {
                    $sub->whereRaw('LOWER(descricao_principal) LIKE ?', [$termo])
                        ->orWhereRaw('LOWER(descricao_detalhada) LIKE ?', [$termo]);
                })->orWhereHas('produtoCatalogo', function ($sub) use ($termo) {
                    $sub->whereRaw('LOWER(nome) LIKE ?', [$termo])
                        ->orWhereRaw('LOWER(categoria) LIKE ?', [$termo]);
                });
            });
        }

        $comparacoes = $query->orderBy('id', 'asc')->get();

        $produtosDisponiveis = ProdutoCatalogo::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $totalValorItens = $comparacoes->sum(fn ($c) => $c->editalItem->valor_total_estimado ?? 0);
        $totalItensConfirmados = $comparacoes->where('classificacao', 'confirmada')->count();

        return [
            'comparacoes'           => $comparacoes,
            'produtosDisponiveis'   => $produtosDisponiveis,
            'totalValorItens'       => $totalValorItens,
            'totalItensConfirmados' => $totalItensConfirmados,
            'totalItens'            => $comparacoes->count(),
            'empresaId'             => $empresaId,
        ];
    }
}
