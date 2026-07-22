<?php

namespace App\Filament\Concerns;

use App\Mail\BankAccountDetailsMailable;
use App\Models\BankAccount;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

/**
 * Compartido entre WorkOrderResource\Pages\EditWorkOrder (acción de
 * cabecera) y WorkOrderResource (acción de fila en el listado) — misma
 * acción, dos lugares. El envío solo tiene sentido si el cliente ya dio
 * conformidad y eligió pagar por transferencia; si eligió efectivo, el
 * mismo botón se muestra deshabilitado como "Paga con efectivo" en vez
 * de desaparecer.
 */
trait SendsBankAccountDetails
{
    use OpensWhatsAppInNewTab;

    protected static function isEligibleForBankAccountDetails(WorkOrder $record): bool
    {
        return in_array($record->status, ['programado', 'en_curso', 'completado'], true)
            && $record->conformity_confirmed_at !== null;
    }

    protected static function paysWithTransferencia(WorkOrder $record): bool
    {
        return in_array('transferencia', $record->serviceOrder->payment_method_preference ?? [], true);
    }

    /**
     * @param  \Filament\Actions\Action|\Filament\Tables\Actions\Action  $action
     * @return \Filament\Actions\Action|\Filament\Tables\Actions\Action
     */
    protected static function configureBankAccountDetailsAction($action)
    {
        return $action
            ->label(fn (WorkOrder $record): string => static::paysWithTransferencia($record)
                ? 'Enviar datos bancarios'
                : 'Paga con efectivo')
            ->icon(fn (WorkOrder $record): string => static::paysWithTransferencia($record)
                ? 'heroicon-o-building-library'
                : 'heroicon-o-banknotes')
            ->color(fn (WorkOrder $record): string => static::paysWithTransferencia($record) ? 'success' : 'gray')
            ->disabled(fn (WorkOrder $record): bool => ! static::paysWithTransferencia($record))
            ->visible(fn (WorkOrder $record): bool => static::isEligibleForBankAccountDetails($record))
            ->form([
                Forms\Components\CheckboxList::make('bank_account_ids')
                    ->label('Cuentas a enviar')
                    ->options(fn (): array => BankAccount::orderBy('name')->pluck('name', 'id')->all())
                    ->default(fn (): array => BankAccount::orderBy('name')->pluck('id')->all())
                    ->required()
                    ->columns(1),
            ])
            ->modalHeading('Enviar datos bancarios')
            ->modalDescription(fn (WorkOrder $record): string => static::bankAccountDetailsDescription($record))
            ->modalSubmitActionLabel('Enviar ahora')
            ->modalSubmitAction(fn ($action) => $action->extraAttributes(static::whatsAppTriggerAttributes()))
            ->action(function (WorkOrder $record, array $data, $livewire): void {
                static::sendBankAccountDetails($record, $data['bank_account_ids'], $livewire);
            });
    }

    protected static function bankAccountDetailsDescription(WorkOrder $record): string
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

    protected static function sendBankAccountDetails(WorkOrder $record, array $bankAccountIds, $livewire): void
    {
        $customer = $record->serviceOrder->customer;

        $bankAccounts = BankAccount::whereIn('id', $bankAccountIds)->orderBy('name')->get();

        if ($bankAccounts->isEmpty()) {
            $livewire->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('No se pudo enviar')
                ->body('No se seleccionó ninguna cuenta bancaria.')
                ->danger()
                ->send();

            return;
        }

        $hasEmail = filled($customer->email);
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
            Mail::to($customer->email)
                ->send(new BankAccountDetailsMailable($record, $bankAccounts));
        }

        if (! $hasWhatsapp) {
            $livewire->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('Datos enviados por email')
                ->success()
                ->send();

            return;
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

        $livewire->js(static::navigateWhatsAppTab($whatsappLink));
    }
}
