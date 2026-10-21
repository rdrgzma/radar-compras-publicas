<?php

namespace App\Filament\Portal\Resources\PerfilFornecedorResource\Pages;

use App\Filament\Portal\Resources\PerfilFornecedorResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerfilFornecedor extends CreateRecord
{
    protected static string $resource = PerfilFornecedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user?->isFornecedor()) {
            // Se for fornecedor, vincula estritamente à empresa do usuário autenticado
            $data['empresa_id'] = $user->empresa_id;
        } else {
            // Se for administrador criando perfil no portal, usa a empresa enviada ou faz fallback para a primeira empresa
            $data['empresa_id'] = $data['empresa_id'] ?? $user?->empresa_id ?? \App\Models\Empresa::first()?->id;
        }

        return $data;
    }
}
