<?php

namespace App\Filament\Portal\Resources\ProdutoCatalogoResource\Pages;

use App\Filament\Portal\Resources\ProdutoCatalogoResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListProdutoCatalogos extends ListRecords
{
    protected static string $resource = ProdutoCatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Produto'),

            Actions\Action::make('reimportar_catalogo')
                ->label('Reimportar Catálogo Demonstrativo')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Reimportar Catálogo Demonstrativo?')
                ->modalDescription('Os produtos do catálogo público serão atualizados sem duplicar registros existentes.')
                ->action(function () {
                    Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\EcocleanDemonstracaoSeeder',
                        '--force' => true,
                    ]);

                    Notification::make()
                        ->title('Catálogo atualizado com sucesso!')
                        ->body('Os produtos foram reimportados com rastreabilidade da origem pública.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
