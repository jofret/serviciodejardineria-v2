<x-filament-panels::page>
    @php($conversaciones = $this->conversations)
    @php($estados = $this->estados)

    <x-filament::section heading="Filtrar por estado">
        <div class="flex flex-wrap gap-2">
            <x-filament::badge
                :color="is_null($estado) ? 'primary' : 'gray'"
                wire:click="filtrarPorEstado(null)"
                class="cursor-pointer"
            >
                Todos
            </x-filament::badge>
            @foreach ($estados as $valor => $etiqueta)
                <x-filament::badge
                    :color="$estado === $valor ? 'primary' : 'gray'"
                    wire:click="filtrarPorEstado('{{ $valor }}')"
                    class="cursor-pointer"
                >
                    {{ $etiqueta }}
                </x-filament::badge>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-white/10">
                        <th class="py-2 pr-4">Cliente</th>
                        <th class="py-2 pr-4">Teléfono</th>
                        <th class="py-2 pr-4">Sitio</th>
                        <th class="py-2 pr-4">Zona</th>
                        <th class="py-2 pr-4">Servicio</th>
                        <th class="py-2 pr-4">Estado</th>
                        <th class="py-2 pr-4">Última actividad</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversaciones['data'] as $conversacion)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-2 pr-4">{{ $conversacion->customer()?->name() ?: 'Sin nombre' }}</td>
                            <td class="py-2 pr-4">{{ $conversacion->customer()?->phone() }}</td>
                            <td class="py-2 pr-4">{{ $conversacion->sitioOrigen() }}</td>
                            <td class="py-2 pr-4">{{ $conversacion->zona() ?? '—' }}</td>
                            <td class="py-2 pr-4">{{ $conversacion->servicioSolicitado() ?? '—' }}</td>
                            <td class="py-2 pr-4">
                                <x-filament::badge :color="match ($conversacion->estadoConversacion()) {
                                    'con_humano' => 'danger',
                                    'cerrada' => 'success',
                                    'esperando_agenda_visita', 'esperando_cotizacion_foto' => 'warning',
                                    default => 'gray',
                                }">
                                    {{ $estados[$conversacion->estadoConversacion()] ?? $conversacion->estadoConversacion() }}
                                </x-filament::badge>
                            </td>
                            <td class="py-2 pr-4">{{ $conversacion->updatedAt()?->format('d/m/Y H:i') }}</td>
                            <td class="py-2 text-right">
                                <x-filament::button
                                    size="sm"
                                    icon="heroicon-o-chat-bubble-left-ellipsis"
                                    tag="a"
                                    :href="\App\Filament\Resources\WhatsappConversationResource::getUrl('manage', ['record' => $conversacion->id()])"
                                >
                                    Atender
                                </x-filament::button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                No hay conversaciones{{ $estado ? ' con este estado' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($conversaciones['meta']['last_page'] > 1)
            <div class="flex items-center justify-between mt-4 text-sm">
                <span class="text-gray-500 dark:text-gray-400">
                    Página {{ $conversaciones['meta']['current_page'] }} de {{ $conversaciones['meta']['last_page'] }}
                    ({{ $conversaciones['meta']['total'] }} en total)
                </span>
                <div class="flex gap-2">
                    <x-filament::button
                        size="sm"
                        color="gray"
                        :disabled="$conversaciones['meta']['current_page'] <= 1"
                        wire:click="irAPagina({{ $conversaciones['meta']['current_page'] - 1 }})"
                    >
                        Anterior
                    </x-filament::button>
                    <x-filament::button
                        size="sm"
                        color="gray"
                        :disabled="$conversaciones['meta']['current_page'] >= $conversaciones['meta']['last_page']"
                        wire:click="irAPagina({{ $conversaciones['meta']['current_page'] + 1 }})"
                    >
                        Siguiente
                    </x-filament::button>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
