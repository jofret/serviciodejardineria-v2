<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Paso único de verificación que pide Meta al dar de alta la URL del
     * webhook en Meta Business Manager.
     */
    public function verify(Request $request, WhatsAppCloudApiService $whatsApp): Response
    {
        if (
            $request->query('hub_mode') === 'subscribe'
            && $whatsApp->verifyWebhookToken((string) $request->query('hub_verify_token'))
        ) {
            return response((string) $request->query('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Recibe cada mensaje entrante. Usa afterResponse() para contestarle 200
     * a Meta al instante y recién ahí procesar: en este hosting no hay worker
     * de cola corriendo solo (dependíamos del cron, con minutos de demora), y
     * con dispatchSync a veces el proxy cortaba la conexión a mitad de la
     * llamada a DeepSeek antes de que hubiera chance de loguear nada.
     */
    public function receive(Request $request, WhatsAppCloudApiService $whatsApp): JsonResponse
    {
        if (! $whatsApp->verifySignature($request->getContent(), $request->header('X-Hub-Signature-256'))) {
            Log::warning('WhatsApp webhook: firma inválida, se ignora el payload.');

            return response()->json(['status' => 'ignored']);
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                if (empty($value['messages'])) {
                    // También llegan webhooks de "statuses" (entregado/leído) sin mensajes: se ignoran.
                    continue;
                }

                foreach ($value['messages'] as $message) {
                    ProcessIncomingWhatsAppMessage::dispatch($message, $value['contacts'] ?? [])->afterResponse();
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
