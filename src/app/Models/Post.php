<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model implements HasMedia
{
    use HasFactory, Sluggable, InteractsWithMedia;

    protected $table = 'posts';

    protected $fillable = [
        'category_id',
        'title',
        'subtitle',
        'excerpt',
        'content',
        'featured_image',
        'gallery_images',
        'location',
        'client_name',
        'project_size',
        'project_duration',
        'machinery_used',
        'has_before_after',
        'video_url',
        'is_featured',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'has_before_after' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Configuración de slugs automáticos
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * Relación con categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con tags (muchos a muchos)
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Relación con propiedades (muchos a muchos, inversa de Property::posts())
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_post')
                    ->withPivot('relation_type', 'comment', 'rating', 'service_date')
                    ->withTimestamps();
    }

    /**
     * Colecciones de imágenes para Spatie Media Library
     */
    public function registerMediaCollections(): void
    {
        // Imagen destacada (una sola)
        $this->addMediaCollection('featured')
            ->singleFile();

        // Galería de imágenes (múltiples, para antes/después)
        $this->addMediaCollection('gallery');

        // Imágenes de "antes" (opcional)
        $this->addMediaCollection('before')
            ->singleFile();

        // Imágenes de "después" (opcional)
        $this->addMediaCollection('after')
            ->singleFile();
    }

    /**
     * Conversiones globales para todas las imágenes del modelo
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(368)
             ->height(232)
             ->sharpen(10)
             ->nonQueued();

        $this->addMediaConversion('og-image')
             ->width(1200)
             ->height(630)
             ->sharpen(5)
             ->nonQueued();

        $this->addMediaConversion('webp')
             ->format('webp')
             ->quality(80)
             ->nonQueued();
    }

    /**
     * Para usar slug en las rutas en lugar de id
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Scope para posts publicados
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope para posts destacados
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para filtrar por ubicación
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    /**
     * Accesor para fecha formateada
     */
    public function getFormattedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('d/m/Y') : null;
    }

    /**
     * Accesor para meta título (SEO)
     */
    public function getMetaTitleAttribute($value)
    {
        return $value ?? $this->title . ' - Limpieza de Terrenos';
    }

    /**
     * Accesor para meta descripción (SEO)
     */
    public function getMetaDescriptionAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->excerpt) {
            return $this->excerpt;
        }

        return "Trabajo de limpieza en {$this->location}. Ver fotos antes/después y solicitar presupuesto.";
    }

    /**
     * Verificar si tiene imágenes antes/después
     */
    public function getHasBeforeAfterMediaAttribute(): bool
    {
        return $this->getMedia('before')->isNotEmpty() &&
               $this->getMedia('after')->isNotEmpty();
    }

    /**
     * Obtener URL de la imagen destacada (versión optimizada)
     */
    public function getFeaturedImageUrlAttribute()
    {
        $media = $this->getFirstMedia('featured');
        if ($media) {
            // Priorizar WebP si existe
            return $media->hasGeneratedConversion('webp')
                ? $media->getUrl('webp')
                : $media->getUrl();
        }
        return asset('images/default-post.jpg');
    }

    /**
     * Obtener URL del thumbnail de la imagen destacada
     */
    public function getFeaturedThumbUrlAttribute()
    {
        $media = $this->getFirstMedia('featured');
        if ($media && $media->hasGeneratedConversion('thumb')) {
            return $media->getUrl('thumb');
        }
        return asset('images/default-thumb.jpg');
    }

    /**
     * Obtener todas las imágenes de la galería
     */
    public function getGalleryMediaAttribute()
    {
        return $this->getMedia('gallery');
    }

    /**
     * Obtener imagen del "antes"
     */
    public function getBeforeMediaAttribute()
    {
        return $this->getFirstMedia('before');
    }

    /**
     * Obtener imagen del "después"
     */
    public function getAfterMediaAttribute()
    {
        return $this->getFirstMedia('after');
    }
}