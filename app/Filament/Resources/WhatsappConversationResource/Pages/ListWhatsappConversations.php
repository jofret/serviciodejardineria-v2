<?php

namespace App\Filament\Resources\WhatsappConversationResource\Pages;

use App\Filament\Resources\WhatsappConversationResource;
use App\Services\Altoparque\AltoparqueApiClient;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;

/**
 * Página a medida (no Filament\Resources\Pages\ListRecords): las
 * conversaciones ya no son un modelo Eloquent local, sino datos de la API
 * central de Altoparque, así que no hay Table/query de Eloquent para
 * bindear. Se pierden búsqueda/orden por columna y las bulk actions de
 * Filament — a cambio, un listado simple con filtro por estado y paginado
 * manual sobre la respuesta de la API.
 */
class ListWhatsappConversations extends Page
{
    protected static string $resource = WhatsappConversationResource::class;

    protected static string $view = 'filament.resources.whatsapp-conversation-resource.pages.list-whatsapp-conversations';

    #[Url]
    public ?string $estado = null;

    #[Url]
    public int $page = 1;

    public function getConversationsProperty(): array
    {
        return app(AltoparqueApiClient::class)->conversations(
            estadoConversacion: $this->estado,
            page: $this->page,
        );
    }

    public function getEstadosProperty(): array
    {
        return WhatsappConversationResource::ESTADOS;
    }

    public function filtrarPorEstado(?string $estado): void
    {
        $this->estado = filled($estado) ? $estado : null;
        $this->page = 1;
    }

    public function irAPagina(int $page): void
    {
        $this->page = max(1, $page);
    }
}
