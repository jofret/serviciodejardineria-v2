<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPendingAttentionBadge;
use App\Filament\Resources\WhatsappConversationResource\Pages;
use App\Services\Altoparque\AltoparqueApiClient;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Las conversaciones ya no viven en tabla local (Claudia escribe en la API
 * central de Altoparque) — este Resource no tiene table()/form() bindeados
 * a Eloquent: ListWhatsappConversations y ManageConversation son páginas a
 * medida que consultan AltoparqueApiClient. Sin $model: nada acá invoca
 * getModel()/getEloquentQuery() (las rutas de las páginas no tipan
 * $record como Eloquent, así que Laravel no intenta bindearlo).
 */
class WhatsappConversationResource extends Resource
{
    use HasPendingAttentionBadge;

    /**
     * Estados posibles de una conversación (antes vivía como constante en
     * el modelo Eloquent local WhatsappConversation, ya borrado).
     */
    public const ESTADOS = [
        'claudia_atendiendo' => 'Claudia atendiendo',
        'esperando_agenda_visita' => 'Esperando agenda de visita',
        'esperando_cotizacion_foto' => 'Esperando cotización por foto',
        'con_humano' => 'Con humano',
        'cerrada' => 'Cerrada',
    ];

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
