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
}
