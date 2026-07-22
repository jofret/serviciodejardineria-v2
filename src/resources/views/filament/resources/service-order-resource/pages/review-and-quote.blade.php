<x-filament-panels::page>
    @php $relevamiento = $serviceOrder->relevamiento; @endphp

    {{--
        Este panel de Filament no carga public/css/tailwind-generated.css (solo lo hacen
        los layouts públicos y del relevador), así que clases utilitarias "no comunes"
        (aspect-square, object-cover, valores arbitrarios) no tienen ninguna regla que
        las respalde acá. Por eso el grid de miniaturas y el lightbox usan CSS propio,
        scopeado con el prefijo "wi-" (work item), en vez de depender de Tailwind.
    --}}
    <style>
        .wi-table-wrap { overflow-x: auto; margin-top: 0.25rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; }
        .wi-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .wi-table thead th { text-align: left; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; color: #6b7280; background-color: #f9fafb; padding: 0.625rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .wi-table tbody td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .wi-table tbody tr:last-child td { border-bottom: 0; }
        .wi-table tbody tr:hover { background-color: #f9fafb; }
        .wi-col-num { width: 2.5rem; text-align: center; color: #6b7280; font-variant-numeric: tabular-nums; }
        .wi-col-pickup { width: 4.5rem; text-align: center; }
        .wi-col-photos { width: 1%; white-space: nowrap; text-align: right; }
        .wi-item-desc { margin: 0; color: #1f2937; }
        .wi-item-obs { margin: 0.25rem 0 0; color: #6b7280; font-size: 0.75rem; }
        .wi-no-photos { color: #9ca3af; font-size: 0.75rem; }
        .wi-pickup-yes { color: #166534; font-weight: 600; }
        .wi-pickup-no { color: #9ca3af; }

        .dark .wi-table-wrap { border-color: rgba(255, 255, 255, 0.1); }
        .dark .wi-table thead th { color: #9ca3af; background-color: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.1); }
        .dark .wi-table tbody td { border-color: rgba(255, 255, 255, 0.06); }
        .dark .wi-table tbody tr:hover { background-color: rgba(255, 255, 255, 0.03); }
        .dark .wi-col-num { color: #9ca3af; }
        .dark .wi-item-desc { color: #f3f4f6; }
        .dark .wi-item-obs { color: #9ca3af; }
        .dark .wi-no-photos { color: #6b7280; }
        .dark .wi-pickup-yes { color: #4ade80; }
        .dark .wi-pickup-no { color: #6b7280; }

        .wi-lightbox-overlay { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.8); padding: 1rem; }
        .wi-lightbox-overlay img { max-height: 90vh; max-width: 90vw; border-radius: 0.5rem; object-fit: contain; }
        .wi-lightbox-close { position: absolute; top: 1rem; right: 1rem; font-size: 2rem; line-height: 1; color: rgba(255, 255, 255, 0.8); background: transparent; border: 0; padding: 0.25rem 0.5rem; cursor: pointer; }
        .wi-lightbox-close:hover { color: #fff; }
        .wi-lightbox-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 2.75rem; height: 2.75rem; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.1); border: 0; border-radius: 9999px; color: #fff; font-size: 1.25rem; cursor: pointer; }
        .wi-lightbox-nav:hover { background: rgba(255, 255, 255, 0.25); }
        .wi-lightbox-prev { left: 1rem; }
        .wi-lightbox-next { right: 1rem; }
        .wi-lightbox-counter { position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); color: rgba(255, 255, 255, 0.85); font-size: 0.8125rem; background: rgba(0, 0, 0, 0.45); padding: 0.25rem 0.75rem; border-radius: 9999px; }
    </style>

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

        <x-filament::section heading="Trabajo a realizar">
            <div
                x-data="{
                    lightboxOpen: false,
                    lightboxImages: [],
                    lightboxIndex: 0,
                    openLightbox(images) {
                        this.lightboxImages = images;
                        this.lightboxIndex = 0;
                        this.lightboxOpen = true;
                    },
                    next() {
                        this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxImages.length;
                    },
                    prev() {
                        this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxImages.length) % this.lightboxImages.length;
                    },
                }"
                @keydown.escape.window="lightboxOpen = false"
                @keydown.arrow-right.window="lightboxOpen && next()"
                @keydown.arrow-left.window="lightboxOpen && prev()"
            >
                @if ($relevamiento->workItems->isNotEmpty())
                    <div class="wi-table-wrap">
                        <table class="wi-table">
                            <thead>
                                <tr>
                                    <th class="wi-col-num">#</th>
                                    <th>Ítem de trabajo</th>
                                    <th class="wi-col-pickup">Retiro</th>
                                    <th class="wi-col-photos">Fotos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($relevamiento->workItems as $index => $item)
                                    @php
                                        $photos = $item->getMedia('photos');
                                        $photoUrlsJson = $photos->map(fn ($photo) => $photo->getUrl())->values()->toJson();
                                    @endphp
                                    <tr>
                                        <td class="wi-col-num">{{ $index + 1 }}</td>
                                        <td>
                                            <p class="wi-item-desc">{{ $item->description ?: '—' }}</p>
                                            @if ($item->observations)
                                                <p class="wi-item-obs">{{ $item->observations }}</p>
                                            @endif
                                        </td>
                                        <td class="wi-col-pickup">
                                            @if ($item->includes_pickup)
                                                <span class="wi-pickup-yes">Sí</span>
                                            @else
                                                <span class="wi-pickup-no">No</span>
                                            @endif
                                        </td>
                                        <td class="wi-col-photos">
                                            @if ($photos->isNotEmpty())
                                                <x-filament::button
                                                    type="button"
                                                    color="gray"
                                                    size="sm"
                                                    icon="heroicon-o-photo"
                                                    x-on:click="openLightbox({{ $photoUrlsJson }})"
                                                >
                                                    Ver imagen{{ $photos->count() > 1 ? ' ('.$photos->count().')' : '' }}
                                                </x-filament::button>
                                            @else
                                                <span class="wi-no-photos">Sin fotos</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No se cargaron ítems de trabajo.</p>
                @endif

                <div
                    x-show="lightboxOpen"
                    x-cloak
                    style="display: none;"
                    @click.self="lightboxOpen = false"
                    class="wi-lightbox-overlay"
                >
                    <button type="button" @click="lightboxOpen = false" class="wi-lightbox-close" aria-label="Cerrar">&times;</button>

                    <template x-if="lightboxImages.length > 1">
                        <button type="button" @click.stop="prev()" class="wi-lightbox-nav wi-lightbox-prev" aria-label="Anterior">&#10094;</button>
                    </template>

                    <img :src="lightboxImages[lightboxIndex]" alt="Foto ampliada">

                    <template x-if="lightboxImages.length > 1">
                        <button type="button" @click.stop="next()" class="wi-lightbox-nav wi-lightbox-next" aria-label="Siguiente">&#10095;</button>
                    </template>

                    <template x-if="lightboxImages.length > 1">
                        <div class="wi-lightbox-counter" x-text="(lightboxIndex + 1) + ' / ' + lightboxImages.length"></div>
                    </template>
                </div>
            </div>
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

        <x-filament::section heading="Precio final de la orden">
            <form wire:submit.prevent="save">
                {{ $this->form }}
            </form>
        </x-filament::section>
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
