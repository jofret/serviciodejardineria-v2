<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Contacto</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #166534; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #166534; }
        .value { margin-left: 10px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
        .button { background-color: #166534; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📬 Nuevo Contacto</h1>
            <p>Limpieza de Terrenos</p>
        </div>
        
        <div class="content">
            <div class="field">
                <span class="label">👤 Nombre:</span>
                <span class="value">{{ $customer->name }}</span>
            </div>
            
            <div class="field">
                <span class="label">📞 Teléfono:</span>
                <span class="value">{{ $customer->phone }}</span>
            </div>
            
            <div class="field">
                <span class="label">✉️ Email:</span>
                <span class="value">{{ $customer->email }}</span>
            </div>
            
            <div class="field">
                <span class="label">📍 Zona:</span>
                <span class="value">
                    @if($customer->zona_principal === 'Otra')
                        {{ $customer->otra_zona }}
                    @else
                        {{ $customer->zona_principal }} - {{ $customer->partido }}
                    @endif
                </span>
            </div>
            
            <div class="field">
                <span class="label">🔧 Servicio:</span>
                <span class="value">{{ $customer->servicio_interes }}</span>
            </div>
            
            <div class="field">
                <span class="label">💬 Mensaje:</span>
                <div class="value">{{ $customer->mensaje_inicial }}</div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/admin/customers/' . $customer->id) }}" class="button">
                    Ver en el panel
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático, por favor no responder.</p>
            <p>&copy; {{ date('Y') }} Limpieza de Terrenos</p>
        </div>
    </div>
</body>
</html>