<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public const PAYMENT_METHODS = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
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
        'final_price',
        'final_price_notes',
        'budget_token',
        'budget_sent_at',
        'budget_accepted_at',
        'payment_method_preference',
    ];

    protected $casts = [
        'work_date' => 'date',
        'price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'budget_sent_at' => 'datetime',
        'budget_accepted_at' => 'datetime',
        'payment_method_preference' => 'array',
    ];

    /**
     * generateWorkOrder() se dispara acá, en vez de solo dentro de
     * acceptBudget(), para que la Orden de Trabajo se genere sin importar
     * por qué camino el status llega a "presupuesto_aceptado" — incluye
     * cambiarlo a mano desde el form de edición en el admin, no solo el
     * flujo de aceptación del cliente.
     */
    protected static function booted(): void
    {
        static::saved(function (ServiceOrder $serviceOrder) {
            if ($serviceOrder->wasChanged('status') && $serviceOrder->status === 'presupuesto_aceptado') {
                $serviceOrder->generateWorkOrder();
            }
        });
    }

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

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    /**
     * Ítems del flujo "presupuesto directo por foto" (sin relevamiento) —
     * ver ServiceOrderItem.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class)->orderBy('order')->orderBy('id');
    }

    public function getBudgetNumberAttribute(): string
    {
        return '00'.$this->documentNumberBase();
    }

    /**
     * Base numérica compartida entre el número de presupuesto (budget_number,
     * prefijo "00") y el de la Orden de Trabajo (work_order_number, prefijo
     * "OT") — mismo código para ambos documentos de una misma operación,
     * solo cambia el prefijo.
     *
     * Usa el año de creación (no el año actual): el número de un documento
     * ya emitido no puede cambiar solo porque cambió el año calendario en
     * el que se lo está mirando.
     */
    public function documentNumberBase(): string
    {
        return $this->customer_id.$this->created_at->year.($this->relevamiento_id ?? 0).$this->id;
    }

    /**
     * Búsqueda por budget_number / work_order_number desde las tablas del
     * admin — ambos números se arman con la misma fórmula de
     * documentNumberBase(), replicada acá en SQL porque son atributos
     * calculados (no columnas reales de la tabla). Se pelan los prefijos
     * "OT" y "00" del término buscado para que funcione sin importar cuál
     * de los dos números copió el admin.
     */
    public static function scopeWhereDocumentNumberLike($query, string $search)
    {
        $normalized = preg_replace('/^00/', '', preg_replace('/^OT/i', '', trim($search)));

        return $query->whereRaw(
            'CONCAT(customer_id, YEAR(created_at), COALESCE(relevamiento_id, 0), id) LIKE ?',
            ['%'.$normalized.'%']
        );
    }

    /**
     * Habilita la pantalla "Revisar y presupuestar": con relevamiento,
     * necesita que el relevador ya lo haya enviado (submitted_at seteado).
     * Con presupuesto directo por foto no hay relevamiento — la orden ya
     * nace con lo que hace falta para presupuestar (fotos y precio de
     * referencia cargados al crearla).
     */
    public function canReviewAndQuote(): bool
    {
        if ($this->flow_type === 'presupuesto_directo') {
            return true;
        }

        return $this->relevamiento_id !== null && $this->relevamiento?->submitted_at !== null;
    }

    /**
     * Genera (o reutiliza) el token del enlace público de presupuesto y marca
     * la orden como presupuestada y enviada. Reutilizar el token en reenvíos
     * evita invalidar un enlace que el cliente ya pueda tener guardado.
     */
    public function generateBudgetToken(): string
    {
        $token = $this->budget_token ?: md5($this->id.time().rand(1000, 9999));

        $this->update([
            'budget_token' => $token,
            'budget_sent_at' => now(),
            'status' => 'presupuestado_enviado',
        ]);

        return $token;
    }

    /**
     * Marca el presupuesto como aceptado por el cliente desde la página
     * pública. Idempotente (el cliente puede tocar el botón más de una vez)
     * y no retrocede el status si el admin ya lo movió manualmente más
     * adelante en el pipeline.
     */
    public function acceptBudget(array $paymentMethodPreference = []): void
    {
        if ($this->budget_accepted_at !== null) {
            return;
        }

        $this->update([
            'budget_accepted_at' => now(),
            'payment_method_preference' => array_values(array_intersect($paymentMethodPreference, array_keys(self::PAYMENT_METHODS))),
            'status' => $this->status === 'presupuestado_enviado' ? 'presupuesto_aceptado' : $this->status,
        ]);
    }

    /**
     * Genera la Orden de Trabajo la primera vez que se acepta el
     * presupuesto, con su checklist heredado de los ítems del Relevamiento
     * (con relevamiento) o de los ítems propios (presupuesto directo por
     * foto) — mismo esquema en los dos casos (description, observations,
     * includes_pickup), así el checklist queda igual sin importar el
     * origen. Idempotente — mismo criterio que Relevamiento::markAsSubmitted()
     * al generar la Orden de Servicio.
     */
    public function generateWorkOrder(): void
    {
        if ($this->workOrder()->exists()) {
            return;
        }

        $workOrder = $this->workOrder()->create(['status' => 'nueva']);

        $items = $this->relevamiento ? $this->relevamiento->workItems : $this->items;

        foreach ($items as $index => $item) {
            $workOrder->checklistItems()->create([
                'description' => $item->description,
                'observations' => $item->observations,
                'includes_pickup' => $item->includes_pickup,
                'order' => $index,
            ]);
        }
    }
}
