@extends('layouts.app')

@section('meta_title', 'Conformidad del trabajo - AltoParque')
@section('meta_description', 'Confirmá la conformidad del trabajo realizado.')
@section('meta_robots', 'noindex, nofollow')

@php
    $order = $workOrder->serviceOrder;
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 p-6">
            <p class="text-gray-700"><span class="font-semibold text-gray-800">Orden de trabajo:</span> {{ $workOrder->work_order_number }}</p>
            <p class="text-gray-700"><span class="font-semibold text-gray-800">Cliente:</span> {{ $order->customer->name }}</p>
            <p class="text-gray-700"><span class="font-semibold text-gray-800">Dirección:</span> {{ $order->property?->full_address ?? '—' }}</p>
        </div>

        <div class="p-8 space-y-6">
            @if ($order->category)
                <div>
                    <p class="text-sm text-gray-500">Servicio</p>
                    <p class="text-lg font-medium text-gray-800">{{ $order->category->name }}</p>
                </div>
            @endif

            @if ($workOrder->checklistItems->isNotEmpty())
                <div>
                    <p class="text-sm text-gray-500 mb-2">Trabajo realizado</p>
                    <ul class="space-y-2">
                        @foreach ($workOrder->checklistItems as $item)
                            <li class="bg-gray-50 rounded-lg p-3 flex items-start gap-2">
                                <span class="mt-0.5">{{ $item->is_completed ? '✅' : '⬜' }}</span>
                                <div>
                                    <p class="text-gray-800">{{ $item->description ?: '—' }}</p>
                                    @if ($item->observations)
                                        <p class="text-sm text-gray-500 mt-1">{{ $item->observations }}</p>
                                    @endif
                                    @if ($item->includes_pickup)
                                        <p class="text-sm text-green-700 font-bold mt-1">Incluye retiro</p>
                                    @else
                                        <p class="text-sm text-gray-500 font-bold mt-1">No incluye retiro</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $whatsappMessage = 'Hola! Confirmo que el trabajo'
                    .($order->category ? ' de '.$order->category->name : '')
                    .' quedó bien. ¡Gracias!';
                $whatsappConfirmLink = 'https://api.whatsapp.com/send/?phone=5491171789529&text='
                    .urlencode($whatsappMessage).'&type=phone_number&app_absent=0';
            @endphp

            <div
                x-data="{
                    confirmed: {{ $workOrder->conformity_confirmed_at ? 'true' : 'false' }},
                    sending: false,
                    confirm() {
                        this.sending = true;

                        // Abrimos WhatsApp de forma sincrónica, en el mismo tick del
                        // click, para que el navegador no lo bloquee como pop-up
                        // (si esperáramos a que el fetch termine, la mayoría de los
                        // navegadores lo tratan como apertura no solicitada).
                        window.open('{{ $whatsappConfirmLink }}', '_blank');

                        fetch('{{ route('conformity.confirm', $workOrder->conformity_token) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                            .then(() => { this.confirmed = true; })
                            .finally(() => { this.sending = false; });
                    },
                }"
            >
                <template x-if="!confirmed">
                    <form method="POST" action="{{ route('conformity.confirm', $workOrder->conformity_token) }}" @submit.prevent="confirm()">
                        @csrf
                        <button
                            type="submit"
                            :disabled="sending"
                            class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-lg text-base disabled:opacity-60"
                        >
                            <span x-show="!sending">Confirmar conformidad</span>
                            <span x-show="sending" x-cloak>Procesando...</span>
                        </button>
                    </form>
                </template>

                <template x-if="confirmed">
                    <div class="bg-green-700 text-white rounded-lg p-4 text-center font-semibold">
                        ✅ ¡Gracias! Tu conformidad fue registrada.
                    </div>
                </template>
            </div>

            <p class="text-center text-sm text-gray-500">
                Cualquier consulta, respondé el mensaje que te enviamos. ¡Gracias por confiar en AltoParque!
            </p>
        </div>
    </div>
</div>
@endsection
