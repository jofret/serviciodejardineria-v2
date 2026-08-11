<?php

namespace App\Providers;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El resto de la UI de Filament se traduce a mano vía ->label() en
        // cada campo/columna (APP_LOCALE queda en "en", no hay archivos de
        // idioma "es" publicados) — pero "Collapse all"/"Expand all" del
        // Repeater son textos fijos del paquete, no hay forma de pisarlos
        // campo por campo sin repetir esto en cada ->collapsible(). Se
        // traducen acá una sola vez para que apliquen a todos los repeaters
        // colapsables de la app.
        Repeater::configureUsing(function (Repeater $repeater): void {
            $repeater
                ->collapseAllAction(fn (Action $action) => $action->label('Colapsar todo'))
                ->expandAllAction(fn (Action $action) => $action->label('Expandir todo'));
        });
    }
}
