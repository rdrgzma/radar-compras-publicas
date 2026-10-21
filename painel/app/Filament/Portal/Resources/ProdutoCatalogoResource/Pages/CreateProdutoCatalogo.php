<?php

namespace App\Filament\Portal\Resources\ProdutoCatalogoResource\Pages;

use App\Filament\Portal\Resources\ProdutoCatalogoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProdutoCatalogo extends CreateRecord
{
    protected static string $resource = ProdutoCatalogoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['empresa_id'])) {
            $data['empresa_id'] = auth()->user()?->empresa_id ?? \App\Models\Empresa::first()?->id;
        }

        if (empty($data['data_coleta'])) {
            $data['data_coleta'] = now('America/Sao_Paulo');
        }

        return $data;
    }
}
