<x-filament-panels::page>
    @php $relevamiento = $serviceOrder->relevamiento; @endphp

    <div class="space-y-6">
        <x-filament::section heading="Datos generales del relevamiento">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Tipo de propiedad</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $relevamiento->property_type_label ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Tipo de servicio</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $relevamiento->service_type_label ?? '—' }}</dd>
                </div>
            </dl>
        </x-filament::section>

        <x-filament::section heading="Otros datos cargados por el relevador">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">¿Requiere Cláusula de No-Repetición?</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $relevamiento->requires_non_compete_clause ? 'Sí' : 'No' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Herramientas necesarias</dt>
                    <dd class="text-gray-600 dark:text-gray-400 mt-1">
                        @forelse ($relevamiento->workTools as $tool)
                            <span class="inline-block text-xs bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-full mr-1 mb-1">{{ $tool->name }}</span>
                        @empty
                            —
                        @endforelse
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Trabajadores para la Obra</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $relevamiento->workers_count ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Duración Aproximada de la Obra</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $relevamiento->estimated_duration_days ? $relevamiento->estimated_duration_days.' día(s)' : '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-700 dark:text-gray-200">Precio Estimativo (referencia, no editable)</dt>
                    <dd class="text-gray-600 dark:text-gray-400">{{ $relevamiento->estimated_price ? '$'.number_format($relevamiento->estimated_price, 2, ',', '.') : '—' }}</dd>
                </div>
            </dl>
        </x-filament::section>

        <form wire:submit.prevent="save">
            {{ $this->form }}
        </form>
    </div>

    {{--
        Formatea "Precio final" con punto de miles y coma decimal mientras se
        escribe (ej. "1.234.567,50"). Se hace a mano porque el bundle de
        Filament instalado acá no trae el plugin de Alpine ($money) que el
        ->mask() nativo necesita — ver ReviewAndQuote::formatPriceDisplay()
        para la conversión inversa al guardar.
    --}}
    <script>
        window.formatThousandsInput = function (event) {
            var input = event.target;
            var raw = input.value.replace(/[^\d,]/g, '');

            var firstComma = raw.indexOf(',');
            if (firstComma !== -1) {
                raw = raw.slice(0, firstComma + 1) + raw.slice(firstComma + 1).replace(/,/g, '');
            }

            var parts = raw.split(',');
            var intDigits = parts[0].replace(/^0+(?=\d)/, '');
            var decDigits = parts.length > 1 ? parts[1].slice(0, 2) : null;

            var formattedInt = intDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            var formatted = decDigits !== null ? (formattedInt + ',' + decDigits) : formattedInt;

            if (input.value !== formatted) {
                input.value = formatted;
                input.dispatchEvent(new Event('input'));
            }
        };
    </script>
</x-filament-panels::page>
