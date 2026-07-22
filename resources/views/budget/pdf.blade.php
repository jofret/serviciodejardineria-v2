<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 13px; }
        .header { background-color: #166534; color: #fff; padding: 18px 24px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; color: #d1fae5; }
        .content { padding: 24px; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.info td { padding: 4px 0; vertical-align: top; }
        table.info td.label { width: 140px; color: #6b7280; }
        table.info td.value { color: #1f2937; font-weight: bold; }
        h2.section-title { font-size: 14px; color: #166534; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-top: 22px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items td, table.items th { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; font-size: 12px; }
        table.items th { background-color: #f9fafb; color: #374151; }
        .price-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px; text-align: center; margin-top: 22px; }
        .price-box .label { color: #166534; margin: 0; }
        .price-box .price { font-size: 26px; font-weight: bold; color: #166534; margin: 4px 0 0; }
        .price-box .notes { font-size: 12px; color: #4b5563; margin-top: 8px; }
        .footer { margin-top: 30px; font-size: 11px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Presupuesto</h1>
        <p>AltoParque</p>
    </div>

    <div class="content">
        <table class="info">
            <tr>
                <td class="label">Cliente</td>
                <td class="value">{{ $order->customer->name }}</td>
            </tr>
            <tr>
                <td class="label">Propiedad</td>
                <td class="value">{{ $order->property?->display_label ?? '—' }}</td>
            </tr>
            @if ($order->category)
                <tr>
                    <td class="label">Servicio</td>
                    <td class="value">{{ $order->category->name }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">Fecha</td>
                <td class="value">{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        @if ($order->relevamiento && $order->relevamiento->workItems->isNotEmpty())
            <h2 class="section-title">Trabajo a realizar</h2>
            <table class="items">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Observaciones</th>
                        <th>Retiro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->relevamiento->workItems as $item)
                        <tr>
                            <td>{{ $item->description ?: '—' }}</td>
                            <td>{{ $item->observations ?: '—' }}</td>
                            <td>
                                @if ($item->includes_pickup)
                                    <span style="color: #166534; font-weight: bold;">Incluye retiro</span>
                                @else
                                    <span style="color: #6b7280; font-weight: bold;">No incluye retiro</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="price-box">
            <p class="label">Precio final</p>
            <p class="price">
                {{ $order->final_price ? '$'.number_format($order->final_price, 2, ',', '.') : 'A confirmar' }}
            </p>
            @if ($order->final_price_notes)
                <p class="notes">{{ $order->final_price_notes }}</p>
            @endif
        </div>

        <p class="footer">Documento generado automáticamente por AltoParque — {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
