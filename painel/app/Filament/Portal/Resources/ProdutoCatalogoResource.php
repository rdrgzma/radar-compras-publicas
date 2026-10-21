<?php

namespace App\Filament\Portal\Resources;

use App\Filament\Portal\Resources\ProdutoCatalogoResource\Pages;
use App\Models\ProdutoCatalogo;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProdutoCatalogoResource extends Resource
{
    protected static ?string $model = ProdutoCatalogo::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Catálogo da Empresa';

    protected static ?string $modelLabel = 'Produto do Catálogo';

    protected static ?string $pluralModelLabel = 'Catálogo de Produtos';

    protected static ?int $navigationSort = 2;

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
                    ->label('Nome do Produto')
                    ->placeholder('Ex: Detergente Neutro Concentrado 5L')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('categoria')
                    ->label('Família / Categoria')
                    ->options([
                        'Água sanitária, detergentes e desinfetantes' => 'Água sanitária, detergentes e desinfetantes',
                        'Desengordurantes e limpa-vidros'            => 'Desengordurantes e limpa-vidros',
                        'Sabonetes líquidos'                         => 'Sabonetes líquidos',
                        'Papel higiênico e papel-toalha'             => 'Papel higiênico e papel-toalha',
                        'Sacos para lixo'                            => 'Sacos para lixo',
                        'Rodos, baldes, esponjas, panos e escovas'   => 'Rodos, baldes, esponjas, panos e escovas',
                        'Copos descartáveis e luvas'                 => 'Copos descartáveis e luvas',
                        'Outros'                                     => 'Outros',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('embalagem')
                    ->label('Embalagem')
                    ->placeholder('Ex: Bombona 5L, Frasco 500ml, Pacote 100un')
                    ->maxLength(100),

                Forms\Components\TextInput::make('capacidade')
                    ->label('Capacidade / Dimensões')
                    ->placeholder('Ex: 5 Litros, 500 ml, 20x21 cm')
                    ->maxLength(100),

                Forms\Components\TextInput::make('unidade')
                    ->label('Unidade de Fornecimento')
                    ->placeholder('Ex: Unidade, Caixa, Bombona, Pacote')
                    ->maxLength(50),

                Forms\Components\TextInput::make('preco_referencia_site')
                    ->label('Preço Público de Referência (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->helperText('Preço extraído do site para referência; não vincular como proposta comercial fechada.'),

                Forms\Components\TextInput::make('url_origem')
                    =>label('URL de Origem no Catálogo')
                    ->default('https://www.produtosecoclean.com.br/produtos/')
                    ->maxLength(255),

                Forms\Components\Textarea::make('especificacao_tecnica')
                    ->label('Especificação Técnica')
                    ->rows(3)
                    ->placeholder('Princípio ativo, concentração, normas ABNT aplicáveis...'),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações Comerciais')
                    ->rows(2)
                    ->placeholder('Ex: Abrangência de entrega sujeita a confirmação comercial para esta linha.'),

                Forms\Components\Toggle::make('ativo')
                    ->label('Produto Ativo no Catálogo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('categoria')
                    ->label('Família')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Água sanitária, detergentes e desinfetantes' => 'info',
                        'Desengordurantes e limpa-vidros'            => 'warning',
                        'Sabonetes líquidos'                         => 'success',
                        'Papel higiênico e papel-toalha'             => 'primary',
                        'Sacos para lixo'                            => 'danger',
                        default                                      => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('embalagem')
                    ->label('Embalagem')
                    ->searchable(),

                Tables\Columns\TextColumn::make('capacidade')
                    ->label('Capacidade'),

                Tables\Columns\TextColumn::make('preco_referencia_site')
                    ->label('Preço Ref.')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('data_coleta')
                    ->label('Coleta')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->label('Família do Produto')
                    ->options([
                        'Água sanitária, detergentes e desinfetantes' => 'Água sanitária, detergentes e desinfetantes',
                        'Desengordurantes e limpa-vidros'            => 'Desengordurantes e limpa-vidros',
                        'Sabonetes líquidos'                         => 'Sabonetes líquidos',
                        'Papel higiênico e papel-toalha'             => 'Papel higiênico e papel-toalha',
                        'Sacos para lixo'                            => 'Sacos para lixo',
                        'Rodos, baldes, esponjas, panos e escovas'   => 'Rodos, baldes, esponjas, panos e escovas',
                        'Copos descartáveis e luvas'                 => 'Copos descartáveis e luvas',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProdutoCatalogos::route('/'),
            'create' => Pages\CreateProdutoCatalogo::route('/create'),
            'edit'   => Pages\EditProdutoCatalogo::route('/{record}/edit'),
        ];
    }
}
