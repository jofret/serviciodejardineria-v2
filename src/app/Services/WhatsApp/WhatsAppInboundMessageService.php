<?php

namespace App\Services\WhatsApp;

use App\Mail\AltoparqueConversationDerivedMailable;
use App\Services\Altoparque\AltoparqueApiClient;
use App\Services\Altoparque\RemoteConversation;
use App\Services\Altoparque\RemoteCustomer;
use App\Services\Claudia\ClaudiaConversationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class WhatsAppInboundMessageService
{
    private const REMITENTE_CLIENTE = 'cliente';

    private const REMITENTE_CLAUDIA = 'claudia';

    /**
     * Casilla que recibe las notificaciones de contacto nuevo en este sitio.
     * Réplica de la constante que antes vivía en WhatsappConversation (ahora
     * la conversación vive en la API central, sin Eloquent local que
     * dispare el hook de aviso).
     */
    private const ADMIN_EMAILS = [
        'geral4bebes@gmail.com',
        'jofretjofret@gmail.com',
    ];

    public function __construct(
        private WhatsAppCloudApiService $whatsApp,
        private ClaudiaConversationService $claudia,
        private AltoparqueApiClient $altoparque,
    ) {
    }

    /**
     * Procesa un mensaje entrante de WhatsApp: crea/actualiza el Customer y
     * la conversación en la API central de Altoparque, guarda el mensaje y,
     * si corresponde, hace que Claudia responda.
     *
     * @param  array<string, mixed>  $message
     * @param  array<int, array<string, mixed>>  $contacts
     */
    public function handle(array $message, array $contacts): void
    {
        $wamid = $message['id'] ?? null;

        if (filled($wamid) && $this->altoparque->messageExists($wamid)) {
            // Meta reintenta el webhook si no respondemos a tiempo o hay un
            // error transitorio: sin esto se duplicaría el mensaje.
            return;
        }

        $waId = $message['from'] ?? null;

        if (blank($waId)) {
            return;
        }

        $customer = $this->resolverCliente($waId, $contacts);

        [$tipo, $contenido] = $this->extraerContenido($message);

        $conversation = $this->resolverConversacion($customer);
        $estadoAntes = $conversation->estadoConversacion();

        $this->altoparque->createMessage($conversation->id(), [
            'wamid' => $wamid,
            'remitente' => self::REMITENTE_CLIENTE,
            'contenido' => $contenido,
            'tipo' => $tipo,
            'enviado_en' => (isset($message['timestamp'])
                ? now()->setTimestamp((int) $message['timestamp'])
                : now())->toIso8601String(),
        ]);

        if ($tipo === 'imagen') {
            $conversation = $this->altoparque->updateConversation($conversation->id(), ['foto_path' => $contenido]);
        }

        if ($conversation->estaConHumano()) {
            // Pausada: ya se guardó el mensaje, Claudia no interviene.
            return;
        }

        // Recibir la foto que se había pedido deriva directo, sin pasar por
        // Claude: es una regla de negocio fija, no algo a interpretar.
        if ($tipo === 'imagen' && $estadoAntes === 'esperando_cotizacion_foto') {
            $this->derivarPorFotoRecibida($conversation, $customer);

            return;
        }

        $this->responderConClaudia($conversation, $customer);
    }

    private function derivarPorFotoRecibida(RemoteConversation $conversation, RemoteCustomer $customer): void
    {
        $this->enviarYGuardar(
            $conversation,
            $customer,
            'Perfecto, ya tengo la foto. Ahora te paso con alguien del equipo para que te pase un precio. 🌿'
        );

        $this->derivarAHumano($conversation, $customer, motivo: 'El cliente mandó la foto pedida para la cotización.');
    }

    private function responderConClaudia(RemoteConversation $conversation, RemoteCustomer $customer): void
    {
        try {
            $resultado = $this->claudia->generarRespuesta($conversation, $customer);
        } catch (Throwable $e) {
            Log::error('Claudia: falló la llamada al motor conversacional.', ['error' => $e->getMessage()]);

            return;
        }

        if (blank($resultado['mensaje'] ?? null)) {
            // DeepSeek a veces devuelve el tool_call con "mensaje" vacío (no
            // es determinístico, la misma conversación reintentada de nuevo
            // suele andar bien) — probamos una vez más antes de dejar al
            // cliente sin respuesta.
            Log::warning('Claudia: DeepSeek devolvió un mensaje vacío, reintentando una vez.');

            try {
                $resultado = $this->claudia->generarRespuesta($conversation, $customer);
            } catch (Throwable $e) {
                Log::error('Claudia: falló el reintento tras mensaje vacío.', ['error' => $e->getMessage()]);

                return;
            }

            if (blank($resultado['mensaje'] ?? null)) {
                Log::error('Claudia: DeepSeek volvió a devolver un mensaje vacío en el reintento.');

                return;
            }
        }

        $this->enviarYGuardar($conversation, $customer, $resultado['mensaje']);

        if (filled($resultado['nombre_cliente'] ?? null) && ! $customer->tieneNombre()) {
            $customer = $this->altoparque->updateCustomer($customer->id(), ['name' => $resultado['nombre_cliente']]);
        }

        $datosNuevos = array_filter([
            'zona' => $resultado['zona'] ?? null,
            'servicio_solicitado' => $resultado['servicio_solicitado'] ?? null,
        ], fn ($valor) => filled($valor));

        if ($datosNuevos) {
            $conversation = $this->altoparque->updateConversation($conversation->id(), $datosNuevos);
        }

        match ($resultado['accion'] ?? 'continuar') {
            'ofrecer_visita' => $this->altoparque->updateConversation($conversation->id(), ['estado_conversacion' => 'esperando_agenda_visita']),
            'ofrecer_foto' => $this->altoparque->updateConversation($conversation->id(), ['estado_conversacion' => 'esperando_cotizacion_foto']),
            'derivar' => $this->derivarAHumano($conversation, $customer, motivo: 'El cliente confirmó que quiere agendar la visita.'),
            default => null,
        };
    }

    /**
     * Envía un mensaje de Claudia por WhatsApp y recién si el envío funcionó
     * lo guarda en la API central — evita registrar un mensaje "enviado" que
     * el cliente nunca recibió.
     */
    private function enviarYGuardar(RemoteConversation $conversation, RemoteCustomer $customer, string $mensaje): void
    {
        if (blank($customer->phone())) {
            return;
        }

        try {
            $this->whatsApp->sendTextMessage($customer->whatsappPhone(), $mensaje);
        } catch (Throwable $e) {
            Log::error('Claudia: no se pudo enviar la respuesta por WhatsApp.', ['error' => $e->getMessage()]);

            return;
        }

        $this->altoparque->createMessage($conversation->id(), [
            'remitente' => self::REMITENTE_CLAUDIA,
            'contenido' => $mensaje,
            'tipo' => 'texto',
            'enviado_en' => now()->toIso8601String(),
        ]);
    }

    /**
     * Deriva la conversación a un humano y, si recién ahora queda
     * "con_humano" sin nadie asignado, manda el aviso por email — réplica
     * del hook que antes vivía en WhatsappConversation::booted().
     */
    private function derivarAHumano(RemoteConversation $conversation, RemoteCustomer $customer, string $motivo): void
    {
        $yaEstabaConHumano = $conversation->estaConHumano();

        $actualizada = $this->altoparque->updateConversation($conversation->id(), [
            'estado_conversacion' => 'con_humano',
        ]);

        if (! $yaEstabaConHumano && blank($actualizada->asignadoA())) {
            Mail::to(self::ADMIN_EMAILS)->send(new AltoparqueConversationDerivedMailable(
                $actualizada,
                $customer,
                $motivo,
                $this->altoparque->panelUrlForConversation($actualizada->id()),
            ));
        }
    }

    private function resolverCliente(string $waId, array $contacts): RemoteCustomer
    {
        $nombreContacto = collect($contacts)->firstWhere('wa_id', $waId)['profile']['name'] ?? null;

        return $this->altoparque->upsertCustomer($waId, $nombreContacto);
    }

    /**
     * @return array{0: string, 1: string} [tipo, contenido]
     */
    private function extraerContenido(array $message): array
    {
        if (($message['type'] ?? null) === 'image' && isset($message['image']['id'])) {
            try {
                return ['imagen', $this->whatsApp->downloadMedia($message['image']['id'])];
            } catch (Throwable $e) {
                Log::error('WhatsApp webhook: no se pudo descargar una imagen entrante.', [
                    'error' => $e->getMessage(),
                    'media_id' => $message['image']['id'],
                ]);

                return ['texto', '[No se pudo descargar la imagen enviada por el cliente]'];
            }
        }

        return ['texto', $message['text']['body'] ?? ''];
    }

    /**
     * Encuentra la conversación activa del cliente o crea una nueva.
     * Si está "con_humano", Claudia sigue pausada — solo se guarda el
     * mensaje. Si estaba "cerrada", un mensaje nuevo la reactiva (ver
     * regla de negocio de "pausa automática" en la spec).
     */
    private function resolverConversacion(RemoteCustomer $customer): RemoteConversation
    {
        $conversation = $this->altoparque->latestConversation($customer->id());

        if (! $conversation) {
            return $this->altoparque->createConversation($customer->id());
        }

        if ($conversation->estaConHumano()) {
            return $conversation;
        }

        if ($conversation->estaCerrada()) {
            return $this->altoparque->updateConversation($conversation->id(), [
                'estado_conversacion' => 'claudia_atendiendo',
            ]);
        }

        return $conversation;
    }
}
