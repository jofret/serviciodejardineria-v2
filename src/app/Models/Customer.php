<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
    'name', 'phone', 'email', 'address', 'zone', 'birthday',
    'customer_type', 'status', 'preferred_contact', 'notes', 'metadata',
    // Nuevos campos
    'zona_principal', 'partido', 'otra_zona', 'servicio_interes',
    'mensaje_inicial', 'fuente'
    ];

    protected $casts = [
        'birthday' => 'date',
        'metadata' => 'array',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}