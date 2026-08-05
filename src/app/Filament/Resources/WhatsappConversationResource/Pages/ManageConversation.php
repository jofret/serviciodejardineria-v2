<?php

namespace App\Filament\Resources\WhatsappConversationResource\Pages;

use App\Filament\Resources\WhatsappConversationResource;
use App\Models\WhatsappConversation;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class ManageConversation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = WhatsappConversationResource::class;

    protected static string $view = 'filament.resources.whatsapp-conversation-resource.pages.manage-conversation';

    public WhatsappConversation $conversation;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->conversation = WhatsappConversation::with('customer', 'asignadoA')->findOrFail($record);

        $this->form->fill();
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

    public function getMessagesProperty(): Collection
    {
        return $this->conversation->messages()->get();
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
                    $this->conversation->cerrar();

                    Notification::make()->title('Caso cerrado')->success()->send();
                }),

            Actions\Action::make('reabrir')
                ->label('Reabrir (Claudia vuelve a responder)')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->conversation->estado_conversacion === 'cerrada')
                ->action(function (): void {
                    $this->conversation->reabrir();

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

        try {
            if (filled($imagen)) {
                app(WhatsAppCloudApiService::class)->sendImageMessage(
                    $this->conversation->customer->whatsappPhone(),
                    $imagen,
                    $contenido,
                );
            } else {
                app(WhatsAppCloudApiService::class)->sendTextMessage(
                    $this->conversation->customer->whatsappPhone(),
                    $contenido,
                );
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title('No se pudo enviar el mensaje')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        if (filled($imagen)) {
            $this->conversation->messages()->create([
                'remitente' => auth()->user()->name,
                'contenido' => $imagen,
                'tipo' => 'imagen',
                'enviado_en' => now(),
            ]);

            if (filled($contenido)) {
                $this->conversation->messages()->create([
                    'remitente' => auth()->user()->name,
                    'contenido' => $contenido,
                    'tipo' => 'texto',
                    'enviado_en' => now(),
                ]);
            }
        } else {
            $this->conversation->messages()->create([
                'remitente' => auth()->user()->name,
                'contenido' => $contenido,
                'tipo' => 'texto',
                'enviado_en' => now(),
            ]);
        }

        if (! $this->conversation->estaConHumano()) {
            $this->conversation->derivarAHumano(auth()->id());
        } elseif (blank($this->conversation->asignado_a)) {
            $this->conversation->update(['asignado_a' => auth()->id()]);
        }

        $this->form->fill();
    }
}
