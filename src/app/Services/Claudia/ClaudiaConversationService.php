<?php

namespace App\Services\Claudia;

use App\Models\Category;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ClaudiaConversationService
{
    private const TOOL_NAME = 'responder_cliente';

    private const ESTADOS_LABEL = [
        'claudia_atendiendo' => 'Charlando normalmente, todavía juntando datos.',
        'esperando_agenda_visita' => 'Le ofreciste agendar una visita sin costo y estás esperando que confirme si quiere o no.',
        'esperando_cotizacion_foto' => 'Aceptaste que mande una foto para pasársela al encargado (él decide si alcanza sin visita, vos no cotizás) y estás esperando que la mande (si en vez de eso escribe texto, seguí la charla normal).',
    ];

    /**
     * Le pide a Claudia (motor DeepSeek, API compatible con OpenAI) la
     * próxima respuesta para el cliente, junto con los datos nuevos que haya
     * podido extraer y la acción a tomar.
     *
     * @return array{mensaje: string, nombre_cliente: ?string, zona: ?string, servicio_solicitado: ?string, accion: string}
     */
    public function generarRespuesta(WhatsappConversation $conversation): array
    {
        $conversation->loadMissing('customer', 'messages');

        $apiKey = config('services.deepseek.key');

        if (blank($apiKey)) {
            throw new RuntimeException('DeepSeek no está configurado (falta DEEPSEEK_API_KEY).');
        }

        $baseUrl = rtrim(config('services.deepseek.base_url', 'https://api.deepseek.com'), '/');

        $response = Http::withToken($apiKey)
            ->post("{$baseUrl}/chat/completions", [
                'model' => config('services.deepseek.model', 'deepseek-v4-flash'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->buildSystemPrompt($conversation)],
                    ...$this->buildMessages($conversation),
                ],
                'tools' => [$this->buildTool()],
                // DeepSeek tiene "modo pensamiento" activado por default, y
                // ese modo no permite forzar una tool específica en
                // tool_choice — hay que desactivarlo para poder garantizar
                // que la respuesta siempre venga como tool_call.
                'thinking' => ['type' => 'disabled'],
                'tool_choice' => [
                    'type' => 'function',
                    'function' => ['name' => self::TOOL_NAME],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('error.message') ?? 'Error desconocido al llamar a la API de DeepSeek.'
            );
        }

        $toolCalls = $response->json('choices.0.message.tool_calls', []);

        foreach ($toolCalls as $toolCall) {
            if (($toolCall['function']['name'] ?? null) === self::TOOL_NAME) {
                // A diferencia de Claude, acá los argumentos vienen como un
                // string JSON, no como un array ya parseado.
                $argumentos = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];

                return array_merge([
                    'mensaje' => '',
                    'nombre_cliente' => null,
                    'zona' => null,
                    'servicio_solicitado' => null,
                    'accion' => 'continuar',
                ], $argumentos);
            }
        }

        Log::error('Claudia: la respuesta de DeepSeek no incluyó el tool_call esperado.', [
            'finish_reason' => $response->json('choices.0.finish_reason'),
        ]);

        return [
            'mensaje' => 'Perdón, tuve un problema para responderte. En un rato te escribe alguien del equipo.',
            'nombre_cliente' => null,
            'zona' => null,
            'servicio_solicitado' => null,
            'accion' => 'continuar',
        ];
    }

    private function buildSystemPrompt(WhatsappConversation $conversation): string
    {
        $customer = $conversation->customer;

        $nombre = $customer->tieneNombre() ? $customer->name : 'todavía no lo dio';
        $zona = filled($conversation->zona) ? $conversation->zona : 'todavía no la dio';
        $servicio = filled($conversation->servicio_solicitado) ? $conversation->servicio_solicitado : 'todavía no lo dijo';
        $estadoDescripcion = self::ESTADOS_LABEL[$conversation->estado_conversacion] ?? 'Charlando normalmente.';

        // Se arma desde la BD real del sitio (no hardcodeado) para que la
        // lista de servicios que Claudia ofrece nunca quede desactualizada
        // respecto a las categorías activas cargadas en el panel.
        $servicios = Category::active()->orderBy('order')->pluck('name')->implode(', ');

        return <<<PROMPT
            Sos Claudia, la asistente virtual de WhatsApp de Altoparque, una empresa que ofrece servicio de jardinería: corte de pasto con tractor cortacésped, poda de árboles y arbustos, desmalezado y limpieza de terrenos, y mantenimiento de jardines en general, en la zona metropolitana de Buenos Aires (Argentina).

            Tu trabajo es atender la consulta inicial de un cliente que escribió por WhatsApp, de forma cordial y breve (es un chat de WhatsApp, no un email). Nunca digas que sos una inteligencia artificial ni un bot, simplemente respondé como Claudia.

            Servicios que ofrece Altoparque hoy: {$servicios}.

            Si es el primer mensaje de la conversación, presentate más o menos así (podés variar la redacción, pero mantené la idea y mencioná los servicios de arriba):
            "¡Hola! 😊 ¿Cómo estás? Acá te cuento un poco. Somos Altoparque, especialistas en corte de pasto, poda, desmalezado y mantenimiento de jardines. Trabajamos en CABA y parte del conurbano (zona norte y oeste del GBA). ¿En qué zona estás y qué tipo de trabajo necesitás hacer? Así te puedo orientar mejor. 🌿"

            Datos que ya tenés sobre este cliente (NUNCA vuelvas a pedir un dato que ya está acá):
            - Nombre: {$nombre}
            - Zona: {$zona}
            - Servicio que le interesa: {$servicio}

            Estado actual de la conversación: {$estadoDescripcion}

            Zonas que atendemos (Altoparque SOLO trabaja acá — cualquier otra zona está fuera de cobertura):
            - CABA: Palermo, Belgrano, Recoleta, Puerto Madero, Caballito, Almagro, Villa Crespo, Colegiales, Núñez, Saavedra, Villa Urquiza, Villa Devoto.
            - Zona Norte del GBA (partidos completos, con todas sus localidades y barrios): Pilar, Escobar, Tigre, San Isidro, Vicente López, San Fernando, San Martín, Malvinas Argentinas, José C. Paz.
            - Zona Oeste del GBA (partidos completos, con todas sus localidades y barrios): Moreno, Merlo, Morón, Ituzaingó, Hurlingham, La Matanza, Tres de Febrero, San Miguel.
            - NO cubrimos el resto de CABA ni otros barrios/partidos que no estén en las listas de arriba, ni la Zona Sur del GBA (La Plata, Avellaneda, Lanús, Quilmes, Berazategui, etc.), ni el interior del país.

            Reglas:
            - Pedí un solo dato faltante por mensaje (nombre, zona, o qué servicio necesita), nunca varios juntos.
            - Antes de ofrecer la visita, confirmá que la zona que dio el cliente está dentro de la cobertura de arriba.
              - Si es ambigua o contradictoria (por ejemplo, nombra un barrio o localidad real pero lo asocia a una provincia o partido que no corresponde), NO asumas ni corrijas en silencio — pedile que aclare dónde está exactamente.
              - Si la zona claramente NO está en la cobertura, avisale directamente y con amabilidad que por ahora no llegamos hasta ahí, y NO ofrezcas coordinar visita ni nada (acción "continuar").
              - Solo seguí adelante con la zona si está clara y dentro de la cobertura.
            - Recién ofrecé la visita cuando tengas los 3 datos: nombre, zona (ya confirmada dentro de cobertura) y servicio (los 3 son obligatorios, ninguno es opcional). Si falta alguno, seguí pidiéndolo de a uno antes de avanzar.
            - Una vez que tenés nombre, zona y servicio, explicá que Altoparque necesita hacer una visita SIN COSTO para poder cotizar con precisión, y preguntale si le gustaría coordinarla. Ahí usá la acción "ofrecer_visita".
            - Ni bien el cliente cuenta qué trabajo necesita, explicale (con tus palabras, sin sonar repetitiva) que para este tipo de trabajo siempre hace falta una visita para poder evaluar y cotizar con precisión. Podés mencionar que una foto ayuda a tener una idea aproximada, pero dejá siempre claro que la foto NO reemplaza la visita — la visita hace falta sí o sí, de todas formas.
            - Si el cliente insiste en que es algo chico (un cantero, un arbolito, un fondo chico, etc.) y te pide que le cotices solo con una foto, NO decidas vos si eso alcanza o no para saltear la visita. Aceptá que mande la foto (acción "ofrecer_foto"), pero aclarale explícitamente que se la vas a pasar al encargado, y que es él quien evalúa si es posible darle un precio sin la visita — nunca prometas un precio ni des a entender que la foto sola alcanza seguro.
            - Nunca decidas por tu cuenta si un trabajo "es simple" o si amerita saltarse la visita: esa evaluación es siempre del encargado humano, una vez que reciba la foto. Tu rol es juntar la foto y derivar, no evaluar el trabajo ni prometer precios.
            - Si el estado es "esperando la confirmación de la visita" y el cliente confirma que sí quiere agendar, respondé avisando que lo derivás con un compañero del equipo, y usá la acción "derivar". Si en cambio duda o dice que no por ahora, seguí la charla normal con la acción "continuar" (por ejemplo, ofrecele la opción de la foto si todavía no se la ofreciste).
            - Nunca inventes precios ni fechas de visita — eso lo coordina el equipo humano.
            - Mensajes cortos, en español rioplatense, tono cordial y profesional. Podés usar algún emoji sutil (🌿🌳), sin abusar.
            PROMPT;
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function buildMessages(WhatsappConversation $conversation): array
    {
        $messages = [];

        foreach ($conversation->messages as $message) {
            $role = $message->remitente === WhatsappMessage::REMITENTE_CLIENTE ? 'user' : 'assistant';

            $contenido = $message->tipo === 'imagen'
                ? '[el cliente mandó una foto]'
                : $message->contenido;

            $messages[] = ['role' => $role, 'content' => $contenido];
        }

        return $messages;
    }

    private function buildTool(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => self::TOOL_NAME,
                'description' => 'Genera la respuesta para el cliente de WhatsApp y actualiza los datos nuevos que se hayan podido extraer de su último mensaje.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'mensaje' => [
                            'type' => 'string',
                            'description' => 'El mensaje de WhatsApp para enviarle al cliente ahora mismo.',
                        ],
                        'nombre_cliente' => [
                            'type' => ['string', 'null'],
                            'description' => 'El nombre del cliente, SOLO si lo dio en este mensaje y todavía no se conocía. Si ya se conocía o no lo dio, null.',
                        ],
                        'zona' => [
                            'type' => ['string', 'null'],
                            'description' => 'La zona o barrio del cliente, SOLO si la dio en este mensaje y todavía no se conocía. Si no, null.',
                        ],
                        'servicio_solicitado' => [
                            'type' => ['string', 'null'],
                            'description' => 'El servicio que pide el cliente, SOLO si lo dio en este mensaje y todavía no se conocía. Si no, null.',
                        ],
                        'accion' => [
                            'type' => 'string',
                            'enum' => ['continuar', 'ofrecer_visita', 'ofrecer_foto', 'derivar'],
                            'description' => 'continuar: seguir la charla. ofrecer_visita: recién ahora se explica formalmente que hace falta una visita sin costo para cotizar (una vez que ya tenés nombre, zona y servicio). ofrecer_foto: el cliente insistió en que le cotices solo con una foto (aunque ya le dijiste que la visita es necesaria) y aceptaste que la mande — dejando claro que la evalúa el encargado, nunca que vos decidís o prometés un precio. derivar: el cliente confirmó que quiere agendar la visita — hay que pasarlo a un humano del equipo.',
                        ],
                    ],
                    'required' => ['mensaje', 'accion'],
                ],
            ],
        ];
    }
}
