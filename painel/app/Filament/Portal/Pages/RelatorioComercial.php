<?php

namespace App\Filament\Portal\Pages;

use App\Models\EditalItem;
use App\Models\Empresa;
use App\Models\ItemComparacao;
use App\Models\ProdutoCatalogo;
use App\Services\CsvExportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioComercial extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Relatório Comercial';

    protected static ?string $title = 'Relatório Comercial de Oportunidades Identificadas';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.portal.pages.relatorio-comercial';

    public function exportarCsv(CsvExportService $csvService): StreamedResponse
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            $empresaId = Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id ?? Empresa::first()?->id;
        }

        $comparacoes = ItemComparacao::with(['editalItem', 'produtoCatalogo'])
            ->where('empresa_id', $empresaId)
            ->get();

        $dadosExport = [];
        foreach ($comparacoes as $c) {
            $item = $c->editalItem;
            $prod = $c->produtoCatalogo;
            $dadosExport[] = [
                'Numero_PNCP'             => $item->numero_controle_pncp,
                'Edital'                  => $item->numero_edital ?? '0103/2026',
                'Orgao'                   => $item->orgao_razao_social ?? 'MUNICIPIO DE CANOAS',
                'Item_Numero'             => $item->item_numero,
                'Lote_Numero'             => $item->lote_numero ?? '',
                'CATMAT'                  => $item->catmat ?? '',
                'Descricao_Edital'        => $item->descricao_principal,
                'Quantidade'              => $item->quantidade,
                'Unidade_Edital'          => $item->unidade_medida,
                'Valor_Unitario_Maximo'   => $item->valor_unitario_estimado,
                'Valor_Total_Item'        => $item->valor_total_estimado,
                'Produto_Ecoclean'        => $prod?->nome ?? '',
                'Embalagem_Ecoclean'      => $prod?->embalagem ?? '',
                'Classificacao'           => $c->classificacao,
                'Motivo_Correspondencia'  => $c->motivo_correspondencia,
                'Divergencia_Tecnica'     => $c->divergencias_especificacao ?? '',
                'Interesse_Ecoclean'      => $c->feedback_usuario ?? 'em_analise',
                'Fonte_Verificacao'       => $item->link_fonte ?? 'https://pregaobanrisul.com.br/editais/0103_2026/349814',
                'Data_Coleta'             => $item->data_coleta?->format('d/m/Y') ?? '21/10/2026',
            ];
        }

        Notification::make()
            ->title('Relatório Comercial Exportado')
            ->body('Download do arquivo CSV sanitizado iniciado com sucesso.')
            ->success()
            ->send();

        return $csvService->exportarOportunidades($dadosExport, 'relatorio-comercial-ecoclean-oportunidades.csv');
    }

    public function getViewData(): array
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            $empresaId = Empresa::where('razao_social', 'like', '%Ecoclean%')->first()?->id ?? Empresa::first()?->id;
        }

        $empresa = Empresa::find($empresaId);

        $comparacoes = ItemComparacao::with(['editalItem', 'produtoCatalogo'])
            ->where('empresa_id', $empresaId)
            ->get();

        $totalValorItensCompativeis = $comparacoes->sum(fn ($c) => $c->editalItem->valor_total_estimado ?? 0);
        $totalItensConfirmados = $comparacoes->where('classificacao', 'confirmada')->count();

        // Contratações Históricas Identificadas
        $publicacoes = [
            [
                'orgao'               => 'Município de Canoas / RS',
                'edital'              => 'Edital 0103/2026 (RP 025/2026)',
                'modalidade'          => 'Pregão Eletrônico SRP (14.133/21)',
                'pncp'                => '88577416000118-1-000066/2026',
                'link_fonte'          => 'https://pregaobanrisul.com.br/editais/0103_2026/349814',
                'objeto'              => 'Aquisição de materiais de higiene e limpeza para Almoxarifados das Secretarias de Administração, Educação e Saúde.',
                'valor_global'        => 61203954.72,
                'valor_compativeis'   => 32863953.53,
                'itens_relacionados'  => 21,
                'situacao'            => 'Exemplo Histórico Recente (Homologado 16/10/2026)',
                'local_entrega'       => 'Almoxarifados de Canoas/RS (Centro, Harmonia e Igara)',
            ],
            [
                'orgao'               => 'Município de Canoas / RS',
                'edital'              => 'Edital 0212/2026',
                'modalidade'          => 'Dispensa com Disputa (Art. 75, II)',
                'pncp'                => '88577416000118-1-000112/2026',
                'link_fonte'          => 'https://pregaobanrisul.com.br/editais/0212_2026/352670',
                'objeto'              => 'Aquisição de materiais de higiene e limpeza para manutenção de estoque crítico da SMA.',
                'valor_global'        => 47273.95,
                'valor_compativeis'   => 47273.95,
                'itens_relacionados'  => 8,
                'situacao'            => 'Exemplo Histórico Recente (Homologado 16/07/2026)',
                'local_entrega'       => 'Almoxarifado Central Canoas/RS',
            ],
            [
                'orgao'               => 'Fundação Municipal de Saúde de Canoas / RS',
                'edital'              => 'Edital 0002/2026',
                'modalidade'          => 'Pregão Eletrônico SRP (14.133/21)',
                'pncp'                => '14885499000176-1-000003/2026',
                'link_fonte'          => 'https://pregaobanrisul.com.br/editais/0002_2026/347529',
                'objeto'              => 'Registro de preços para materiais de higiene e limpeza em atendimento às demandas da FMSC.',
                'valor_global'        => 16257.42,
                'valor_compativeis'   => 16257.42,
                'itens_relacionados'  => 5,
                'situacao'            => 'Exemplo Histórico Recente (Homologado 29/04/2026)',
                'local_entrega'       => 'Almoxarifado da Saúde de Canoas/RS',
            ],
        ];

        return [
            'empresa'                    => $empresa,
            'publicacoes'                => $publicacoes,
            'comparacoes'                => $comparacoes,
            'totalValorItensCompativeis' => $totalValorItensCompativeis,
            'totalItensConfirmados'      => $totalItensConfirmados,
            'totalItensAnalisados'       => $comparacoes->count(),
        ];
    }
}
