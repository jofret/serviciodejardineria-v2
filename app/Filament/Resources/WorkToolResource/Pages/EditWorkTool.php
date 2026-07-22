<?php

namespace App\Filament\Resources\WorkToolResource\Pages;

use App\Filament\Resources\WorkToolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkTool extends EditRecord
{
    protected static string $resource = WorkToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
