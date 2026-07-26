<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo comentario de cliente</title>
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
            <h1>💬 Nuevo comentario de cliente</h1>
            <p>AltoParque</p>
        </div>

        <div class="content">
            <div class="field">
                <span class="label">👤 Cliente:</span>
                <span class="value">{{ $survey->customer->name }}</span>
            </div>

            <div class="field">
                <span class="label">📞 Teléfono:</span>
                <span class="value">{{ $survey->customer->phone }}</span>
            </div>

            @if(filled($survey->gender))
            <div class="field">
                <span class="label">Género:</span>
                <span class="value">{{ $survey->gender }}</span>
            </div>
            @endif

            @if(filled($survey->occupation))
            <div class="field">
                <span class="label">Ocupación:</span>
                <span class="value">{{ $survey->occupation }}</span>
            </div>
            @endif

            @if(filled($survey->birthday_day) || filled($survey->birthday_month))
            <div class="field">
                <span class="label">🎂 Cumpleaños:</span>
                <span class="value">{{ $survey->birthday_day }} {{ $survey->birthday_month }}</span>
            </div>
            @endif

            <div class="field">
                <span class="label">💬 Comentario:</span>
                <div class="value">{{ $survey->comment }}</div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/admin/surveys/'.$survey->id.'/edit') }}" class="button" style="background-color: #166534; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Revisar y publicar
                </a>
            </div>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responder.</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
