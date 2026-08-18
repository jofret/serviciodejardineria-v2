<?php

namespace App\Services\Altoparque;

/**
 * Representa el Customer tal como lo devuelve la API central de Altoparque.
 * Distinto de App\Models\Customer (que sigue existiendo local para el resto
 * del CRM del sitio) — este es solo el que usa Claudia para WhatsApp.
 */
class RemoteCustomer
{
    public function __construct(private readonly array $attributes)
    {
    }

    public function id(): int
    {
        return (int) $this->attributes['id'];
    }

    public function name(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function phone(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Solo viene presente en la respuesta de POST /api/customers
     * (CustomerApiController::store) — indica si el Customer se acaba de
     * crear o ya existía.
     */
    public function wasRecentlyCreated(): bool
    {
        return (bool) ($this->attributes['created'] ?? false);
    }

    public function tieneNombre(): bool
    {
        return filled($this->name()) && $this->name() !== 'Cliente de WhatsApp';
    }

    /**
     * Teléfono normalizado para WhatsApp Cloud API, con prefijo de país 54
     * (Argentina). Réplica de Customer::whatsappPhone() del modelo local.
     */
    public function whatsappPhone(): string
    {
        $telefono = preg_replace('/[^0-9]/', '', $this->phone() ?? '');

        if (substr($telefono, 0, 1) === '0') {
            $telefono = '54'.substr($telefono, 1);
        } elseif (substr($telefono, 0, 2) !== '54') {
            $telefono = '54'.$telefono;
        }

        return $telefono;
    }
}
