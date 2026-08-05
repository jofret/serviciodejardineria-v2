<?php

namespace App\Models;

use App\Mail\WhatsappConversationDerivedMailable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

class WhatsappConversation extends Model
{
    use HasFactory;

    public const ESTADOS = [
        'claudia_atendiendo' => 'Claudia atendiendo',
        'esperando_agenda_visita' => 'Esperando agenda de visita',
        'esperando_cotizacion_foto' => 'Esperando cotización por foto',
        'con_humano' => 'Con humano',
        'cerrada' => 'Cerrada',
    ];

    /**
     * Casilla que recibe las notificaciones de contacto nuevo en este sitio.
     */
    private const ADMIN_EMAILS = [
        'geral4bebes@gmail.com',
        'jofretjofret@gmail.com',
    ];

    protected $fillable = [
        'customer_id',
        'sitio_origen',
        'zona',
        'servicio_solicitado',
        'foto_path',
        'estado_conversacion',
        'asignado_a',
    ];

    /**
     * Motivo de la derivación a humano, para el email de aviso. Es transitorio
     * (no se persiste): lo setea quien llama a derivarAHumano() y lo lee el
     * hook de abajo en el mismo request.
     */
    public ?string $motivoDerivacion = null;

    /**
     * Avisa al equipo por email apenas un caso queda "con_humano" sin nadie
     * asignado todavía — cubre tanto la derivación de Claudia como un humano
     * que toma una conversación sola desde el panel (ahí asignado_a ya viene
     * seteado en el mismo update y no se duplica el aviso).
     */
    protected static function booted(): void
    {
        static::updated(function (WhatsappConversation $conversation): void {
            if (
                $conversation->wasChanged('estado_conversacion')
                && $conversation->estado_conversacion === 'con_humano'
                && blank($conversation->asignado_a)
            ) {
                Mail::to(self::ADMIN_EMAILS)->send(new WhatsappConversationDerivedMailable(
                    $conversation,
                    $conversation->motivoDerivacion ?? 'Conversación derivada a un humano.',
                ));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class)->orderBy('enviado_en');
    }

    public function estaConHumano(): bool
    {
        return $this->estado_conversacion === 'con_humano';
    }

    /**
     * Deriva la conversación a un humano: apenas pasa a "con_humano", Claudia
     * deja de responder automáticamente (la pausa la aplica quien consuma
     * este estado, no el modelo). $motivo es opcional y solo se usa para el
     * email de aviso (ver booted()).
     */
    public function derivarAHumano(?int $userId = null, ?string $motivo = null): void
    {
        $this->motivoDerivacion = $motivo;

        $this->update([
            'estado_conversacion' => 'con_humano',
            'asignado_a' => $userId ?? $this->asignado_a,
        ]);
    }

    public function cerrar(): void
    {
        $this->update(['estado_conversacion' => 'cerrada']);
    }

    /**
     * Reabre el caso para que Claudia vuelva a responder, solo por acción
     * explícita del humano (ver reglas de negocio de la spec).
     */
    public function reabrir(): void
    {
        $this->update(['estado_conversacion' => 'claudia_atendiendo']);
    }
}
