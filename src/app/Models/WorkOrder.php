<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WorkOrder extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const PIPELINE_STATUSES = [
        'nueva' => 'Nueva',
        'programado' => 'Programado',
        'en_curso' => 'En curso',
        'completado' => 'Completado',
    ];

    public const OTHER_STATUSES = [
        'cancelado' => 'Cancelado',
        'reprogramado' => 'Reprogramado',
    ];

    protected $fillable = [
        'service_order_id',
        'work_date',
        'time_slot',
        'status',
        'conformity_token',
        'conformity_sent_at',
        'conformity_confirmed_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'conformity_sent_at' => 'datetime',
        'conformity_confirmed_at' => 'datetime',
    ];

    public static function allStatusOptions(): array
    {
        return [
            'Pipeline' => self::PIPELINE_STATUSES,
            'Otros' => self::OTHER_STATUSES,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('before_photos');
        $this->addMediaCollection('after_photos');
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Mismo código base que el número de presupuesto de la Orden de
     * Servicio vinculada (ver ServiceOrder::documentNumberBase()), con
     * prefijo "OT" en vez de "00" — para diferenciar visualmente ambos
     * documentos de una misma operación. Se mantienen los dos ceros
     * después del prefijo (ej. "OT00...") igual que en budget_number.
     */
    public function getWorkOrderNumberAttribute(): string
    {
        return 'OT00'.$this->serviceOrder->documentNumberBase();
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(WorkOrderChecklistItem::class)->orderBy('order')->orderBy('id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(Worker::class);
    }

    /**
     * Genera (o reutiliza) el token del enlace público de conformidad —
     * mismo mecanismo que ServiceOrder::generateBudgetToken().
     */
    public function generateConformityToken(): string
    {
        $token = $this->conformity_token ?: md5($this->id.time().rand(1000, 9999));

        $this->update([
            'conformity_token' => $token,
            'conformity_sent_at' => now(),
        ]);

        return $token;
    }

    /**
     * Marca la conformidad como confirmada por el cliente desde la página
     * pública, y avanza la Orden de Servicio original. Idempotente y no
     * retrocede el status si el admin ya lo movió más adelante a mano —
     * mismo criterio que ServiceOrder::acceptBudget().
     */
    public function confirmConformity(): void
    {
        if ($this->conformity_confirmed_at !== null) {
            return;
        }

        $this->update([
            'conformity_confirmed_at' => now(),
            'status' => 'completado',
        ]);

        $order = $this->serviceOrder;

        $order->update([
            'status' => $order->status === 'trabajo_programado' ? 'conformidad_cliente' : $order->status,
        ]);
    }
}
