<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ServiceOrder extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const PIPELINE_STATUSES = [
        'visita_programada' => 'Visita programada',
        'visita_realizada' => 'Visita realizada',
        'presupuestado_enviado' => 'Presupuestado y enviado',
        'presupuesto_aceptado' => 'Presupuesto aceptado',
        'trabajo_programado' => 'Trabajo programado',
        'conformidad_cliente' => 'Conformidad del cliente',
        'servicio_pagado' => 'Servicio pagado/abonado',
        'factura_enviada' => 'Factura enviada',
    ];

    public const OTHER_STATUSES = [
        'cancelado' => 'Cancelado',
        'reprogramado' => 'Reprogramado',
    ];

    public const TIME_SLOTS = [
        'manana' => 'Mañana (8 a 12hs)',
        'tarde' => 'Tarde (12 a 17hs)',
        'tarde_noche' => 'Tarde-noche (17 a 20hs)',
    ];

    public const FLOW_TYPES = [
        'con_relevamiento' => 'Con relevamiento',
        'presupuesto_directo' => 'Presupuesto directo por foto',
    ];

    protected $fillable = [
        'customer_id',
        'property_id',
        'relevamiento_id',
        'flow_type',
        'category_id',
        'post_id',
        'work_date',
        'time_slot',
        'status',
        'price',
        'observations',
    ];

    protected $casts = [
        'work_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('budget_photos');
    }

    public static function allStatusOptions(): array
    {
        return [
            'Pipeline' => self::PIPELINE_STATUSES,
            'Otros' => self::OTHER_STATUSES,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function relevamiento(): BelongsTo
    {
        return $this->belongsTo(Relevamiento::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
