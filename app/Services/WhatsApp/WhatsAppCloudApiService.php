<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WhatsAppCloudApiService
{
    /**
     * Envía un mensaje de texto por WhatsApp Business Cloud API al número
     * dado (con código de país, sin "+" ni espacios).
     */
    public function sendTextMessage(string $to, string $message): void
    {
        $token = config('services.whatsapp_cloud_api.token');
        $phoneNumberId = config('services.whatsapp_cloud_api.phone_number_id');

        if (blank($token) || blank($phoneNumberId)) {
            throw new RuntimeException('WhatsApp Cloud API no está configurada (faltan credenciales).');
        }

        $apiVersion = config('services.whatsapp_cloud_api.api_version', 'v21.0');

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message') ?? 'Error desconocido al enviar el mensaje por WhatsApp.'
            );
        }
    }

    /**
     * Envía una imagen ya guardada en el disco público (por su ruta relativa,
     * ej. "whatsapp-photos-enviadas/xxx.jpg") por WhatsApp, con un texto
     * opcional como epígrafe. Primero la sube a los servidores de Meta (así
     * no depende de que nuestro storage sea accesible públicamente) y recién
     * con el media ID que devuelve manda el mensaje.
     */
    public function sendImageMessage(string $to, string $path, ?string $caption = null): void
    {
        $token = config('services.whatsapp_cloud_api.token');
        $phoneNumberId = config('services.whatsapp_cloud_api.phone_number_id');

        if (blank($token) || blank($phoneNumberId)) {
            throw new RuntimeException('WhatsApp Cloud API no está configurada (faltan credenciales).');
        }

        $apiVersion = config('services.whatsapp_cloud_api.api_version', 'v21.0');

        $mediaId = $this->uploadMedia($path, $phoneNumberId, $apiVersion, $token);

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'image',
                'image' => array_filter([
                    'id' => $mediaId,
                    'caption' => $caption,
                ]),
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message') ?? 'Error desconocido al enviar la imagen por WhatsApp.'
            );
        }
    }

    private function uploadMedia(string $path, string $phoneNumberId, string $apiVersion, string $token): string
    {
        $fullPath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

        $response = Http::withToken($token)
            ->attach('file', file_get_contents($fullPath), basename($fullPath), ['Content-Type' => $mimeType])
            ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/media", [
                'messaging_product' => 'whatsapp',
                'type' => $mimeType,
            ]);

        if ($response->failed() || blank($response->json('id'))) {
            throw new RuntimeException(
                $response->json('error.message') ?? 'No se pudo subir la imagen a WhatsApp.'
            );
        }

        return $response->json('id');
    }

    /**
     * Confirma el token que Meta envía al verificar la URL del webhook
     * (paso único, al dar de alta la integración en Meta Business Manager).
     */
    public function verifyWebhookToken(string $token): bool
    {
        $expected = config('services.whatsapp_cloud_api.verify_token');

        return filled($expected) && hash_equals($expected, $token);
    }

    /**
     * Valida la firma HMAC (X-Hub-Signature-256) que Meta manda en cada
     * webhook, para confirmar que el payload viene realmente de Meta y no
     * fue falsificado. Si todavía no se configuró el app secret (número sin
     * dar de alta) se deja pasar sin validar, para no trabar el desarrollo.
     */
    public function verifySignature(string $payload, ?string $signatureHeader): bool
    {
        $appSecret = config('services.whatsapp_cloud_api.app_secret');

        if (blank($appSecret)) {
            return true;
        }

        if (blank($signatureHeader) || ! str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expected, substr($signatureHeader, strlen('sha256=')));
    }

    /**
     * Descarga una imagen enviada por el cliente (referenciada por su media
     * ID) y la guarda en el disco público. Devuelve la ruta relativa para
     * guardar en whatsapp_messages.contenido / whatsapp_conversations.foto_path.
     */
    public function downloadMedia(string $mediaId): string
    {
        $token = config('services.whatsapp_cloud_api.token');

        if (blank($token)) {
            throw new RuntimeException('WhatsApp Cloud API no está configurada (faltan credenciales).');
        }

        $apiVersion = config('services.whatsapp_cloud_api.api_version', 'v21.0');

        $metaResponse = Http::withToken($token)->get("https://graph.facebook.com/{$apiVersion}/{$mediaId}");

        if ($metaResponse->failed()) {
            throw new RuntimeException('No se pudo obtener la URL de la imagen enviada por WhatsApp.');
        }

        $fileResponse = Http::withToken($token)->get($metaResponse->json('url'));

        if ($fileResponse->failed()) {
            throw new RuntimeException('No se pudo descargar la imagen enviada por WhatsApp.');
        }

        $extension = match ($metaResponse->json('mime_type')) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $path = 'whatsapp-photos/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($path, $fileResponse->body());

        return $path;
    }
}
