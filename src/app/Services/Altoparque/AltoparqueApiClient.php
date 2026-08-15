<?php

namespace App\Services\Altoparque;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente HTTP hacia la API central de Altoparque (altoparque.com), donde
 * viven el Customer, la WhatsappConversation y los WhatsappMessage de
 * Claudia — ya no se escriben en la base local de este sitio.
 *
 * Mismo patrón que WhatsAppCloudApiService/ClaudiaConversationService:
 * Http::withToken(), sin reintentos, RuntimeException si $response->failed().
 *
 * A diferencia de poda-de-altura-v2 (que atiende varios sitios desde una
 * sola instancia y necesita useSite()/tokens_por_sitio para elegir el token
 * según el dominio detectado), esta es una instalación de un solo sitio: un
 * único token (ALTOPARQUE_API_TOKEN) alcanza siempre.
 */
class AltoparqueApiClient
{
    public function upsertCustomer(string $phone, ?string $name = null): RemoteCustomer
    {
        $response = $this->request()->post($this->url('/customers'), array_filter([
            'phone' => $phone,
            'name' => $name,
        ], fn ($valor) => filled($valor)));

        return new RemoteCustomer($this->jsonOrFail($response, 'crear/actualizar el cliente'));
    }

    public function updateCustomer(int $id, array $data): RemoteCustomer
    {
        $response = $this->request()->patch($this->url("/customers/{$id}"), $data);

        return new RemoteCustomer($this->jsonOrFail($response, 'actualizar el cliente'));
    }

    public function latestConversation(int $customerId): ?RemoteConversation
    {
        $response = $this->request()->get($this->url('/whatsapp-conversations/latest'), [
            'customer_id' => $customerId,
        ]);

        $data = $this->jsonOrFail($response, 'buscar la conversación')['data'] ?? null;

        return $data ? new RemoteConversation($data) : null;
    }

    public function createConversation(int $customerId, array $data = []): RemoteConversation
    {
        $response = $this->request()->post($this->url('/whatsapp-conversations'), array_merge(
            ['customer_id' => $customerId],
            $data,
        ));

        return new RemoteConversation($this->jsonOrFail($response, 'crear la conversación'));
    }

    public function updateConversation(int $id, array $data): RemoteConversation
    {
        $response = $this->request()->patch($this->url("/whatsapp-conversations/{$id}"), $data);

        return new RemoteConversation($this->jsonOrFail($response, 'actualizar la conversación'));
    }

    /**
     * Una conversación puntual, con el customer embebido. Rechaza (404) si
     * no pertenece al sitio de este token.
     */
    public function conversation(int $id): RemoteConversation
    {
        $response = $this->request()->get($this->url("/whatsapp-conversations/{$id}"));

        return new RemoteConversation($this->jsonOrFail($response, 'buscar la conversación'));
    }

    /**
     * Lista paginada de conversaciones de este sitio, con el customer
     * embebido — para el panel.
     *
     * @return array{data: array<int, RemoteConversation>, meta: array{current_page: int, last_page: int, total: int}}
     */
    public function conversations(?string $estadoConversacion = null, int $page = 1, int $perPage = 20): array
    {
        $response = $this->request()->get($this->url('/whatsapp-conversations'), array_filter([
            'estado_conversacion' => $estadoConversacion,
            'page' => $page,
            'per_page' => $perPage,
        ], fn ($valor) => filled($valor)));

        $body = $this->jsonOrFail($response, 'listar las conversaciones');

        return [
            'data' => array_map(fn (array $item) => new RemoteConversation($item), $body['data'] ?? []),
            'meta' => [
                'current_page' => $body['current_page'] ?? 1,
                'last_page' => $body['last_page'] ?? 1,
                'total' => $body['total'] ?? 0,
            ],
        ];
    }

    public function messageExists(string $wamid): bool
    {
        $response = $this->request()->get($this->url('/whatsapp-messages/exists'), ['wamid' => $wamid]);

        return (bool) ($this->jsonOrFail($response, 'chequear si el mensaje ya existe')['exists'] ?? false);
    }

    public function createMessage(int $conversationId, array $data): array
    {
        $response = $this->request()->post($this->url("/whatsapp-conversations/{$conversationId}/messages"), $data);

        return $this->jsonOrFail($response, 'guardar el mensaje');
    }

    /**
     * @return array<int, array{remitente: string, tipo: string, contenido: string}>
     */
    public function conversationMessages(int $conversationId): array
    {
        $response = $this->request()->get($this->url("/whatsapp-conversations/{$conversationId}/messages"));

        return $this->jsonOrFail($response, 'traer el historial de mensajes')['data'] ?? [];
    }

    /**
     * URL al panel de Altoparque Central para atender la conversación
     * (usada en el email de aviso cuando Claudia deriva a un humano).
     */
    public function panelUrlForConversation(int $conversationId): string
    {
        $adminBase = preg_replace('#/api/?$#', '', rtrim($this->baseUrl(), '/'));

        return "{$adminBase}/admin/whatsapp-conversations/{$conversationId}/edit";
    }

    private function request(): PendingRequest
    {
        $token = config('services.altoparque.api_token');

        if (blank($token)) {
            throw new RuntimeException('Altoparque API no está configurada (falta ALTOPARQUE_API_TOKEN).');
        }

        return Http::withToken($token)->acceptJson();
    }

    private function url(string $path): string
    {
        return rtrim($this->baseUrl(), '/').$path;
    }

    private function baseUrl(): string
    {
        $url = config('services.altoparque.api_url');

        if (blank($url)) {
            throw new RuntimeException('Altoparque API no está configurada (falta ALTOPARQUE_API_URL).');
        }

        return $url;
    }

    private function jsonOrFail(Response $response, string $accion): array
    {
        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('message') ?? "Error desconocido al {$accion} en la API de Altoparque."
            );
        }

        return $response->json() ?? [];
    }
}
