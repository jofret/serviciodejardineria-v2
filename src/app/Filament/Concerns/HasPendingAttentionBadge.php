<?php

namespace App\Filament\Concerns;

/**
 * Badge de menú lateral para señalar que un Resource tiene algo pendiente
 * que requiere atención del admin (mismo estilo que SurveyResource ya usa).
 *
 * Para sumar un caso nuevo: agregar el trait al Resource y definir
 * pendingAttentionCount() (y opcionalmente pendingAttentionTooltip()).
 * El badge se oculta solo cuando el conteo da 0.
 */
trait HasPendingAttentionBadge
{
    protected static function pendingAttentionCount(): int
    {
        return 0;
    }

    protected static function pendingAttentionTooltip(): ?string
    {
        return null;
    }

    protected static function pendingAttentionColor(): string
    {
        return 'warning';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::pendingAttentionCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::pendingAttentionTooltip();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::pendingAttentionColor();
    }
}
