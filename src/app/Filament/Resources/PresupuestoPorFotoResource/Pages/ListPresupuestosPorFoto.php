<?php

namespace App\Filament\Resources\PresupuestoPorFotoResource\Pages;

use App\Filament\Resources\PresupuestoPorFotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPresupuestosPorFoto extends ListRecords
{
    protected static string $resource = PresupuestoPorFotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear'),
        ];
    }
}
