<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'zone',
        'birthday',
        'customer_type',
        'status',
        'lead_status',
        'preferred_contact',
        'notes',
        'metadata',
        // Campos del formulario de contacto
        'zona_principal',
        'partido',
        'otra_zona',
        'servicio_interes',
        'mensaje_inicial',
        'fuente',
    ];

    protected $casts = [
        'birthday' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Relación con propiedades
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Relación con encuestas
     */
    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Relación con órdenes de servicio
     */
    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }

    /**
     * Indica si corresponde ofrecer el botón de "Encuesta WhatsApp":
     * false si ya hay una encuesta respondida o publicada para este cliente.
     */
    public function canRequestTestimonial(): bool
    {
        return ! $this->surveys()
            ->where(function ($query) {
                $query->whereNotNull('answered_at')->orWhere('is_published', true);
            })
            ->exists();
    }

    /**
     * Estado del testimonio más reciente del cliente, para mostrar en el admin.
     */
    public function testimonialStatusLabel(): string
    {
        $survey = $this->surveys()->latest()->first();

        if (! $survey) {
            return 'No enviado';
        }

        if ($survey->is_published) {
            return 'Publicado';
        }

        if ($survey->answered_at) {
            return 'Completado';
        }

        return 'Enlace enviado';
    }

    /**
     * Relación con posts a través de propiedades
     */
    public function posts()
    {
        return $this->hasManyThrough(Post::class, Property::class);
    }

    /**
     * Accesor para cumpleaños formateado
     */
    public function getBirthdayAttribute()
    {
        if ($this->birthday_month && $this->birthday_day) {
            return $this->birthday_day.' de '.$this->birthday_month;
        }

        return null;
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'activo');
    }

    /**
     * Scope para clientes potenciales (leads)
     */
    public function scopePotential($query)
    {
        return $query->where('status', 'potencial');
    }

    /**
     * Scope para filtrar por zona
     */
    public function scopeByZone($query, $zone)
    {
        return $query->where('zona_principal', $zone)
            ->orWhere('otra_zona', 'LIKE', "%{$zone}%");
    }

    /**
     * Obtener zona completa formateada
     */
    public function getFullZoneAttribute()
    {
        if ($this->zona_principal === 'Otra') {
            return $this->otra_zona;
        }

        if ($this->partido) {
            return $this->zona_principal.' - '.$this->partido;
        }

        return $this->zona_principal;
    }
}
