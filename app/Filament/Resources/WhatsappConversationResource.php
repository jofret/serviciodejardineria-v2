<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPendingAttentionBadge;
use App\Filament\Resources\WhatsappConversationResource\Pages;
use App\Models\WhatsappConversation;
use App\Services\Altoparque\AltoparqueApiClient;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Las conversaciones ya no viven en la tabla local whatsapp_conversations
 * (Claudia ahora escribe en la API central de Altoparque) — por eso este
 * Resource no tiene table()/form() bindeados a Eloquent: ListWhatsappConversations
 * y ManageConversation son páginas a medida que consultan AltoparqueApiClient.
 * $model se mantiene solo por compatibilidad con Resource (label, ícono,
 * etc.), no para leer/escribir datos.
 */
class WhatsappConversationResource extends Resource
{
    use HasPendingAttentionBadge;

    protected static ?string $model = WhatsappConversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'WhatsApp (Claudia)';

    protected static ?string $modelLabel = 'conversación';

    protected static ?string $pluralModelLabel = 'conversaciones de WhatsApp';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappConversations::route('/'),
            'manage' => Pages\ManageConversation::route('/{record}/atender'),
        ];
    }

    /**
     * Cuenta vía API en vez de la tabla local (que ya no recibe conversaciones
     * nuevas). Si la API central no responde, no tira abajo el menú lateral:
     * se loguea y se oculta el badge (mejor que romper la navegación entera).
     */
    protected static function pendingAttentionCount(): int
    {
        try {
            return app(AltoparqueApiClient::class)
                ->conversations(estadoConversacion: 'con_humano', perPage: 1)['meta']['total'];
        } catch (Throwable $e) {
            Log::warning('No se pudo obtener el conteo de conversaciones pendientes desde Altoparque.', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    protected static function pendingAttentionTooltip(): ?string
    {
        return 'Conversaciones derivadas a un humano, esperando respuesta';
    }

    protected static function pendingAttentionColor(): string
    {
        return 'danger';
    }
}
