<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Concerns\FormatsThousandsInput;
use App\Filament\Concerns\OpensWhatsAppInNewTab;
use App\Filament\Resources\ServiceOrderResource;
use App\Mail\BudgetMailable;
use App\Models\ServiceOrder;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Mail;

class ReviewAndQuote extends Page implements HasForms
{
    use FormatsThousandsInput;
    use InteractsWithForms;
    use OpensWhatsAppInNewTab;

    protected static string $resource = ServiceOrderResource::class;

    protected static string $view = 'filament.resources.service-order-resource.pages.review-and-quote';

    public ServiceOrder $serviceOrder;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->serviceOrder = ServiceOrder::with([
            'customer',
            'property',
            'category',
            'items.media',
            'relevamiento.workItems.media',
            'relevamiento.workTools',
        ])->findOrFail($record);

        abort_unless($this->serviceOrder->canReviewAndQuote(), 404);

        $this->form->fill([
            'final_price' => $this->serviceOrder->final_price,
            'final_price_notes' => $this->serviceOrder->final_price_notes,
        ]);
    }

    public function getTitle(): string
    {
        return 'Revisar y presupuestar';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Precio final')
                    ->description('Propio de esta orden de servicio — independiente del precio estimativo que cargó el relevador.')
                    ->schema([
                        static::thousandsFormattedTextInput('final_price')
                            ->label('Precio final')
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Textarea::make('final_price_notes')
                            ->label('Observaciones del precio final')
                            ->helperText('Justificá acá si el precio final difiere del estimado por el relevador.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Guardar')
                ->color('gray')
                ->action('save'),

            Actions\Action::make('sendBudget')
                ->label('Enviar presupuesto al cliente')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Enviar presupuesto')
                ->modalDescription(fn (): string => $this->sendBudgetDescription())
                ->modalSubmitActionLabel('Enviar ahora')
                ->modalSubmitAction(fn ($action) => $action->extraAttributes(static::whatsAppTriggerAttributes()))
                ->action('sendBudget'),
        ];
    }

    /**
     * Arma la descripción del modal de confirmación según los canales que
     * realmente se van a usar (el envío ya no es "elegir uno": manda por
     * todos los que el cliente tenga cargados).
     */
    private function sendBudgetDescription(): string
    {
        $canales = array_filter([
            filled($this->serviceOrder->customer->email) ? 'email' : null,
            filled($this->serviceOrder->customer->phone) ? 'WhatsApp' : null,
        ]);

        if ($canales === []) {
            return 'El cliente no tiene email ni teléfono cargado — no hay forma de enviarle el presupuesto.';
        }

        return 'Se guarda el precio final cargado y se envía por '.implode(' y ', $canales).
            '. El cliente va a poder ver el presupuesto, aceptarlo y descargarlo.';
    }

    public function save(): void
    {
        $this->serviceOrder->update($this->form->getState());

        Notification::make()
            ->title('Precio final guardado')
            ->success()
            ->send();
    }

    public function sendBudget()
    {
        $this->serviceOrder->update($this->form->getState());

        $token = $this->serviceOrder->generateBudgetToken();
        $enlace = url('/presupuesto/'.$token);

        $hasEmail = filled($this->serviceOrder->customer->email);
        $hasWhatsapp = filled($this->serviceOrder->customer->phone);

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
            Mail::to($this->serviceOrder->customer->email)
                ->send(new BudgetMailable($this->serviceOrder, $enlace));
        }

        if (! $hasWhatsapp) {
            $this->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('Presupuesto enviado por email')
                ->success()
                ->send();

            return null;
        }

        $telefono = $this->serviceOrder->customer->whatsappPhone();

        $mensaje = "Hola {$this->serviceOrder->customer->name}! 👋\n\n";
        $mensaje .= "Ya está listo el presupuesto de *AltoParque* para tu servicio.\n\n";
        $mensaje .= "📋 Podés verlo, aceptarlo o descargarlo acá:\n";
        $mensaje .= $enlace."\n\n";
        $mensaje .= '¡Gracias por confiar en nosotros! 🌿';

        $mensajeCodificado = urlencode($mensaje);

        // Se usa api.whatsapp.com/send directo en vez de wa.me: wa.me corrompe
        // emojis (4 bytes UTF-8) en su propio redirect hacia api.whatsapp.com.
        $whatsappLink = "https://api.whatsapp.com/send/?phone={$telefono}&text={$mensajeCodificado}&type=phone_number&app_absent=0";

        Notification::make()
            ->title($hasEmail ? 'Presupuesto enviado por email' : 'Presupuesto guardado')
            ->body('Se abrió WhatsApp con el mensaje listo para enviar.')
            ->success()
            ->send();

        $this->js(static::navigateWhatsAppTab($whatsappLink));

        return null;
    }
}
