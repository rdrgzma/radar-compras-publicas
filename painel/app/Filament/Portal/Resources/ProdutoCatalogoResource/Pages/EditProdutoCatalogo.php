<?php

namespace App\Filament\Portal\Resources\ProdutoCatalogoResource\Pages;

use App\Filament\Portal\Resources\ProdutoCatalogoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProdutoCatalogo extends EditRecord
{
    protected static string $resource = ProdutoCatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
