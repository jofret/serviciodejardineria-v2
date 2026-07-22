<?php

namespace App\Filament\Resources\PresupuestoPorFotoResource\Pages;

use App\Filament\Resources\PresupuestoPorFotoResource;
use App\Filament\Resources\RelevamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPresupuestoPorFoto extends EditRecord
{
    protected static string $resource = PresupuestoPorFotoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RelevamientoResource::normalizeCategoryData($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
