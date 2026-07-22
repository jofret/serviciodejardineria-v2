<?php

namespace App\Filament\Concerns;

use Closure;
use Filament\Forms\Components\TextInput;

/**
 * Formatea un TextInput numérico con punto de miles y coma decimal
 * mientras se escribe (ej. "1.234.567,50"). El formateo en sí ocurre en
 * el navegador (window.formatThousandsInput, registrado globalmente para
 * todo el panel en AdminPanelProvider) porque el bundle de Filament
 * instalado acá no trae el plugin de Alpine ($money) que el ->mask()
 * nativo necesita.
 */
trait FormatsThousandsInput
{
    protected static function thousandsFormattedTextInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->type('text')
            ->inputMode('decimal')
            ->extraInputAttributes(['x-on:input' => 'window.formatThousandsInput($event)'])
            ->afterStateHydrated(fn ($component, $state) => $component->state(static::formatThousandsDisplay($state)))
            ->dehydrateStateUsing(fn ($state) => static::parseThousandsInput($state))
            ->rule(static fn (): Closure => function (string $attribute, $value, Closure $fail) {
                if (filled($value) && ! is_numeric(static::parseThousandsInput($value))) {
                    $fail('Este campo debe ser un valor numérico.');
                }
            });
    }

    /**
     * Convierte el valor crudo (numérico, o ya formateado si el usuario
     * está editando) a la representación con punto de miles y coma
     * decimal que se muestra en el campo (ej. "1.234.567,50").
     */
    protected static function formatThousandsDisplay($state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $normalized = static::parseThousandsInput($state);

        if (! is_numeric($normalized)) {
            return (string) $state;
        }

        [$intPart, $decPart] = array_pad(explode('.', $normalized, 2), 2, null);

        $formattedInt = number_format((float) $intPart, 0, '', '.');

        return $decPart !== null ? $formattedInt.','.$decPart : $formattedInt;
    }

    /**
     * Inversa de formatThousandsDisplay(): saca los puntos de miles y
     * convierte la coma decimal en punto, para validar y guardar el
     * valor numérico real.
     */
    protected static function parseThousandsInput($state): ?string
    {
        if (blank($state)) {
            return null;
        }

        return str_replace(['.', ','], ['', '.'], (string) $state);
    }
}
