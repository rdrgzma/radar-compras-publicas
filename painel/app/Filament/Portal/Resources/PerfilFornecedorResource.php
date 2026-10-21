<?php

namespace App\Filament\Portal\Resources;

use App\Filament\Portal\Resources\PerfilFornecedorResource\Pages;
use App\Models\FornecedorPerfil;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PerfilFornecedorResource extends Resource
{
    protected static ?string $model = FornecedorPerfil::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Perfil de Fornecimento';

    protected static ?string $modelLabel = 'Perfil de Fornecimento';

    protected static ?string $pluralModelLabel = 'Perfis de Fornecimento';

    public static function getEloquentQuery(): Builder
    {
        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId && auth()->user()?->isAdmin()) {
            return parent::getEloquentQuery();
        }
        return parent::getEloquentQuery()->where('empresa_id', $empresaId);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa Vinculada')
                    ->relationship('empresa', 'razao_social')
                    ->default(fn () => auth()->user()?->empresa_id ?? \App\Models\Empresa::first()?->id)
                    ->required()
                    ->visible(fn () => auth()->user()?->isAdmin() && !auth()->user()?->empresa_id),
                Forms\Components\Hidden::make('empresa_id')
                    ->default(fn () => auth()->user()?->empresa_id ?? \App\Models\Empresa::first()?->id)
                    ->visible(fn () => !auth()->user()?->isAdmin() || (bool) auth()->user()?->empresa_id),
                Forms\Components\TextInput::make('nome')
                    ->label('Nome do Perfil')
                    ->placeholder('Ex: Fornecimento de EPIs e Uniformes')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TagsInput::make('termos_obrigatorios')
                    ->label('Palavras-chave Obrigatórias / Incluídas')
                    ->helperText('Oportunidades contendo ao menos um desses termos no objeto serão selecionadas.')
                    ->placeholder('Digite a palavra e pressione Enter (ex: epi, uniforme, seguranca)')
                    ->required(),
                Forms\Components\TagsInput::make('termos_excluidos')
                    ->label('Palavras-chave Excluídas')
                    ->helperText('Oportunidades contendo qualquer um desses termos serão sumariamente descartadas.')
                    ->placeholder('Ex: asfalto, engenharia civil, reforma'),
                Forms\Components\Select::make('ufs')
                    ->label('Unidades Federativas (UFs) de Interesse')
                    ->multiple()
                    ->options([
                        'AC' => 'AC - Acre', 'AL' => 'AL - Alagoas', 'AP' => 'AP - Amapá',
                        'AM' => 'AM - Amazonas', 'BA' => 'BA - Bahia', 'CE' => 'CE - Ceará',
                        'DF' => 'DF - Distrito Federal', 'ES' => 'ES - Espírito Santo',
                        'GO' => 'GO - Goiás', 'MA' => 'MA - Maranhão', 'MT' => 'MT - Mato Grosso',
                        'MS' => 'MS - Mato Grosso do Sul', 'MG' => 'MG - Minas Gerais',
                        'PA' => 'PA - Pará', 'PB' => 'PB - Paraíba', 'PR' => 'PR - Paraná',
                        'PE' => 'PE - Pernambuco', 'PI' => 'PI - Piauí', 'RJ' => 'RJ - Rio de Janeiro',
                        'RN' => 'RN - Rio Grande do Norte', 'RS' => 'RS - Rio Grande do Sul',
                        'RO' => 'RO - Rondônia', 'RR' => 'RR - Roraima', 'SC' => 'SC - Santa Catarina',
                        'SP' => 'SP - São Paulo', 'SE' => 'SE - Sergipe', 'TO' => 'TO - Tocantins',
                    ])
                    ->helperText('Deixe vazio para monitorar licitações em todo o território nacional.'),
                Forms\Components\Toggle::make('ativo')
                    ->label('Perfil Ativo para o Radar')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('termos_obrigatorios')
                    ->label('Termos Incluídos')
                    ->badge(),
                Tables\Columns\TextColumn::make('termos_excluidos')
                    ->label('Termos Excluídos')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('ufs')
                    ->label('UFs')
                    ->badge()
                    ->color('info')
                    ->default('Brasil (Todas)'),
                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerfilFornecedors::route('/'),
            'create' => Pages\CreatePerfilFornecedor::route('/create'),
            'edit' => Pages\EditPerfilFornecedor::route('/{record}/edit'),
        ];
    }
}
