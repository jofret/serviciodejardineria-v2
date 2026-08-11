<?php

namespace App\Filament\Concerns;

use App\Models\Relevamiento;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

trait SendsRelevamientoToRelevador
{
    /**
     * Marca el relevamiento como enviado y abre WhatsApp con el aviso listo
     * para el relevador asignado. $livewire es el componente (Page o tabla)
     * desde donde se dispara la acción — necesario para $livewire->js().
     */
    public static function sendRelevamientoToRelevador(Relevamiento $record, $livewire): void
    {
        $relevador = $record->relevador;

        if (! $relevador || ! filled($relevador->whatsapp)) {
            $livewire->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('No se pudo enviar')
                ->body('El relevador asignado no tiene WhatsApp cargado.')
                ->danger()
                ->send();

            return;
        }

        $record->update(['status' => 'enviado_a_relevador']);

        $telefono = $relevador->whatsappPhone();
        $enlace = route('relevador.dashboard');

        $horario = $record->scheduled_time_from
            ? Carbon::parse($record->scheduled_time_from)->format('H:i').($record->scheduled_time_to ? ' a '.Carbon::parse($record->scheduled_time_to)->format('H:i') : '')
            : null;

        $mensaje = "Hola {$relevador->name}! 👋\n\n";
        $mensaje .= "Te asignamos un nuevo relevamiento en *AltoParque*:\n\n";
        $mensaje .= "📍 {$record->property->display_label}\n";
        $mensaje .= "🔧 {$record->service_type_label}\n";
        if ($record->scheduled_date) {
            $mensaje .= '🗓 '.Carbon::parse($record->scheduled_date)->format('d/m/Y').($horario ? " ({$horario})" : '')."\n";
        }
        $mensaje .= "\nAccedé para completarlo:\n{$enlace}";

        $mensajeCodificado = urlencode($mensaje);

        // Se usa api.whatsapp.com/send directo en vez de wa.me: wa.me corrompe
        // emojis (4 bytes UTF-8) en su propio redirect hacia api.whatsapp.com.
        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text={$mensajeCodificado}&type=phone_number&app_absent=0";

        Notification::make()
            ->title('Relevamiento enviado a relevador')
            ->body('Se abrió WhatsApp con el mensaje listo para enviar.')
            ->success()
            ->send();

        $livewire->js(static::navigateWhatsAppTab($whatsappLink));
    }

    /**
     * Aprueba la reapertura y abre WhatsApp con el aviso listo para el
     * relevador asignado, igual que sendRelevamientoToRelevador().
     */
    public static function sendReopenApprovedToRelevador(Relevamiento $record, $livewire): void
    {
        $relevador = $record->relevador;

        if (! $relevador || ! filled($relevador->whatsapp)) {
            $record->approveReopen();
            $livewire->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('Reapertura aprobada')
                ->body('El relevador asignado no tiene WhatsApp cargado, no se le pudo avisar.')
                ->warning()
                ->send();

            return;
        }

        $record->approveReopen();

        $telefono = $relevador->whatsappPhone();
        $enlace = route('relevador.dashboard');

        $mensaje = "Hola {$relevador->name}! 👋\n\n";
        $mensaje .= "Se aprobó la reapertura de un relevamiento en *AltoParque*:\n\n";
        $mensaje .= "📍 {$record->property->display_label}\n";
        $mensaje .= "🔧 {$record->service_type_label}\n";
        $mensaje .= "\nYa podés volver a editarlo:\n{$enlace}";

        $mensajeCodificado = urlencode($mensaje);

        // Se usa api.whatsapp.com/send directo en vez de wa.me: wa.me corrompe
        // emojis (4 bytes UTF-8) en su propio redirect hacia api.whatsapp.com.
        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text={$mensajeCodificado}&type=phone_number&app_absent=0";

        Notification::make()
            ->title('Reapertura aprobada')
            ->body('Se abrió WhatsApp con el aviso listo para enviar.')
            ->success()
            ->send();

        $livewire->js(static::navigateWhatsAppTab($whatsappLink));
    }
}
