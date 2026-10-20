<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label('Perfil de Acesso')
                    ->options([
                        'admin'      => 'Administrador Global',
                        'fornecedor' => 'Fornecedor (Portal)',
                    ])
                    ->required()
                    ->default('fornecedor'),
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa Vinculada')
                    ->relationship('empresa', 'razao_social')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Papel')
                    ->formatStateUsing(fn ($state) => $state === 'admin' ? 'Administrador' : 'Fornecedor')
                    ->colors([
                        'danger'  => 'admin',
                        'success' => 'fornecedor',
                    ]),
                Tables\Columns\TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Papel')
                    ->options([
                        'admin'      => 'Administrador',
                        'fornecedor' => 'Fornecedor',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'edit' => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
