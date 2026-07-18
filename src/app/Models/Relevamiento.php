<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'notes',
        'property_type',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'submitted_at' => 'datetime',
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

    public function markAsSubmitted(): void
    {
        $this->update(['submitted_at' => now()]);

        if ($this->serviceOrder && $this->serviceOrder->status === 'visita_programada') {
            $this->serviceOrder->update(['status' => 'visita_realizada']);
        }
    }
}
