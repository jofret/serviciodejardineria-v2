<?php

namespace App\Filament\Resources\RelevamientoResource\Pages;

use App\Filament\Resources\RelevamientoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRelevamiento extends CreateRecord
{
    protected static string $resource = RelevamientoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RelevamientoResource::normalizeCategoryData($data);
    }
}
