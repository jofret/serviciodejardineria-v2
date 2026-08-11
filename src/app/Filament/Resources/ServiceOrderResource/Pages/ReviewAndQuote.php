<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Concerns\OpensWhatsAppInNewTab;
use App\Filament\Resources\PresupuestoPorFotoResource;
use App\Filament\Resources\ServiceOrderResource;
use App\Models\ServiceOrder;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ReviewAndQuote extends Page implements HasForms
{
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
            'relevamiento.workItems.media',
            'relevamiento.workTools',
        ])->findOrFail($record);

        abort_unless($this->serviceOrder->canReviewAndQuote(), 404);

        $this->form->fill([
            'final_price' => $this->serviceOrder->final_price,
            'final_price_notes' => $this->serviceOrder->final_price_notes,
            'budget_comment' => $this->serviceOrder->budget_comment,
        ]);
    }

    public function getTitle(): string
    {
        return 'Revisar y presupuestar';
    }

    public function form(Form $form): Form
    {
        return $form
            ->model($this->serviceOrder->relevamiento)
            ->schema([
                Forms\Components\Section::make('Trabajo a realizar')
                    ->description('Los mismos ítems que carga un relevador desde el celular — se pueden agregar, editar o quitar acá igual que en Presupuestos por foto.')
                    ->schema([
                        PresupuestoPorFotoResource::workItemsField(requirePhoto: false, showPhotosField: false),
                    ]),

                Forms\Components\Section::make('Precio final')
                    ->description('Propio de esta orden de servicio — independiente del precio estimativo que cargó el relevador.')
                    ->schema([
                        Forms\Components\TextInput::make('final_price')
                            ->label('Precio final')
                            ->type('text')
                            ->inputMode('decimal')
                            ->prefix('$')
                            ->required()
                            ->extraInputAttributes(['x-on:input' => 'window.formatThousandsInput($event)'])
                            ->afterStateHydrated(fn ($component, $state) => $component->state(static::formatPriceDisplay($state)))
                            ->dehydrateStateUsing(fn ($state) => static::parsePriceInput($state))
                            ->rule(static fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                if (filled($value) && ! is_numeric(static::parsePriceInput($value))) {
                                    $fail('El precio final debe ser un valor numérico.');
                                }
                            }),
                        Forms\Components\Textarea::make('final_price_notes')
                            ->label('Observaciones del precio final')
                            ->helperText('Interno: justificá acá si el precio final difiere del estimado por el relevador. No se muestra al cliente.')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('budget_comment')
                            ->label('Comentario para el cliente')
                            ->helperText('Público: aparece en la proforma que ve el cliente, después de "Orden y Limpieza".')
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

    private function sendBudgetDescription(): string
    {
        if (! filled($this->serviceOrder->customer->phone)) {
            return 'El cliente no tiene teléfono cargado — no hay forma de enviarle el presupuesto.';
        }

        return 'Se guarda el precio final cargado y se envía por WhatsApp. '.
            'El cliente va a poder ver el presupuesto, aceptarlo y descargarlo.';
    }

    /**
     * Convierte el valor crudo (numérico, o ya formateado si el usuario
     * está editando) a la representación con punto de miles y coma
     * decimal que se muestra en el campo (ej. "1.234.567,50").
     */
    private static function formatPriceDisplay($state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $normalized = static::parsePriceInput($state);

        if (! is_numeric($normalized)) {
            return (string) $state;
        }

        [$intPart, $decPart] = array_pad(explode('.', $normalized, 2), 2, null);

        $formattedInt = number_format((float) $intPart, 0, '', '.');

        return $decPart !== null ? $formattedInt.','.$decPart : $formattedInt;
    }

    /**
     * Inversa de formatPriceDisplay(): saca los puntos de miles y
     * convierte la coma decimal en punto, para validar y guardar el
     * valor numérico real.
     */
    private static function parsePriceInput($state): ?string
    {
        if (blank($state)) {
            return null;
        }

        return str_replace(['.', ','], ['', '.'], (string) $state);
    }

    public function save(): void
    {
        $this->serviceOrder->update($this->form->getState());
        $this->form->saveRelationships();

        Notification::make()
            ->title('Precio final guardado')
            ->success()
            ->send();
    }

    public function sendBudget()
    {
        $this->serviceOrder->update($this->form->getState());
        $this->form->saveRelationships();

        $token = $this->serviceOrder->generateBudgetToken();
        $enlace = url('/presupuesto/'.$token);

        if (! filled($this->serviceOrder->customer->phone)) {
            $this->js(static::closeWhatsAppTab());

            Notification::make()
                ->title('No se pudo enviar')
                ->body('El cliente no tiene teléfono cargado.')
                ->danger()
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
            ->title('Presupuesto guardado')
            ->body('Se abrió WhatsApp con el mensaje listo para enviar.')
            ->success()
            ->send();

        $this->js(static::navigateWhatsAppTab($whatsappLink));

        return null;
    }
}
