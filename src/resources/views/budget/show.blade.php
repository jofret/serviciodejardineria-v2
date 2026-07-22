@extends('layouts.app')

@section('meta_title', 'Tu presupuesto - AltoParque')
@section('meta_description', 'Presupuesto para tu servicio de jardinería.')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 p-6">
            <p class="text-gray-700"><span class="font-semibold text-gray-800">Presupuesto:</span> {{ $order->budget_number }}</p>
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

            @php
                $items = $order->relevamiento?->workItems ?? $order->items;
            @endphp

            @if ($items->isNotEmpty())
                <div>
                    <p class="text-sm text-gray-500 mb-2">Trabajo a realizar</p>
                    <ul class="space-y-3">
                        @foreach ($items as $item)
                            <li class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-800">{{ $item->description ?: '—' }}</p>
                                @if ($item->observations)
                                    <p class="text-sm text-gray-500 mt-1">{{ $item->observations }}</p>
                                @endif
                                @if ($item->includes_pickup)
                                    <p class="text-sm text-green-700 font-bold mt-1">Incluye retiro</p>
                                @else
                                    <p class="text-sm text-gray-500 font-bold mt-1">No incluye retiro</p>
                                @endif
                                @if ($item->getMedia('photos')->isNotEmpty())
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
                                        @foreach ($item->getMedia('photos') as $photo)
                                            <a href="{{ $photo->getUrl() }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                                                <img src="{{ $photo->getUrl() }}" alt="Foto del trabajo" class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($order->relevamiento && ($order->relevamiento->requires_non_compete_clause || $order->relevamiento->workers_count))
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-1 text-sm text-gray-700">
                    @if ($order->relevamiento->requires_non_compete_clause)
                        <p>Incluye Cláusula de No Repetición</p>
                    @endif
                    @if ($order->relevamiento->workers_count)
                        <p>Personal para la obra: {{ $order->relevamiento->workers_count }} trabajadores</p>
                    @endif
                </div>
            @endif

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 lg:text-right">
                <p class="text-sm font-bold text-gray-500">Precio final</p>
                <p class="text-3xl font-bold text-green-700 mt-1">
                    {{ $order->final_price ? '$'.number_format($order->final_price, 0, ',', '.').' ARS' : 'A confirmar' }}
                </p>
            </div>

            @php
                $whatsappMessage = 'Hola! Acepto el presupuesto número '.$order->budget_number
                    .($order->category ? ' para '.$order->category->name : '')
                    .'. ¡Gracias!';
                $whatsappConfirmLink = 'https://api.whatsapp.com/send/?phone=5491171789529&text='
                    .urlencode($whatsappMessage).'&type=phone_number&app_absent=0';
            @endphp

            <div
                x-data="{
                    accepted: {{ $order->budget_accepted_at ? 'true' : 'false' }},
                    sending: false,
                    paymentMethodError: false,
                    paymentMethods: @js($order->payment_method_preference ?? []),
                    accept() {
                        if (this.paymentMethods.length === 0) {
                            this.paymentMethodError = true;

                            return;
                        }

                        this.paymentMethodError = false;
                        this.sending = true;

                        // Abrimos WhatsApp de forma sincrónica, en el mismo tick del
                        // click, para que el navegador no lo bloquee como pop-up
                        // (si esperáramos a que el fetch termine, la mayoría de los
                        // navegadores lo tratan como apertura no solicitada).
                        window.open('{{ $whatsappConfirmLink }}', '_blank');

                        fetch('{{ route('budget.accept', $order->budget_token) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ payment_method: this.paymentMethods }),
                        })
                            .then(() => { this.accepted = true; })
                            .finally(() => { this.sending = false; });
                    },
                }"
            >
                <template x-if="!accepted">
                    <form method="POST" action="{{ route('budget.accept', $order->budget_token) }}" @submit.prevent="accept()">
                        @csrf

                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                            <p class="text-sm font-semibold text-amber-800 mb-1">💡 Para tener en cuenta al finalizar el trabajo</p>
                            <p class="text-sm text-amber-700 mb-3">¿Cómo preferís abonar? Elegí una opción o ambas.</p>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="payment_method[]" value="efectivo" x-model="paymentMethods" class="rounded border-gray-300 text-green-700 focus:ring-green-600">
                                    <span class="text-gray-800">Efectivo</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="payment_method[]" value="transferencia" x-model="paymentMethods" class="rounded border-gray-300 text-green-700 focus:ring-green-600">
                                    <span class="text-gray-800">Transferencia</span>
                                </label>
                            </div>
                            <p x-show="paymentMethodError" x-cloak class="text-sm text-red-600 font-medium mt-2">Elegí al menos una forma de pago para continuar.</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="sending"
                            class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-lg text-base disabled:opacity-60"
                        >
                            <span x-show="!sending">Aceptar presupuesto</span>
                            <span x-show="sending" x-cloak>Procesando...</span>
                        </button>
                    </form>
                </template>

                <template x-if="accepted">
                    <div class="bg-green-700 text-white rounded-lg p-4 text-center font-semibold">
                        ✅ ¡Gracias! Tu presupuesto fue aceptado. Nos pusimos en contacto para coordinar los próximos pasos.
                    </div>
                </template>
            </div>

            <a href="{{ route('budget.download', $order->budget_token) }}" class="block text-center text-sm text-green-700 font-medium underline">
                Descargar como documento
            </a>

            <p class="text-center text-sm text-gray-500">
                Cualquier consulta, respondé el mensaje que te enviamos. ¡Gracias por confiar en AltoParque!
            </p>
        </div>
    </div>
</div>
@endsection
