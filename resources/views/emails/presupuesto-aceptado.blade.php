<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto aceptado</title>
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
            <h1>✅ Presupuesto aceptado</h1>
            <p>AltoParque</p>
        </div>

        <div class="content">
            <div class="field">
                <span class="label">👤 Cliente:</span>
                <span class="value">{{ $order->customer->name }}</span>
            </div>

            @if ($order->category)
                <div class="field">
                    <span class="label">🔧 Servicio:</span>
                    <span class="value">{{ $order->category->name }}</span>
                </div>
            @endif

            <div class="field">
                <span class="label">💲 Precio final:</span>
                <span class="value">
                    {{ $order->final_price ? '$'.number_format($order->final_price, 2, ',', '.') : 'A confirmar' }}
                </span>
            </div>

            <div class="field">
                <span class="label">📅 Fecha de aceptación:</span>
                <span class="value">{{ $order->budget_accepted_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="field">
                <span class="label">💳 Método de pago preferido:</span>
                <span class="value">
                    @if (filled($order->payment_method_preference))
                        {{ collect($order->payment_method_preference)->map(fn (string $method) => \App\Models\ServiceOrder::PAYMENT_METHODS[$method] ?? $method)->implode(' + ') }}
                    @else
                        No especificó
                    @endif
                </span>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/admin/service-orders/'.$order->id.'/edit') }}" class="button">Ver orden en el panel</a>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responder.</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
