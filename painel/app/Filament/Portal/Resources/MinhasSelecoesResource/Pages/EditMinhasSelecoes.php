<?php

namespace App\Filament\Portal\Resources\MinhasSelecoesResource\Pages;

use App\Filament\Portal\Resources\MinhasSelecoesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMinhasSelecoes extends EditRecord
{
    protected static string $resource = MinhasSelecoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['atualizado_por_id'] = auth()->id();
        return $data;
    }
}
