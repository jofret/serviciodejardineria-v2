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
     * 'name' es NOT NULL en la tabla — cuando Claudia crea el Customer y
     * WhatsApp no mandó el nombre de perfil del contacto, se usa este
     * placeholder hasta que el cliente lo diga en la conversación.
     */
    public const NOMBRE_PENDIENTE = 'Cliente de WhatsApp';

    public function tieneNombre(): bool
    {
        return filled($this->name) && $this->name !== self::NOMBRE_PENDIENTE;
    }

    /**
     * Busca un cliente por su número de WhatsApp (wa_id de Meta, ej.
     * "5491122334455"), comparando solo los últimos 10 dígitos para que
     * coincida sin importar con qué formato se haya cargado el teléfono
     * (con o sin 0, con o sin el 54/549 de país).
     */
    public static function findByWhatsappNumber(string $waId): ?self
    {
        $sufijo = substr(preg_replace('/[^0-9]/', '', $waId), -10);

        return static::query()
            ->whereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 10) = ?", [$sufijo])
            ->first();
    }

    /**
     * Teléfono normalizado para enlaces de WhatsApp (api.whatsapp.com/send),
     * con prefijo de país 54 (Argentina).
     */
    public function whatsappPhone(): string
    {
        $telefono = preg_replace('/[^0-9]/', '', $this->phone ?? '');

        if (substr($telefono, 0, 1) === '0') {
            $telefono = '54'.substr($telefono, 1);
        } elseif (substr($telefono, 0, 2) !== '54') {
            $telefono = '54'.$telefono;
        }

        return $telefono;
    }

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
     * Relación con conversaciones de WhatsApp (Claudia)
     */
    public function whatsappConversations()
    {
        return $this->hasMany(WhatsappConversation::class);
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
     * true si el cliente tiene al menos una Orden de Trabajo completada —
     * requisito para habilitar el botón de "Encuesta WhatsApp".
     */
    public function hasCompletedWorkOrder(): bool
    {
        return $this->serviceOrders()
            ->whereHas('workOrder', fn ($query) => $query->where('status', 'completado'))
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
