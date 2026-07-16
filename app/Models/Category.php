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
                // Sin onUpdate: si el slug se regenerara cada vez que cambia el nombre,
                // corregir el nombre de una categoría ya publicada cambiaría su URL
                // indexada sin que nadie lo pida explícitamente. Igual que Post y Tag.
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
        return $value ?? $this->name . ' - Servicio de Jardinería';
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?? $this->description ?? "Servicio profesional de {$this->name} en zona norte y CABA.";
    }
}