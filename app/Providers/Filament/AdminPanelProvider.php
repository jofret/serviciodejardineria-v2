<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Los badges del menú lateral (ver App\Filament\Concerns\HasPendingAttentionBadge)
            // se recalculan en cada render de Livewire. Sin este poll, solo se actualizarían
            // al navegar a otra página — este wire:poll hace que se refresquen solos mientras
            // el admin se queda en la misma pantalla, sin tener que recargar.
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<div wire:poll.15s style="display:none" aria-hidden="true"></div>',
            )
            // Formatea "Precio Estimativo" con punto de miles y coma decimal
            // (ej. "1.234.567,50") en el form de Presupuestos por foto — ver
            // PresupuestoPorFotoResource::estimatedPriceField(). Escopeado a
            // las páginas de ese recurso nada más (no es un hook de panel
            // entero) porque el bundle de Filament instalado acá no trae el
            // plugin de Alpine ($money) que el ->mask() nativo necesitaría.
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => request()->routeIs('filament.admin.resources.presupuestos-por-foto.*') ? <<<'HTML'
                    <script>
                        window.formatThousandsInput = function (event) {
                            var input = event.target;
                            var raw = input.value.replace(/[^\d,]/g, '');

                            var firstComma = raw.indexOf(',');
                            if (firstComma !== -1) {
                                raw = raw.slice(0, firstComma + 1) + raw.slice(firstComma + 1).replace(/,/g, '');
                            }

                            var parts = raw.split(',');
                            var intDigits = parts[0].replace(/^0+(?=\d)/, '');
                            var decDigits = parts.length > 1 ? parts[1].slice(0, 2) : null;

                            var formattedInt = intDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            var formatted = decDigits !== null ? (formattedInt + ',' + decDigits) : formattedInt;

                            if (input.value !== formatted) {
                                input.value = formatted;
                                input.dispatchEvent(new Event('input'));
                            }
                        };
                    </script>
                    HTML : '',
            );
    }
}
