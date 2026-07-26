<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu comentario ya está publicado</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #166534; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
        .button { background-color: #166534; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌿 ¡Ya está publicado!</h1>
            <p>AltoParque</p>
        </div>

        <div class="content">
            <p>¡Hola {{ $survey->customer->name }}!</p>
            <p>Muchas gracias por tu comentario, ya está publicado en nuestra web. Fue un placer haber trabajado con vos, y nos alegra mucho que hayas confiado en AltoParque.</p>

            @if($survey->post)
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('post.show', $survey->post) }}" class="button" style="background-color: #166534; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Ver mi comentario publicado
                </a>
            </div>
            @endif

            <p style="margin-top: 20px;">¡Cualquier cosa que necesites, contactanos! 🙌</p>
        </div>

        <div class="footer">
            <p>¡Gracias por confiar en AltoParque!</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
