<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderChecklistItem extends Model
{
    protected $fillable = [
        'work_order_id',
        'description',
        'observations',
        'includes_pickup',
        'completed_at',
        'is_completed',
        'order',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'includes_pickup' => 'boolean',
    ];

    protected $appends = [
        'is_completed',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    public function setIsCompletedAttribute(bool $value): void
    {
        $this->completed_at = $value ? ($this->completed_at ?? now()) : null;
    }
}
