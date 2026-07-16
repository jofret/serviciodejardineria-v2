<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class Tag extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'description'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?? $this->name . ' - Tag | AltoParque';
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?? "Trabajos de jardinería etiquetados con {$this->name}.";
    }
}