<?php

namespace App\Filament\Resources\RelevadorResource\Pages;

use App\Filament\Resources\RelevadorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRelevador extends EditRecord
{
    protected static string $resource = RelevadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
