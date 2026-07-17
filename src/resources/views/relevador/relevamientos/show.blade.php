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
        @if ($relevamiento->serviceOrder)
            <div><dt class="inline font-medium text-gray-700">Fecha programada:</dt> <dd class="inline">{{ optional($relevamiento->serviceOrder->work_date)->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="inline font-medium text-gray-700">Franja horaria:</dt> <dd class="inline">{{ \App\Models\ServiceOrder::TIME_SLOTS[$relevamiento->serviceOrder->time_slot] ?? '—' }}</dd></div>
            <div><dt class="inline font-medium text-gray-700">Estado de la orden:</dt> <dd class="inline">{{ (\App\Models\ServiceOrder::PIPELINE_STATUSES + \App\Models\ServiceOrder::OTHER_STATUSES)[$relevamiento->serviceOrder->status] ?? $relevamiento->serviceOrder->status }}</dd></div>
        @else
            <div><dt class="inline font-medium text-gray-700">Fecha programada:</dt> <dd class="inline">{{ optional($relevamiento->scheduled_date)->format('d/m/Y') ?? '—' }}</dd></div>
        @endif
        <div><dt class="inline font-medium text-gray-700">Estado del relevamiento:</dt> <dd class="inline">{{ $relevamiento->status === 'enviado' ? 'Enviado' : 'Pendiente' }}</dd></div>
    </dl>
</div>

@if ($relevamiento->status === 'pendiente')
    @include('relevador.relevamientos._form')
@else
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-4">
        <h2 class="font-semibold text-gray-800">Relevamiento enviado</h2>

        <div class="text-sm text-gray-600 space-y-1">
            <p><span class="font-medium text-gray-700">Tipo:</span> {{ $relevamiento->property->property_type_label ?? '—' }}</p>
            <p><span class="font-medium text-gray-700">Superficie:</span> {{ $relevamiento->property->total_area ? $relevamiento->property->total_area.' m²' : '—' }}</p>
        </div>

        @foreach ([
            'garden_areas' => ['¿Tiene jardín?', $relevamiento->property->has_garden, fn ($row) => ($row['name'] ?? '—').($row['size'] ?? null ? ' — '.$row['size'].' m²' : '')],
            'pools' => ['¿Tiene piscina?', $relevamiento->property->has_pool, fn ($row) => \App\Models\Property::POOL_TYPES[$row['type'] ?? null] ?? ($row['type'] ?? '—')],
            'trees' => ['¿Tiene árboles?', $relevamiento->property->has_trees, fn ($row) => ($row['species'] ?? '—').' x'.($row['quantity'] ?? 1)],
            'plants' => ['¿Tiene plantas?', $relevamiento->property->has_plants, fn ($row) => ($row['species'] ?? '—').' x'.($row['quantity'] ?? 1)],
            'sport_areas' => ['¿Tiene áreas deportivas?', $relevamiento->property->has_sport_areas, fn ($row) => \App\Models\Property::SPORT_AREA_TYPES[$row['type'] ?? null] ?? ($row['type'] ?? '—')],
        ] as $field => [$label, $has, $formatter])
            @if ($has)
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-sm font-medium text-gray-700 mb-1">{{ $label }}</p>
                    <ul class="text-sm text-gray-600 list-disc list-inside">
                        @foreach ($relevamiento->property->{$field} ?? [] as $row)
                            <li>{{ $formatter($row) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach

        @if ($relevamiento->property->tags->isNotEmpty())
            <div class="pt-2 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-700 mb-1">Tags</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($relevamiento->property->tags as $tag)
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">{{ $tag->name }}</span>
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
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($relevamiento->getMedia('photos') as $photo)
                        <a href="{{ $photo->getUrl() }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                            <img src="{{ $photo->getUrl() }}" alt="Foto de la visita" class="w-full h-full object-cover">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
@endsection
