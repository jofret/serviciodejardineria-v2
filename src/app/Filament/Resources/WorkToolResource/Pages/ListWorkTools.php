<?php

namespace App\Filament\Resources\WorkToolResource\Pages;

use App\Filament\Resources\WorkToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkTools extends ListRecords
{
    protected static string $resource = WorkToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
