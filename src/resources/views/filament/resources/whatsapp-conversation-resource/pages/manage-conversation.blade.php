<x-filament-panels::page>
    @php
        $estados = \App\Filament\Resources\WhatsappConversationResource::ESTADOS;
    @endphp

    <div class="space-y-6" wire:poll.10s="refrescar">
        <x-filament::section heading="Datos de la consulta">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Sitio de origen</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $conversation->sitio_origen }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Zona</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $conversation->zona ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Servicio solicitado</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $conversation->servicio_solicitado ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Estado</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $estados[$conversation->estado_conversacion] ?? $conversation->estado_conversacion }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Teléfono del cliente</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $conversation->customer->phone }}</dd>
                </div>
            </dl>

            @if ($conversation->foto_path)
                <div class="mt-4">
                    <dt class="font-medium text-gray-700 dark:text-gray-200 mb-2">Foto enviada por el cliente</dt>
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($conversation->foto_path) }}" target="_blank">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($conversation->foto_path) }}"
                            alt="Foto enviada por el cliente"
                            class="max-w-xs rounded-lg border border-gray-200 dark:border-white/10"
                        >
                    </a>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Historial de mensajes">
            <div class="space-y-3 max-h-[28rem] overflow-y-auto p-1">
                @forelse ($this->messages as $message)
                    @php $esCliente = $message->remitente === 'cliente'; @endphp
                    <div class="flex {{ $esCliente ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[80%] rounded-lg px-3 py-2 text-sm {{ $esCliente ? 'bg-gray-100 dark:bg-white/5 text-gray-800 dark:text-gray-100' : 'bg-primary-600 text-white' }}">
                            <p class="text-xs opacity-70 mb-1">
                                {{ $esCliente ? 'Cliente' : ucfirst($message->remitente) }}
                                · {{ $message->enviado_en->format('d/m H:i') }}
                            </p>

                            @if ($message->tipo === 'imagen')
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($message->contenido) }}"
                                    alt="Imagen enviada"
                                    class="max-w-full rounded"
                                >
                            @else
                                <p class="whitespace-pre-wrap">{{ $message->contenido }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no hay mensajes en esta conversación.</p>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section heading="Responder">
            @if ($conversation->estado_conversacion === 'cerrada')
                <p class="text-sm text-gray-500 dark:text-gray-400">Este caso está cerrado. Reabrilo desde el botón de arriba para poder responder.</p>
            @else
                <form wire:submit.prevent="send" class="space-y-3">
                    {{ $this->form }}

                    <div class="flex justify-end">
                        <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                            Enviar
                        </x-filament::button>
                    </div>
                </form>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
