# Claudia (bot de WhatsApp con IA) — puesta en marcha

Este documento es la continuación práctica del port de "Claudia" (el bot de WhatsApp que ya funciona en `poda-de-altura-v2`) a este sitio. El código ya está completo y probado localmente; lo que falta es 100% configuración externa (Meta Business Manager + variables de entorno), no desarrollo.

## Qué se hizo (resumen)

Se portó una instancia **propia e independiente** de Claudia a `serviciodejardineria-v2` — no un bot compartido con poda-de-altura-v2. Cada sitio tiene su propia base de datos, su propio número de WhatsApp Business, su propio panel de administración y su propia clave de DeepSeek.

Se agregó:
- Migraciones: `whatsapp_conversations`, `whatsapp_messages` (con `wamid` para evitar duplicados).
- Modelos: `WhatsappConversation`, `WhatsappMessage`, y métodos nuevos en `Customer` (`findByWhatsappNumber`, `tieneNombre`, `whatsappConversations`).
- Servicios: `WhatsAppCloudApiService` (habla con la API de Meta), `WhatsAppInboundMessageService` (procesa cada mensaje entrante), `ClaudiaConversationService` (arma el prompt y llama a DeepSeek — los servicios que ofrece se toman en vivo de `Category::active()`, así que no hay que tocar el prompt cuando cambian las categorías).
- `WhatsAppWebhookController` (rutas `GET`/`POST /webhook/whatsapp`), el `Job` que procesa cada mensaje, y el `Mailable` que avisa por email cuando un caso se deriva a un humano.
- Panel Filament: **CRM → WhatsApp (Claudia)**, para ver conversaciones, responder manualmente y cerrar/reabrir casos.

Ya se probó localmente con Docker: el webhook recibe un mensaje simulado, crea el `Customer` y la conversación correctamente, y falla de forma controlada (logueada, sin romper nada) al intentar llamar a DeepSeek sin credenciales — que es exactamente lo que falta cargar.

## Paso 1 — Dar de alta el número de WhatsApp en Meta

1. Entrar a [Meta Business Manager](https://business.facebook.com/) (la misma cuenta donde ya está dado de alta el número de poda-de-altura-v2, si existe).
2. Ir a **WhatsApp → Introducción / API Setup** y crear una nueva app de WhatsApp Business Platform (o agregar un número nuevo a una app existente).
3. Agregar el número de teléfono que va a usar Claudia para este sitio (tiene que ser un número que **no** esté ya usado en WhatsApp normal ni en el bot de poda-de-altura-v2 — cada número Cloud API es independiente).
4. Completar la verificación del número (SMS o llamada).
5. Anotar estos 3 datos, que aparecen en el panel de la app:
   - **Token de acceso permanente** (no el temporal de 24hs — hay que generar uno de "System User" con permisos `whatsapp_business_messaging`).
   - **Phone Number ID** (no es el número de teléfono, es un ID interno que muestra el panel).
   - **App Secret** (en Configuración de la app → Básico).

## Paso 2 — Cargar las credenciales en el `.env` del servidor

En el `.env` de **producción/staging** de este sitio (no en este repo — el `.env` no se versiona), completar:

```
DEEPSEEK_API_KEY=            # puede ser la misma que usa poda-de-altura-v2, o una nueva
DEEPSEEK_MODEL=deepseek-v4-flash
DEEPSEEK_BASE_URL=https://api.deepseek.com

WHATSAPP_CLOUD_API_TOKEN=              # el token permanente del Paso 1
WHATSAPP_CLOUD_API_PHONE_NUMBER_ID=    # el Phone Number ID del Paso 1
WHATSAPP_CLOUD_API_VERSION=v21.0
WHATSAPP_CLOUD_API_VERIFY_TOKEN=       # inventar un string random y largo — es el que se usa en el Paso 3
WHATSAPP_CLOUD_API_APP_SECRET=         # el App Secret del Paso 1
```

Los placeholders vacíos ya están en `.env.example` y en `config/services.php` — no hace falta tocar código, solo completar valores.

Después de cargar los valores: `php artisan config:clear` en el servidor.

## Paso 3 — Registrar la URL del webhook en Meta

1. En la misma sección de la app de Meta (**WhatsApp → Configuration → Webhook**), pegar:
   - **URL de callback**: `https://serviciodejardineria.com.ar/webhook/whatsapp`
   - **Verify token**: el mismo valor que se puso en `WHATSAPP_CLOUD_API_VERIFY_TOKEN` en el Paso 2.
2. Meta va a hacer un `GET` a esa URL para verificarla — si el deploy ya tiene las credenciales cargadas, va a responder OK automáticamente (lo resuelve `WhatsAppWebhookController::verify()`).
3. Suscribirse al campo **`messages`** del webhook (es el único que necesita este bot).

## Paso 4 — Probar de punta a punta

1. Desde un celular, mandar un WhatsApp al número dado de alta.
2. Claudia debería responder sola en segundos (arranca con la presentación de Altoparque y los servicios de jardinería).
3. Entrar a `/admin` → **CRM → WhatsApp (Claudia)** para ver la conversación aparecer ahí en vivo.
4. Probar el flujo completo: dar nombre, zona y servicio → Claudia ofrece la visita sin costo → confirmar → la conversación pasa a "Con humano" y llega el email de aviso a `gerald@altoparque.com` / `jofretjofret@gmail.com`.
5. Desde el panel, responder manualmente y confirmar que el mensaje llega al WhatsApp real del celular de prueba.

## Notas

- El correo de aviso ("Claudia derivó un caso") va a las mismas casillas que usa poda-de-altura-v2 (`gerald@altoparque.com`, `jofretjofret@gmail.com`) — si el equipo de jardinería es distinto, cambiar la lista `ADMIN_EMAILS` en `app/Models/WhatsappConversation.php`.
- Si en algún momento se quiere ajustar qué zonas cubre Claudia acá, están en `app/Services/Claudia/ClaudiaConversationService.php` (`buildSystemPrompt()`) — hoy son las mismas localidades que ya usa el formulario de contacto del sitio.
- Esta instancia es independiente de la de poda-de-altura-v2: cada una tiene su propia base de datos, así que un cliente que le escribe a los dos números va a generar dos `Customer` separados, uno en cada sitio.
