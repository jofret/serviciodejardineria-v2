<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relevamiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'assigned_to',
        'status',
        'scheduled_date',
        'submitted_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function relevador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
