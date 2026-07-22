<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Concerns\OpensWhatsAppInNewTab;
use App\Filament\Resources\WorkOrderResource;
use App\Mail\BankAccountDetailsMailable;
use App\Mail\WorkOrderConformityRequestMailable;
use App\Models\BankAccount;
use App\Models\WorkOrder;
use Filament\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditWorkOrder extends EditRecord
{
    use OpensWhatsAppInNewTab;

    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('work_order_number')
                ->label(fn (WorkOrder $record): string => 'N° '.$record->work_order_number)
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->disabled(),

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

            Actions\Action::make('sendBankAccountDetails')
                ->label('Enviar datos bancarios')
                ->icon('heroicon-o-building-library')
                ->color('success')
                ->visible(fn (WorkOrder $record): bool => in_array($record->status, ['programado', 'en_curso', 'completado'])
                    && in_array('transferencia', $record->serviceOrder->payment_method_preference ?? []))
                ->form([
                    CheckboxList::make('bank_account_ids')
                        ->label('Cuentas a enviar')
                        ->options(fn (): array => BankAccount::orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): array => BankAccount::orderBy('name')->pluck('id')->all())
                        ->required()
                        ->columns(1),
                ])
                ->modalHeading('Enviar datos bancarios')
                ->modalDescription(fn (WorkOrder $record): string => $this->bankAccountDetailsDescription($record))
                ->modalSubmitActionLabel('Enviar ahora')
                ->modalSubmitAction(fn ($action) => $action->extraAttributes(static::whatsAppTriggerAttributes()))
                ->action(fn (WorkOrder $record, array $data) => $this->sendBankAccountDetails($record, $data['bank_account_ids'])),

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

    private function bankAccountDetailsDescription(WorkOrder $record): string
    {
        if (BankAccount::count() === 0) {
            return 'No hay ninguna cuenta bancaria cargada en el sistema — cargá una en "Cuentas Bancarias" antes de enviar.';
        }

        $customer = $record->serviceOrder->customer;

        $canales = array_filter([
            filled($customer->email) ? 'email' : null,
            filled($customer->phone) ? 'WhatsApp' : null,
        ]);

        if ($canales === []) {
            return 'El cliente no tiene email ni teléfono cargado — no hay forma de enviarle los datos.';
        }

        return 'Se envían los datos de las cuentas seleccionadas, por '.implode(' y ', $canales).'.';
    }

    public function sendBankAccountDetails(WorkOrder $record, array $bankAccountIds)
    {
        $customer = $record->serviceOrder->customer;

        $bankAccounts = BankAccount::whereIn('id', $bankAccountIds)->orderBy('name')->get();

        if ($bankAccounts->isEmpty()) {
            $this->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('No se pudo enviar')
                ->body('No se seleccionó ninguna cuenta bancaria.')
                ->danger()
                ->send();

            return null;
        }

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
                ->send(new BankAccountDetailsMailable($record, $bankAccounts));
        }

        if (! $hasWhatsapp) {
            $this->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('Datos enviados por email')
                ->success()
                ->send();

            return null;
        }

        $telefono = $customer->whatsappPhone();

        $mensaje = "Hola {$customer->name}! 👋\n\n";
        $mensaje .= "Te paso los datos para la transferencia:\n\n";
        $mensaje .= $bankAccounts->map(fn (BankAccount $bankAccount): string => $bankAccount->formattedDetails())->implode("\n\n");
        $mensaje .= "\n\n¡Gracias por confiar en *AltoParque*!";

        $mensajeCodificado = urlencode($mensaje);

        // Se usa api.whatsapp.com/send directo en vez de wa.me: wa.me corrompe
        // emojis (4 bytes UTF-8) en su propio redirect hacia api.whatsapp.com.
        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text={$mensajeCodificado}&type=phone_number&app_absent=0";

        Notification::make()
            ->title($hasEmail ? 'Datos enviados por email' : 'Datos listos')
            ->body('Se abrió WhatsApp con el mensaje listo para enviar.')
            ->success()
            ->send();

        $this->js(static::navigateWhatsAppTab($whatsappLink));

        return null;
    }
}
