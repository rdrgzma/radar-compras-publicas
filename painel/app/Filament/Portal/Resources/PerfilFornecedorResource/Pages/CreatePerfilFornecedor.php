<?php

namespace App\Filament\Portal\Resources\PerfilFornecedorResource\Pages;

use App\Filament\Portal\Resources\PerfilFornecedorResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerfilFornecedor extends CreateRecord
{
    protected static string $resource = PerfilFornecedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Garante que o perfil seja estritamente gravado com a empresa autenticada
        $data['empresa_id'] = auth()->user()->empresa_id;
        return $data;
    }
}
