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
}
