<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class RelevamientoWorkItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'relevamiento_id',
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

    public function relevamiento(): BelongsTo
    {
        return $this->belongsTo(Relevamiento::class);
    }
}
