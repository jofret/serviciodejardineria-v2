@extends('relevador.layout')

@section('title', 'Mis visitas')

@section('content')
<h1 class="text-xl font-bold text-gray-800 mb-4">Mis visitas</h1>

<div class="flex gap-2 mb-4">
    @php
        $tabs = ['pendiente' => 'Pendientes', 'enviado' => 'Enviados', 'todos' => 'Todos'];
    @endphp
    @foreach ($tabs as $value => $label)
        <a href="{{ route('relevador.dashboard', ['estado' => $value]) }}"
           class="px-4 py-2 rounded-full text-sm font-medium {{ $estado === $value ? 'bg-green-700 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if ($relevamientos->isEmpty())
    <div class="text-center text-gray-500 py-16">
        No hay visitas {{ $estado !== 'todos' ? 'en este estado' : 'asignadas' }} por ahora.
    </div>
@else
    <div class="space-y-3">
        @foreach ($relevamientos as $relevamiento)
            <a href="{{ route('relevador.show', $relevamiento) }}"
               class="block bg-white rounded-xl shadow-sm border border-gray-100 p-4 active:bg-gray-50">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $relevamiento->property->property_type_label ?? 'Propiedad' }}</p>
                        <p class="text-sm text-gray-500">{{ $relevamiento->property->customer?->name }}</p>
                        <p class="text-sm text-gray-500">{{ $relevamiento->property->display_label }}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $relevamiento->submitted_at ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $relevamiento->submitted_at ? 'Enviado' : 'Pendiente' }}
                    </span>
                </div>
                @if ($relevamiento->serviceOrder?->work_date)
                    <p class="text-xs text-gray-400 mt-2">
                        📅 {{ $relevamiento->serviceOrder->work_date->format('d/m/Y') }}
                        @if ($relevamiento->serviceOrder->time_slot)
                            · {{ \App\Models\ServiceOrder::TIME_SLOTS[$relevamiento->serviceOrder->time_slot] ?? $relevamiento->serviceOrder->time_slot }}
                        @endif
                    </p>
                @elseif ($relevamiento->scheduled_date)
                    <p class="text-xs text-gray-400 mt-2">
                        📅 {{ $relevamiento->scheduled_date->format('d/m/Y') }}
                        @if ($relevamiento->scheduled_time_from)
                            · {{ \Illuminate\Support\Carbon::parse($relevamiento->scheduled_time_from)->format('H:i') }}
                            @if ($relevamiento->scheduled_time_to)
                                - {{ \Illuminate\Support\Carbon::parse($relevamiento->scheduled_time_to)->format('H:i') }}
                            @endif
                        @endif
                    </p>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection
