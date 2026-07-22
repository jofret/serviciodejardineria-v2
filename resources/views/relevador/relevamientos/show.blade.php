@extends('relevador.layout')

@section('title', $relevamiento->property->display_label)

@section('content')
<a href="{{ route('relevador.dashboard') }}" class="text-sm text-green-700 mb-4 inline-block">&larr; Volver</a>

@if (session('status'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm p-3">
        ✅ {{ session('status') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
    <div>
        <h1 class="text-lg font-bold text-gray-800">{{ $relevamiento->property->property_type_label ?? 'Propiedad' }}</h1>
        <p class="text-sm text-gray-500">{{ $relevamiento->property->customer?->name }}</p>
    </div>

    <dl class="text-sm text-gray-600 space-y-1">
        <div><dt class="inline font-medium text-gray-700">Dirección:</dt> <dd class="inline">{{ $relevamiento->property->address ?? '—' }}</dd></div>
        <div><dt class="inline font-medium text-gray-700">Zona:</dt> <dd class="inline">{{ $relevamiento->property->zone ?? '—' }}</dd></div>
        @if ($relevamiento->service_type_label)
            <div><dt class="inline font-medium text-gray-700">Tipo de servicio:</dt> <dd class="inline font-bold text-base text-gray-800">{{ $relevamiento->service_type_label }}</dd></div>
        @endif
        @if ($relevamiento->serviceOrder)
            <div><dt class="inline font-medium text-gray-700">Fecha programada:</dt> <dd class="inline">{{ optional($relevamiento->serviceOrder->work_date)->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="inline font-medium text-gray-700">Franja horaria:</dt> <dd class="inline">{{ \App\Models\ServiceOrder::TIME_SLOTS[$relevamiento->serviceOrder->time_slot] ?? '—' }}</dd></div>
            <div><dt class="inline font-medium text-gray-700">Estado de la orden:</dt> <dd class="inline">{{ (\App\Models\ServiceOrder::PIPELINE_STATUSES + \App\Models\ServiceOrder::OTHER_STATUSES)[$relevamiento->serviceOrder->status] ?? $relevamiento->serviceOrder->status }}</dd></div>
        @else
            <div><dt class="inline font-medium text-gray-700">Fecha programada:</dt> <dd class="inline">{{ optional($relevamiento->scheduled_date)->format('d/m/Y') ?? '—' }}</dd></div>
            @if ($relevamiento->scheduled_time_from)
                <div>
                    <dt class="inline font-medium text-gray-700">Horario:</dt>
                    <dd class="inline">
                        {{ \Illuminate\Support\Carbon::parse($relevamiento->scheduled_time_from)->format('H:i') }}
                        @if ($relevamiento->scheduled_time_to)
                            - {{ \Illuminate\Support\Carbon::parse($relevamiento->scheduled_time_to)->format('H:i') }}
                        @endif
                    </dd>
                </div>
            @endif
        @endif
        <div><dt class="inline font-medium text-gray-700">Estado del relevamiento:</dt> <dd class="inline">{{ $relevamiento->submitted_at ? 'Enviado' : 'Pendiente' }}</dd></div>
    </dl>
</div>

@if (! $relevamiento->submitted_at)
    @include('relevador.relevamientos._form')
@else
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-4">
        <h2 class="font-semibold text-gray-800">Relevamiento Enviado</h2>

        <div class="text-sm text-gray-600 space-y-1">
            <p><span class="font-medium text-gray-700">Superficie:</span> {{ $relevamiento->property->total_area ? $relevamiento->property->total_area.' m²' : '—' }}</p>
            <p><span class="font-medium text-gray-700">Precio Estimativo:</span> {{ $relevamiento->estimated_price ? '$'.number_format($relevamiento->estimated_price, 2, ',', '.') : '—' }}</p>
            <p><span class="font-medium text-gray-700">Trabajadores para la Obra:</span> {{ $relevamiento->workers_count ?? '—' }}</p>
            <p><span class="font-medium text-gray-700">Duración Aproximada de la Obra:</span> {{ $relevamiento->estimated_duration_days ? $relevamiento->estimated_duration_days.' día(s)' : '—' }}</p>
        </div>

        @if ($relevamiento->workItems->isNotEmpty())
            <div class="pt-2 border-t border-gray-100 space-y-3">
                <p class="text-sm font-medium text-gray-700">Trabajo a Realizar</p>
                @foreach ($relevamiento->workItems as $item)
                    <div class="bg-gray-50 rounded-lg p-3 space-y-1">
                        <p class="text-sm text-gray-800">{{ $item->description ?: '—' }}</p>
                        @if ($item->observations)
                            <p class="text-xs text-gray-500">{{ $item->observations }}</p>
                        @endif
                        @if ($item->includes_pickup)
                            <p class="text-xs text-green-700 font-medium">Incluye retiro</p>
                        @endif
                        @if ($item->getMedia('photos')->isNotEmpty())
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
                                @foreach ($item->getMedia('photos') as $photo)
                                    <a href="{{ $photo->getUrl() }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                                        <img src="{{ $photo->getUrl() }}" alt="Foto del ítem" class="w-full h-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="pt-2 border-t border-gray-100 space-y-1">
            <p class="text-sm text-gray-600"><span class="font-medium text-gray-700">Requiere Cláusula de No-Repetición:</span> {{ $relevamiento->requires_non_compete_clause ? 'Sí' : 'No' }}</p>
        </div>

        @if ($relevamiento->workTools->isNotEmpty())
            <div class="pt-2 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-700 mb-1">Herramientas para Realizar el Trabajo</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($relevamiento->workTools as $tool)
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">{{ $tool->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($relevamiento->notes)
            <div class="pt-2 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-700 mb-1">Notas</p>
                <p class="text-sm text-gray-600">{{ $relevamiento->notes }}</p>
            </div>
        @endif

        @if ($relevamiento->getMedia('photos')->isNotEmpty())
            <div class="pt-2 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-700 mb-2">Fotos</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    @foreach ($relevamiento->getMedia('photos') as $photo)
                        <a href="{{ $photo->getUrl() }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                            <img src="{{ $photo->getUrl() }}" alt="Foto de la visita" class="w-full h-full object-cover">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($relevamiento->reopen_requested_at)
        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
            Solicitaste la reapertura el {{ $relevamiento->reopen_requested_at->format('d/m/Y H:i') }}. Vas a poder volver a editarlo cuando el administrador la apruebe.
        </div>
    @else
        <form method="POST" action="{{ route('relevador.reopen.request', $relevamiento) }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full bg-white border border-amber-300 text-amber-700 hover:bg-amber-50 font-semibold py-3 rounded-lg text-base">
                Solicitar reapertura
            </button>
        </form>
    @endif
@endif
@endsection
