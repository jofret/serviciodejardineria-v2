<?php

namespace App\Filament\Resources\RelevadorResource\Pages;

use App\Filament\Resources\RelevadorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRelevador extends CreateRecord
{
    protected static string $resource = RelevadorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'relevador';

        return $data;
    }
}
