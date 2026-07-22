<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Concerns\SendsBankAccountDetails;
use App\Filament\Resources\WorkOrderResource;
use App\Mail\WorkOrderConformityRequestMailable;
use App\Models\WorkOrder;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditWorkOrder extends EditRecord
{
    use SendsBankAccountDetails;

    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('conformity_confirmed')
                ->label('Conformidad Confirmada')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->disabled()
                ->visible(fn (WorkOrder $record): bool => $record->conformity_confirmed_at !== null),

            Actions\Action::make('sendConformityRequest')
                ->label('Solicitar conformidad del cliente')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->visible(fn (WorkOrder $record): bool => in_array($record->status, ['en_curso', 'completado']) && $record->conformity_confirmed_at === null)
                ->requiresConfirmation()
                ->modalHeading('Solicitar conformidad')
                ->modalDescription(fn (WorkOrder $record): string => $this->conformityRequestDescription($record))
                ->modalSubmitActionLabel('Enviar ahora')
                ->modalSubmitAction(fn ($action) => $action->extraAttributes(static::whatsAppTriggerAttributes()))
                ->action(fn (WorkOrder $record) => $this->sendConformityRequest($record)),

            static::configureBankAccountDetailsAction(Actions\Action::make('sendBankAccountDetails')),

            Actions\DeleteAction::make(),
        ];
    }

    private function conformityRequestDescription(WorkOrder $record): string
    {
        $customer = $record->serviceOrder->customer;

        $canales = array_filter([
            filled($customer->email) ? 'email' : null,
            filled($customer->phone) ? 'WhatsApp' : null,
        ]);

        if ($canales === []) {
            return 'El cliente no tiene email ni teléfono cargado — no hay forma de pedirle la conformidad.';
        }

        return 'Se envía por '.implode(' y ', $canales).
            '. El cliente va a poder ver el detalle del trabajo y confirmar que quedó bien.';
    }

    public function sendConformityRequest(WorkOrder $record)
    {
        $customer = $record->serviceOrder->customer;

        $token = $record->generateConformityToken();
        $enlace = url('/conformidad/'.$token);

        $hasEmail = filled($customer->email);
        $hasWhatsapp = filled($customer->phone);

        if (! $hasEmail && ! $hasWhatsapp) {
            $this->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('No se pudo enviar')
                ->body('El cliente no tiene email ni teléfono cargado.')
                ->danger()
                ->send();

            return null;
        }

        if ($hasEmail) {
            Mail::to($customer->email)
                ->send(new WorkOrderConformityRequestMailable($record, $enlace));
        }

        if (! $hasWhatsapp) {
            $this->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('Conformidad solicitada por email')
                ->success()
                ->send();

            return null;
        }

        $telefono = $customer->whatsappPhone();

        $mensaje = "Hola {$customer->name}! 👋\n\n";
        $mensaje .= "Terminamos el trabajo en tu propiedad, *AltoParque*.\n\n";
        $mensaje .= "📋 Confirmá que quedó todo bien acá:\n";
        $mensaje .= $enlace."\n\n";
        $mensaje .= '¡Gracias por confiar en nosotros! 🌿';

        $mensajeCodificado = urlencode($mensaje);

        // Se usa api.whatsapp.com/send directo en vez de wa.me: wa.me corrompe
        // emojis (4 bytes UTF-8) en su propio redirect hacia api.whatsapp.com.
        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text={$mensajeCodificado}&type=phone_number&app_absent=0";

        Notification::make()
            ->title($hasEmail ? 'Conformidad solicitada por email' : 'Solicitud guardada')
            ->body('Se abrió WhatsApp con el mensaje listo para enviar.')
            ->success()
            ->send();

        $this->js(static::navigateWhatsAppTab($whatsappLink));

        return null;
    }
}
