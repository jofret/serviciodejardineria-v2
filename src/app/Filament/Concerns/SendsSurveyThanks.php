<?php

namespace App\Filament\Concerns;

use App\Mail\EncuestaPublicadaMailable;
use App\Models\Survey;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

/**
 * Compartido entre SurveyResource (acción manual "Agradecer por WhatsApp") y
 * EditSurvey (disparo automático al publicar) — misma lógica de "si tiene
 * email mandale mail + si tiene teléfono abrile WhatsApp", igual que
 * SendsBankAccountDetails para WorkOrder.
 */
trait SendsSurveyThanks
{
    use OpensWhatsAppInNewTab;

    protected static function sendSurveyThanks(Survey $record, $livewire, bool $includeEmail = true): void
    {
        $customer = $record->customer;

        $hasEmail = $includeEmail && filled($customer->email);
        $hasWhatsapp = filled($customer->phone);

        if (! $hasEmail && ! $hasWhatsapp) {
            $livewire->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('No se pudo enviar')
                ->body('El cliente no tiene email ni teléfono cargado.')
                ->danger()
                ->send();

            return;
        }

        if ($hasEmail) {
            Mail::to($customer->email)->send(new EncuestaPublicadaMailable($record));
        }

        if (! $hasWhatsapp) {
            $livewire->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('Encuesta publicada, aviso enviado por email')
                ->success()
                ->send();

            return;
        }

        $telefono = $customer->whatsappPhone();

        $mensaje = "¡Hola {$customer->name}! 🌿 Muchas gracias por tu comentario, ya está publicado en nuestra web. ";
        $mensaje .= 'Fue un placer haber trabajado con vos, y nos alegra mucho que hayas confiado en AltoParque. ';

        if ($record->post) {
            $mensaje .= "Podés verlo acá:\n".route('post.show', $record->post)."\n\n";
        }

        $mensaje .= '¡Cualquier cosa que necesites, contactanos! 🙌';

        // Se usa api.whatsapp.com/send directo en vez de wa.me: wa.me corrompe
        // emojis (4 bytes UTF-8) en su propio redirect hacia api.whatsapp.com.
        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text=".urlencode($mensaje).'&type=phone_number&app_absent=0';

        Notification::make()
            ->title($hasEmail ? 'Encuesta publicada, aviso enviado por email' : 'Mensaje listo')
            ->body('Se abrió WhatsApp con el mensaje listo para enviar.')
            ->success()
            ->send();

        $livewire->js(static::navigateWhatsAppTab($whatsappLink));
    }
}
