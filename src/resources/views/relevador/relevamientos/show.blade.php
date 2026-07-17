@extends('relevador.layout')

@section('title', $relevamiento->property->name)

@section('content')
<a href="{{ route('relevador.dashboard') }}" class="text-sm text-green-700 mb-4 inline-block">&larr; Volver</a>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
    <div>
        <h1 class="text-lg font-bold text-gray-800">{{ $relevamiento->property->name }}</h1>
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

    @if ($relevamiento->notes)
        <div class="pt-2 border-t border-gray-100">
            <p class="text-sm font-medium text-gray-700 mb-1">Notas</p>
            <p class="text-sm text-gray-600">{{ $relevamiento->notes }}</p>
        </div>
    @endif
</div>

<div class="mt-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl p-4 text-center">
    📋 Formulario de relevamiento — próximamente
</div>
@endsection
