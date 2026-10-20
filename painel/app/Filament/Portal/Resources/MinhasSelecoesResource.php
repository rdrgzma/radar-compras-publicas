<?php

namespace App\Filament\Portal\Resources;

use App\Models\OportunidadeSelecao;
use App\Services\CsvExportService;
use App\Services\GoApiClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MinhasSelecoesResource extends Resource
{
    protected static ?string $model = OportunidadeSelecao::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationLabel = 'Minhas Seleções';

    protected static ?string $modelLabel = 'Seleção';

    protected static ?string $pluralModelLabel = 'Minhas Seleções';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        // Isolamento estrito por empresa logada
        $empresaId = auth()->user()?->empresa_id;
        return parent::getEloquentQuery()->where('empresa_id', $empresaId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_controle_pncp')
                    ->label('Nº de Controle PNCP')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Situação da Seleção')
                    ->options([
                        'em_analise'  => 'Em Análise',
                        'selecionada' => 'Selecionada (Participar)',
                        'descartada'  => 'Descartada',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações Internas da Empresa')
                    ->rows(3)
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_controle_pncp')
                    ->label('Controle PNCP')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'selecionada' => 'Selecionada',
                        'descartada'  => 'Descartada',
                        default       => 'Em Análise',
                    })
                    ->colors([
                        'warning' => 'em_analise',
                        'success' => 'selecionada',
                        'danger'  => 'descartada',
                    ]),
                Tables\Columns\TextColumn::make('observacoes')
                    ->label('Observações')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('atualizadoPor.name')
                    ->label('Atualizado por')
                    ->default('—'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtrar por Status')
                    ->options([
                        'em_analise'  => 'Em Análise',
                        'selecionada' => 'Selecionada',
                        'descartada'  => 'Descartada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verPncp')
                    ->label('Ver Edital')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (OportunidadeSelecao $record, GoApiClient $client) {
                        $op = $client->obterOportunidade($record->numero_controle_pncp);
                        return $op['link_pncp'] ?? 'https://pncp.gov.br';
                    })
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportarCsv')
                    ->label('Exportar Seleção em CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (GoApiClient $client) {
                        $empresaId = auth()->user()->empresa_id;
                        $selecoes = OportunidadeSelecao::where('empresa_id', $empresaId)
                            ->orderByDesc('updated_at')
                            ->get();

                        if ($selecoes->isEmpty()) {
                            Notification::make()
                                ->title('Nenhum registro para exportação')
                                ->body('Sua empresa ainda não selecionou oportunidades.')
                                ->warning()
                                ->send();
                            return null;
                        }

                        $cabecalhos = [
                            'Numero Controle PNCP',
                            'Status Selecao',
                            'Observacoes',
                            'Orgao Licitante',
                            'CNPJ Orgao',
                            'UF',
                            'Modalidade',
                            'Valor Estimado (R$)',
                            'Objeto da Licitacao',
                            'Link Oficial PNCP',
                            'Data Coleta',
                            'Atualizado Em',
                        ];

                        $linhas = [];
                        foreach ($selecoes as $s) {
                            $op = $client->obterOportunidade($s->numero_controle_pncp) ?? [];
                            $valor = $op['valor_total_estimado'] ?? null;
                            $valorFmt = is_numeric($valor) ? number_format($valor, 2, ',', '.') : 'não informado';

                            $linhas[] = [
                                $s->numero_controle_pncp,
                                $s->status_label,
                                $s->observacoes ?? '',
                                $op['orgao_razao_social'] ?? 'não informado',
                                $op['orgao_cnpj'] ?? 'não informado',
                                $op['unidade_uf_sigla'] ?? 'não informado',
                                $op['modalidade_nome'] ?? 'não informado',
                                $valorFmt,
                                $op['objeto'] ?? 'não informado',
                                $op['link_pncp'] ?? 'não informado',
                                !empty($op['data_coleta']) ? \Carbon\Carbon::parse($op['data_coleta'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') : 'não informado',
                                $s->updated_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') ?? '',
                            ];
                        }

                        $csvContent = CsvExportService::gerarCsv($cabecalhos, $linhas, ';');
                        $filename = 'radar-compras-selecao-' . now()->format('Y-m-d-His') . '.csv';

                        return response()->streamDownload(function () use ($csvContent) {
                            echo $csvContent;
                        }, $filename, [
                            'Content-Type'        => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMinhasSelecoes::route('/'),
            'edit' => Pages\EditMinhasSelecoes::route('/{record}/edit'),
        ];
    }
}
