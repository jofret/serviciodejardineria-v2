<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Quedó todo bien?</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #166534; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .button { background-color: #166534; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌿 ¿Quedó todo bien?</h1>
            <p>AltoParque</p>
        </div>

        <div class="content">
            <p>Hola {{ $workOrder->serviceOrder->customer->name }}!</p>
            <p>Terminamos el trabajo en tu propiedad. Nos gustaría que confirmes que quedó todo bien.</p>

            <p>Desde el siguiente enlace podés ver el detalle de lo realizado y confirmar tu conformidad:</p>

            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ $link }}" class="button">Confirmar conformidad</a>
            </div>
        </div>

        <div class="footer">
            <p>¡Gracias por confiar en AltoParque!</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
