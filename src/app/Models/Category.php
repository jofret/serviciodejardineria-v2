<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class Category extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'description',
        'meta_title',
        'meta_description',
        'order',
        'is_active'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true // 👈 ESTA LÍNEA ES LA CLAVE
            ]
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?? $this->name . ' - Limpieza de Terrenos';
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?? $this->description ?? "Trabajos de {$this->name} profesionales en zona norte.";
    }
}