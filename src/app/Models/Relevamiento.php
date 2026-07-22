<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Relevamiento extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'property_id',
        'category_id',
        'category_other',
        'assigned_to',
        'status',
        'scheduled_date',
        'scheduled_time_from',
        'scheduled_time_to',
        'submitted_at',
        'reopen_requested_at',
        'notes',
        'property_type',
        'requires_non_compete_clause',
        'estimated_price',
        'workers_count',
        'estimated_duration_days',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'submitted_at' => 'datetime',
        'reopen_requested_at' => 'datetime',
        'requires_non_compete_clause' => 'boolean',
        'estimated_price' => 'decimal:2',
        'workers_count' => 'integer',
        'estimated_duration_days' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Relevamiento $relevamiento) {
            if (! $relevamiento->property_type && $relevamiento->property_id) {
                $relevamiento->property_type = Property::find($relevamiento->property_id)?->property_type;
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function getPropertyTypeLabelAttribute(): ?string
    {
        if (! $this->property_type) {
            return null;
        }

        return Property::PROPERTY_TYPES[$this->property_type] ?? $this->property_type;
    }

    public function getServiceTypeLabelAttribute(): ?string
    {
        return $this->category?->name ?? $this->category_other;
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function relevador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function serviceOrder(): HasOne
    {
        return $this->hasOne(ServiceOrder::class);
    }

    public function workItems(): HasMany
    {
        return $this->hasMany(RelevamientoWorkItem::class)->orderBy('order')->orderBy('id');
    }

    public function workTools(): BelongsToMany
    {
        return $this->belongsToMany(WorkTool::class);
    }

    public function pruneEmptyWorkItems(): void
    {
        $this->workItems()
            ->whereDoesntHave('media')
            ->where(fn ($query) => $query->whereNull('description')->orWhere('description', ''))
            ->where(fn ($query) => $query->whereNull('observations')->orWhere('observations', ''))
            ->where('includes_pickup', false)
            ->delete();
    }

    public function markAsSubmitted(): void
    {
        $this->update(['submitted_at' => now()]);

        $serviceOrder = $this->serviceOrder()->first();

        if ($serviceOrder) {
            if ($serviceOrder->status === 'visita_programada') {
                $serviceOrder->update(['status' => 'visita_realizada']);
            }

            return;
        }

        ServiceOrder::create([
            'customer_id' => $this->property->customer_id,
            'property_id' => $this->property_id,
            'relevamiento_id' => $this->id,
            'flow_type' => 'con_relevamiento',
            'category_id' => $this->category_id,
            'status' => 'visita_realizada',
        ]);
    }

    public function requestReopen(): void
    {
        $this->update(['reopen_requested_at' => now()]);
    }

    public function approveReopen(): void
    {
        $this->update([
            'submitted_at' => null,
            'reopen_requested_at' => null,
        ]);
    }
}
