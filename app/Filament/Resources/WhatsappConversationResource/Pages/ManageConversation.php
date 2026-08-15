<?php

namespace App\Filament\Resources\WhatsappConversationResource\Pages;

use App\Filament\Resources\WhatsappConversationResource;
use App\Services\Altoparque\AltoparqueApiClient;
use App\Services\Altoparque\RemoteCustomer;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Antes trabajaba contra el WhatsappConversation Eloquent local; ahora la
 * conversación vive en la API central de Altoparque, así que todo pasa por
 * AltoparqueApiClient. $conversation queda como un stdClass con la misma
 * forma que el modelo local (mismos nombres de atributo) para no tener que
 * tocar la vista Blade.
 */
class ManageConversation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = WhatsappConversationResource::class;

    protected static string $view = 'filament.resources.whatsapp-conversation-resource.pages.manage-conversation';

    public int $conversationId;

    public object $conversation;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->conversationId = (int) $record;

        $this->refrescar();

        $this->form->fill();
    }

    /**
     * Refresca $conversation desde la API. Se llama explícitamente (no hay
     * Eloquent local que Livewire re-hidrate solo entre requests).
     */
    public function refrescar(): void
    {
        $remota = app(AltoparqueApiClient::class)->conversation($this->conversationId);

        $this->conversation = (object) [
            'sitio_origen' => $remota->sitioOrigen(),
            'zona' => $remota->zona(),
            'servicio_solicitado' => $remota->servicioSolicitado(),
            'estado_conversacion' => $remota->estadoConversacion(),
            'foto_path' => $remota->fotoPath(),
            'customer' => (object) [
                'name' => $remota->customer()?->name(),
                'phone' => $remota->customer()?->phone(),
            ],
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('contenido')
                    ->label('Respuesta')
                    ->required(fn (Forms\Get $get): bool => blank($get('imagen')))
                    ->maxLength(4096)
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('imagen')
                    ->label('Adjuntar foto (opcional)')
                    ->image()
                    ->disk('public')
                    ->directory('whatsapp-photos-enviadas')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function getTitle(): string
    {
        return 'Conversación con '.($this->conversation->customer->name ?: $this->conversation->customer->phone);
    }

    /**
     * @return Collection<int, object{remitente: string, tipo: string, contenido: string, enviado_en: Carbon}>
     */
    public function getMessagesProperty(): Collection
    {
        return collect(app(AltoparqueApiClient::class)->conversationMessages($this->conversationId))
            ->map(fn (array $mensaje) => (object) [
                'remitente' => $mensaje['remitente'],
                'tipo' => $mensaje['tipo'],
                'contenido' => $mensaje['contenido'],
                'enviado_en' => Carbon::parse($mensaje['enviado_en']),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cerrar')
                ->label('Cerrar caso')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->conversation->estado_conversacion !== 'cerrada')
                ->action(function (): void {
                    app(AltoparqueApiClient::class)->updateConversation($this->conversationId, [
                        'estado_conversacion' => 'cerrada',
                    ]);

                    $this->refrescar();

                    Notification::make()->title('Caso cerrado')->success()->send();
                }),

            Actions\Action::make('reabrir')
                ->label('Reabrir (Claudia vuelve a responder)')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->conversation->estado_conversacion === 'cerrada')
                ->action(function (): void {
                    app(AltoparqueApiClient::class)->updateConversation($this->conversationId, [
                        'estado_conversacion' => 'claudia_atendiendo',
                    ]);

                    $this->refrescar();

                    Notification::make()->title('Caso reabierto')->success()->send();
                }),
        ];
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $contenido = $data['contenido'] ?? null;
        $imagen = $data['imagen'] ?? null;

        if (blank($this->conversation->customer->phone)) {
            Notification::make()
                ->title('No se pudo enviar')
                ->body('El cliente no tiene teléfono cargado.')
                ->danger()
                ->send();

            return;
        }

        // El objeto RemoteCustomer real ya no se conserva entre requests de
        // Livewire (solo sobrevive $conversation, un stdClass) — se arma uno
        // efímero acá solo para reusar la normalización de whatsappPhone().
        $telefono = (new RemoteCustomer(['phone' => $this->conversation->customer->phone]))->whatsappPhone();

        try {
            if (filled($imagen)) {
                app(WhatsAppCloudApiService::class)->sendImageMessage($telefono, $imagen, $contenido);
            } else {
                app(WhatsAppCloudApiService::class)->sendTextMessage($telefono, $contenido);
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title('No se pudo enviar el mensaje')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $altoparque = app(AltoparqueApiClient::class);

        // remitente queda fijo en "humano" (no el nombre del admin): la API
        // central solo acepta cliente/claudia/humano en este campo.
        if (filled($imagen)) {
            $altoparque->createMessage($this->conversationId, [
                'remitente' => 'humano',
                'contenido' => $imagen,
                'tipo' => 'imagen',
                'enviado_en' => now()->toIso8601String(),
            ]);

            if (filled($contenido)) {
                $altoparque->createMessage($this->conversationId, [
                    'remitente' => 'humano',
                    'contenido' => $contenido,
                    'tipo' => 'texto',
                    'enviado_en' => now()->toIso8601String(),
                ]);
            }
        } else {
            $altoparque->createMessage($this->conversationId, [
                'remitente' => 'humano',
                'contenido' => $contenido,
                'tipo' => 'texto',
                'enviado_en' => now()->toIso8601String(),
            ]);
        }

        // No se manda asignado_a: el id del admin local (auth()->id()) no
        // corresponde a ningún usuario real en la base central — asignarlo
        // pisaría/apuntaría a un usuario central distinto por coincidencia
        // de id. Sin un mapeo confiable entre cuentas locales y centrales,
        // mejor dejarlo sin asignar que asignarlo mal.
        if ($this->conversation->estado_conversacion !== 'con_humano') {
            $altoparque->updateConversation($this->conversationId, [
                'estado_conversacion' => 'con_humano',
            ]);
        }

        $this->refrescar();

        $this->form->fill();
    }
}
