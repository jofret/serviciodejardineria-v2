<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Ítem de una Orden de Servicio con flujo "presupuesto directo por foto"
 * (sin Relevamiento) — mismo esquema que RelevamientoWorkItem (descripción,
 * observaciones, fotos, incluye retiro) para que "Revisar y presupuestar"
 * y la proforma pública puedan mostrar los ítems de los dos flujos con el
 * mismo componente, sin importar de dónde vienen.
 */
class ServiceOrderItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'service_order_id',
        'description',
        'observations',
        'includes_pickup',
        'order',
    ];

    protected $casts = [
        'includes_pickup' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
