<?php

namespace App\Services\WhatsApp;

use App\Models\Customer;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\Claudia\ClaudiaConversationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppInboundMessageService
{
    /**
     * Instalación de un solo sitio (a diferencia de poda-de-altura-v2, acá
     * no hace falta detectar de qué dominio vino el cliente): todo lo que
     * llega a este webhook es de serviciodejardineria.com.ar.
     */
    private const SITIO_ORIGEN = 'serviciodejardineria.com.ar';

    public function __construct(
        private WhatsAppCloudApiService $whatsApp,
        private ClaudiaConversationService $claudia,
    ) {
    }

    /**
     * Procesa un mensaje entrante de WhatsApp: crea/actualiza el Customer,
     * resuelve la conversación activa, guarda el mensaje y, si corresponde,
     * hace que Claudia responda.
     *
     * @param  array<string, mixed>  $message
     * @param  array<int, array<string, mixed>>  $contacts
     */
    public function handle(array $message, array $contacts): void
    {
        $wamid = $message['id'] ?? null;

        if (filled($wamid) && WhatsappMessage::where('wamid', $wamid)->exists()) {
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
        $estadoAntes = $conversation->estado_conversacion;

        $conversation->messages()->create([
            'wamid' => $wamid,
            'remitente' => WhatsappMessage::REMITENTE_CLIENTE,
            'contenido' => $contenido,
            'tipo' => $tipo,
            'enviado_en' => isset($message['timestamp']) ? now()->setTimestamp((int) $message['timestamp']) : now(),
        ]);

        if ($tipo === 'imagen') {
            $conversation->update(['foto_path' => $contenido]);
        }

        if ($conversation->estaConHumano()) {
            // Pausada: ya se guardó el mensaje, Claudia no interviene.
            return;
        }

        // Recibir la foto que se había pedido deriva directo, sin pasar por
        // Claude: es una regla de negocio fija, no algo a interpretar.
        if ($tipo === 'imagen' && $estadoAntes === 'esperando_cotizacion_foto') {
            $this->derivarPorFotoRecibida($conversation);

            return;
        }

        $this->responderConClaudia($conversation);
    }

    private function derivarPorFotoRecibida(WhatsappConversation $conversation): void
    {
        $this->enviarYGuardar(
            $conversation,
            'Perfecto, ya tengo la foto. Ahora te paso con alguien del equipo para que te pase un precio. 🌿'
        );

        $conversation->derivarAHumano(motivo: 'El cliente mandó la foto pedida para la cotización.');
    }

    private function responderConClaudia(WhatsappConversation $conversation): void
    {
        try {
            $resultado = $this->claudia->generarRespuesta($conversation);
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
                $resultado = $this->claudia->generarRespuesta($conversation);
            } catch (Throwable $e) {
                Log::error('Claudia: falló el reintento tras mensaje vacío.', ['error' => $e->getMessage()]);

                return;
            }

            if (blank($resultado['mensaje'] ?? null)) {
                Log::error('Claudia: DeepSeek volvió a devolver un mensaje vacío en el reintento.');

                return;
            }
        }

        $this->enviarYGuardar($conversation, $resultado['mensaje']);

        $customer = $conversation->customer;

        if (filled($resultado['nombre_cliente'] ?? null) && ! $customer->tieneNombre()) {
            $customer->update(['name' => $resultado['nombre_cliente']]);
        }

        $datosNuevos = array_filter([
            'zona' => $resultado['zona'] ?? null,
            'servicio_solicitado' => $resultado['servicio_solicitado'] ?? null,
        ], fn ($valor) => filled($valor));

        if ($datosNuevos) {
            $conversation->update($datosNuevos);
        }

        match ($resultado['accion'] ?? 'continuar') {
            'ofrecer_visita' => $conversation->update(['estado_conversacion' => 'esperando_agenda_visita']),
            'ofrecer_foto' => $conversation->update(['estado_conversacion' => 'esperando_cotizacion_foto']),
            'derivar' => $conversation->derivarAHumano(motivo: 'El cliente confirmó que quiere agendar la visita.'),
            default => null,
        };
    }

    /**
     * Envía un mensaje de Claudia por WhatsApp y recién si el envío funcionó
     * lo guarda en whatsapp_messages — evita registrar un mensaje "enviado"
     * que el cliente nunca recibió.
     */
    private function enviarYGuardar(WhatsappConversation $conversation, string $mensaje): void
    {
        if (blank($conversation->customer->phone)) {
            return;
        }

        try {
            $this->whatsApp->sendTextMessage($conversation->customer->whatsappPhone(), $mensaje);
        } catch (Throwable $e) {
            Log::error('Claudia: no se pudo enviar la respuesta por WhatsApp.', ['error' => $e->getMessage()]);

            return;
        }

        $conversation->messages()->create([
            'remitente' => WhatsappMessage::REMITENTE_CLAUDIA,
            'contenido' => $mensaje,
            'tipo' => 'texto',
            'enviado_en' => now(),
        ]);
    }

    private function resolverCliente(string $waId, array $contacts): Customer
    {
        $nombreContacto = collect($contacts)->firstWhere('wa_id', $waId)['profile']['name'] ?? null;

        $customer = Customer::findByWhatsappNumber($waId);

        if (! $customer) {
            // 'name' es NOT NULL en customers — WhatsApp no siempre manda el
            // nombre de perfil del contacto, así que hace falta un placeholder
            // hasta que Claudia se lo pida y lo actualice en la conversación.
            return Customer::create([
                'name' => $nombreContacto ?? Customer::NOMBRE_PENDIENTE,
                'phone' => $waId,
                'status' => 'potencial',
                'preferred_contact' => 'whatsapp',
            ]);
        }

        if (! $customer->tieneNombre() && filled($nombreContacto)) {
            $customer->update(['name' => $nombreContacto]);
        }

        return $customer;
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
    private function resolverConversacion(Customer $customer): WhatsappConversation
    {
        $conversation = $customer->whatsappConversations()->latest()->first();

        if (! $conversation) {
            return $customer->whatsappConversations()->create([
                'sitio_origen' => self::SITIO_ORIGEN,
            ]);
        }

        if ($conversation->estaConHumano()) {
            return $conversation;
        }

        if ($conversation->estado_conversacion === 'cerrada') {
            $conversation->update(['estado_conversacion' => 'claudia_atendiendo']);
        }

        return $conversation;
    }
}
