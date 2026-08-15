<?php

namespace App\Services\Altoparque;

use Illuminate\Support\Carbon;

/**
 * Representa la WhatsappConversation tal como la devuelve la API central de
 * Altoparque. El sitio_origen queda fijado por el token del lado central —
 * acá es de solo lectura.
 */
class RemoteConversation
{
    public function __construct(private readonly array $attributes)
    {
    }

    public function id(): int
    {
        return (int) $this->attributes['id'];
    }

    public function customerId(): int
    {
        return (int) $this->attributes['customer_id'];
    }

    public function sitioOrigen(): ?string
    {
        return $this->attributes['sitio_origen'] ?? null;
    }

    public function zona(): ?string
    {
        return $this->attributes['zona'] ?? null;
    }

    public function servicioSolicitado(): ?string
    {
        return $this->attributes['servicio_solicitado'] ?? null;
    }

    public function estadoConversacion(): string
    {
        return $this->attributes['estado_conversacion'] ?? 'claudia_atendiendo';
    }

    public function asignadoA(): ?int
    {
        return $this->attributes['asignado_a'] ?? null;
    }

    public function fotoPath(): ?string
    {
        return $this->attributes['foto_path'] ?? null;
    }

    public function updatedAt(): ?Carbon
    {
        return isset($this->attributes['updated_at']) ? Carbon::parse($this->attributes['updated_at']) : null;
    }

    /**
     * Solo viene cargado cuando el endpoint lo embebe (index/show), no en
     * store/update/latest.
     */
    public function customer(): ?RemoteCustomer
    {
        return isset($this->attributes['customer']) ? new RemoteCustomer($this->attributes['customer']) : null;
    }

    public function estaConHumano(): bool
    {
        return $this->estadoConversacion() === 'con_humano';
    }

    public function estaCerrada(): bool
    {
        return $this->estadoConversacion() === 'cerrada';
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
