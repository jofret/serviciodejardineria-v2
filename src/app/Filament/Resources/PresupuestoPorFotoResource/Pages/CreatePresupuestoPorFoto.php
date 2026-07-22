<?php

namespace App\Filament\Resources\PresupuestoPorFotoResource\Pages;

use App\Filament\Resources\PresupuestoPorFotoResource;
use App\Filament\Resources\RelevamientoResource;
use App\Filament\Resources\ServiceOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePresupuestoPorFoto extends CreateRecord
{
    protected static string $resource = PresupuestoPorFotoResource::class;

    public function getTitle(): string
    {
        return 'Crear presupuesto por foto';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RelevamientoResource::normalizeCategoryData($data);
    }

    /**
     * A diferencia de un Relevamiento normal, este "nace enviado": no hay
     * relevador ni visita pendiente, los datos y fotos ya están completos
     * en el mismo formulario de alta. markAsSubmitted() es el mismo
     * mecanismo que usa el relevador al enviar el suyo — genera la Orden
     * de Servicio automáticamente si todavía no existe.
     */
    protected function afterCreate(): void
    {
        $this->record->markAsSubmitted();
    }

    protected function getRedirectUrl(): string
    {
        $serviceOrder = $this->record->serviceOrder()->first();

        return ServiceOrderResource::getUrl('review-and-quote', ['record' => $serviceOrder]);
    }
}
