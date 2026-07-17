<?php

namespace App\Filament\Resources\RelevadorResource\Pages;

use App\Filament\Resources\RelevadorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRelevadores extends ListRecords
{
    protected static string $resource = RelevadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
