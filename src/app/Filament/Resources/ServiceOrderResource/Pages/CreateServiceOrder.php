<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Resources\ServiceOrderResource;
use App\Models\Property;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

/**
 * Único camino de creación manual que queda: "presupuesto directo por
 * foto". El de "con relevamiento" ya no se crea a mano — se genera solo
 * cuando el relevador envía el Relevamiento (ver
 * Relevamiento::markAsSubmitted()). Por eso este form no reutiliza
 * ServiceOrderResource::form() completo (ese sigue con el selector de
 * flujo y todos los campos — lo sigue usando EditServiceOrder para
 * órdenes con relevamiento ya existentes).
 */
class CreateServiceOrder extends CreateRecord
{
    protected static string $resource = ServiceOrderResource::class;

    public function getTitle(): string
    {
        return 'Crear orden con foto';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ServiceOrderResource::propertyField(),
                ...ServiceOrderResource::photoFlowFields(),
            ]);
    }

    /**
     * Sin campo Cliente en este form — el cliente sale solo de la
     * Propiedad elegida (toda Property ya tiene su Customer).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['customer_id'] = Property::findOrFail($data['property_id'])->customer_id;
        $data['flow_type'] = 'presupuesto_directo';
        $data['status'] = 'presupuestado_enviado';

        return $data;
    }
}
