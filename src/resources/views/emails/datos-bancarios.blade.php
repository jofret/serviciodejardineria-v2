<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos para la transferencia</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #166534; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .account { background-color: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .account-name { font-weight: bold; color: #166534; margin-bottom: 8px; }
        .field { margin-bottom: 4px; }
        .label { font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏦 Datos para la transferencia</h1>
            <p>AltoParque</p>
        </div>

        <div class="content">
            <p>Hola {{ $workOrder->serviceOrder->customer->name }}!</p>
            <p>Te dejamos los datos de la cuenta{{ $bankAccounts->count() > 1 ? 's' : '' }} para que puedas hacer la transferencia:</p>

            @foreach ($bankAccounts as $bankAccount)
                <div class="account">
                    <p class="account-name">{{ $bankAccount->name }}</p>
                    <p class="field"><span class="label">Banco:</span> {{ $bankAccount->bank_name }}</p>
                    <p class="field"><span class="label">Titular:</span> {{ $bankAccount->account_holder }}</p>
                    <p class="field"><span class="label">CBU:</span> {{ $bankAccount->cbu }}</p>
                    @if ($bankAccount->cbu_alias)
                        <p class="field"><span class="label">Alias:</span> {{ $bankAccount->cbu_alias }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="footer">
            <p>¡Gracias por confiar en AltoParque!</p>
            <p>&copy; {{ date('Y') }} AltoParque</p>
        </div>
    </div>
</body>
</html>
