<?php

namespace App\Filament\Portal\Resources\PerfilFornecedorResource\Pages;

use App\Filament\Portal\Resources\PerfilFornecedorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerfilFornecedors extends ListRecords
{
    protected static string $resource = PerfilFornecedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
