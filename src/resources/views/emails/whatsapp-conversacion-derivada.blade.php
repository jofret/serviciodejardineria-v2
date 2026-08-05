<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claudia derivó un caso de WhatsApp</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #166534; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #166534; }
        .value { margin-left: 10px; }
        .button { background-color: #166534; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🙋 Claudia derivó un caso</h1>
            <p>Servicio de Jardinería - AltoParque</p>
        </div>

        <div class="content">
            <div class="field">
                <span class="label">👤 Cliente:</span>
                <span class="value">{{ $conversation->customer->name ?: 'Sin nombre' }}</span>
            </div>

            <div class="field">
                <span class="label">📞 Teléfono:</span>
                <span class="value">{{ $conversation->customer->phone }}</span>
            </div>

            @if ($conversation->zona)
                <div class="field">
                    <span class="label">📍 Zona:</span>
                    <span class="value">{{ $conversation->zona }}</span>
                </div>
            @endif

            @if ($conversation->servicio_solicitado)
                <div class="field">
                    <span class="label">🌿 Servicio:</span>
                    <span class="value">{{ $conversation->servicio_solicitado }}</span>
                </div>
            @endif

            <div class="field">
                <span class="label">❓ Motivo:</span>
                <span class="value">{{ $motivo }}</span>
            </div>

            <p style="margin-top: 20px; color: #666;">
                Nadie tomó todavía esta conversación. Entrá al panel para responderle al cliente por WhatsApp.
            </p>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ $panelUrl }}" class="button">Atender conversación</a>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responder.</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
