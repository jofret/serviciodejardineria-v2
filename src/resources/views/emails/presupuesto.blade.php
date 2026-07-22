<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu presupuesto</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #166534; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .price-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .price { font-size: 28px; font-weight: bold; color: #166534; }
        .notes { color: #555; font-size: 14px; margin-top: 10px; }
        .button { background-color: #166534; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌿 Tu presupuesto</h1>
            <p>AltoParque</p>
        </div>

        <div class="content">
            <p>Hola {{ $order->customer->name }}!</p>
            <p>Ya está listo el presupuesto para tu servicio.</p>

            <div class="price-box">
                <p style="margin: 0; color: #166534;">Precio final</p>
                <p class="price">
                    {{ $order->final_price ? '$'.number_format($order->final_price, 2, ',', '.') : 'A confirmar' }}
                </p>
                @if ($order->final_price_notes)
                    <p class="notes">{{ $order->final_price_notes }}</p>
                @endif
            </div>

            <p>Desde el siguiente enlace podés ver el detalle completo, aceptar el presupuesto y descargarlo como documento:</p>

            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ $link }}" class="button">Ver presupuesto completo</a>
            </div>
        </div>

        <div class="footer">
            <p>¡Gracias por confiar en AltoParque!</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
