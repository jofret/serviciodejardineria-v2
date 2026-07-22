<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Resources\ServiceOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceOrders extends ListRecords
{
    protected static string $resource = ServiceOrderResource::class;

    /**
     * Sin botón de creación manual: el flujo "con relevamiento" se genera
     * solo (Relevamiento::markAsSubmitted()), y el de foto directa ahora
     * se crea desde "Presupuestos por foto" (ver PresupuestoPorFotoResource).
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
