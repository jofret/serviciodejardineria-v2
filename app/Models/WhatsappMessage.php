<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    use HasFactory;

    public const REMITENTE_CLIENTE = 'cliente';

    public const REMITENTE_CLAUDIA = 'claudia';

    public const TIPOS = [
        'texto' => 'Texto',
        'imagen' => 'Imagen',
    ];

    protected $fillable = [
        'whatsapp_conversation_id',
        'wamid',
        'remitente',
        'contenido',
        'tipo',
        'enviado_en',
    ];

    protected $casts = [
        'enviado_en' => 'datetime',
    ];

    /**
     * Al crear un mensaje se actualiza updated_at de la conversación, así
     * el listado del panel puede ordenar por "última actividad".
     */
    protected $touches = ['conversation'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'whatsapp_conversation_id');
    }
}
