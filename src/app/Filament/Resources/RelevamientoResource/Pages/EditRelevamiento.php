<?php

namespace App\Filament\Resources\RelevamientoResource\Pages;

use App\Filament\Resources\RelevamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRelevamiento extends EditRecord
{
    protected static string $resource = RelevamientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['category_id']) && filled($data['category_other'] ?? null)) {
            $data['category_id'] = 'otro';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RelevamientoResource::normalizeCategoryData($data);
    }
}
