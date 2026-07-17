@php
    $property = $relevamiento->property;
    $isCustomPropertyType = $property->property_type && ! array_key_exists($property->property_type, \App\Models\Property::PROPERTY_TYPES);
    $selectedPropertyType = old('property_type', $isCustomPropertyType ? 'otro' : $property->property_type);
    $propertyTypeOther = old('property_type_other', $isCustomPropertyType ? $property->property_type : '');
@endphp

<div id="autosave-status" class="mt-4 text-xs text-gray-400 flex items-center gap-1" data-state="idle">
    <span>Los cambios se guardan solos mientras completás el formulario.</span>
</div>

<form method="POST" action="{{ route('relevador.update', $relevamiento) }}" id="relevamiento-form" class="mt-2 space-y-4">
    @csrf

    @if ($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm p-3">
            <p class="font-medium mb-1">Revisá estos datos:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <h2 class="font-semibold text-gray-800">Datos generales</h2>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de propiedad</label>
            <select name="property_type" id="property_type" data-toggle-select="property_type_other_wrap"
                    data-toggle-select-value="otro" class="w-full rounded-lg border-gray-300 text-base py-2 px-3">
                @foreach (\App\Models\Property::PROPERTY_TYPES as $value => $label)
                    <option value="{{ $value }}" {{ $selectedPropertyType === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div id="property_type_other_wrap" class="{{ $selectedPropertyType === 'otro' ? '' : 'hidden' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">Especificar tipo de propiedad</label>
            <input type="text" name="property_type_other" value="{{ $propertyTypeOther }}"
                   class="w-full rounded-lg border-gray-300 text-base py-2 px-3">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Superficie total (m²)</label>
            <input type="number" step="0.01" name="total_area" value="{{ old('total_area', $property->total_area) }}"
                   class="w-full rounded-lg border-gray-300 text-base py-2 px-3">
        </div>
    </div>

    {{-- Jardín --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="has_garden" value="1" data-toggle="garden_areas_section"
                   {{ old('has_garden', $property->has_garden) ? 'checked' : '' }} class="rounded">
            <span class="font-semibold text-gray-800">¿Tiene jardín?</span>
        </label>
        <div id="garden_areas_section" class="space-y-2 {{ $property->has_garden ? '' : 'hidden' }}">
            <div id="garden_areas_rows" class="space-y-2">
                @foreach ($property->garden_areas ?? [] as $row)
                    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
                        <input type="text" name="garden_areas[{{ $loop->index }}][name]" placeholder="Nombre" value="{{ $row['name'] ?? '' }}" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <input type="number" step="0.01" name="garden_areas[{{ $loop->index }}][size]" placeholder="m²" value="{{ $row['size'] ?? '' }}" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-add="garden_areas_template" data-container="garden_areas_rows"
                    data-next-index="{{ count($property->garden_areas ?? []) }}"
                    class="text-sm text-green-700 font-medium">+ Agregar área de jardín</button>
        </div>
    </div>

    {{-- Piscina --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="has_pool" value="1" data-toggle="pools_section"
                   {{ old('has_pool', $property->has_pool) ? 'checked' : '' }} class="rounded">
            <span class="font-semibold text-gray-800">¿Tiene piscina?</span>
        </label>
        <div id="pools_section" class="space-y-2 {{ $property->has_pool ? '' : 'hidden' }}">
            <div id="pools_rows" class="space-y-2">
                @foreach ($property->pools ?? [] as $row)
                    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
                        <select name="pools[{{ $loop->index }}][type]" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
                            @foreach (\App\Models\Property::POOL_TYPES as $value => $label)
                                <option value="{{ $value }}" {{ ($row['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="pools[{{ $loop->index }}][liters]" placeholder="Litros" value="{{ $row['liters'] ?? '' }}" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <input type="number" step="0.01" name="pools[{{ $loop->index }}][size_m2]" placeholder="m²" value="{{ $row['size_m2'] ?? '' }}" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-add="pools_template" data-container="pools_rows"
                    data-next-index="{{ count($property->pools ?? []) }}"
                    class="text-sm text-green-700 font-medium">+ Agregar piscina</button>
        </div>
    </div>

    {{-- Árboles --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="has_trees" value="1" data-toggle="trees_section"
                   {{ old('has_trees', $property->has_trees) ? 'checked' : '' }} class="rounded">
            <span class="font-semibold text-gray-800">¿Tiene árboles?</span>
        </label>
        <div id="trees_section" class="space-y-2 {{ $property->has_trees ? '' : 'hidden' }}">
            <div id="trees_rows" class="space-y-2">
                @foreach ($property->trees ?? [] as $row)
                    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
                        <input type="text" name="trees[{{ $loop->index }}][species]" placeholder="Especie" value="{{ $row['species'] ?? '' }}" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <input type="number" name="trees[{{ $loop->index }}][quantity]" placeholder="Cant." value="{{ $row['quantity'] ?? '' }}" class="w-16 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <input type="number" step="0.01" name="trees[{{ $loop->index }}][height]" placeholder="Altura m" value="{{ $row['height'] ?? '' }}" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-add="trees_template" data-container="trees_rows"
                    data-next-index="{{ count($property->trees ?? []) }}"
                    class="text-sm text-green-700 font-medium">+ Agregar árbol</button>
        </div>
    </div>

    {{-- Plantas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="has_plants" value="1" data-toggle="plants_section"
                   {{ old('has_plants', $property->has_plants) ? 'checked' : '' }} class="rounded">
            <span class="font-semibold text-gray-800">¿Tiene plantas?</span>
        </label>
        <div id="plants_section" class="space-y-2 {{ $property->has_plants ? '' : 'hidden' }}">
            <div id="plants_rows" class="space-y-2">
                @foreach ($property->plants ?? [] as $row)
                    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
                        <input type="text" name="plants[{{ $loop->index }}][species]" placeholder="Especie" value="{{ $row['species'] ?? '' }}" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <input type="number" name="plants[{{ $loop->index }}][quantity]" placeholder="Cant." value="{{ $row['quantity'] ?? '' }}" class="w-16 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-add="plants_template" data-container="plants_rows"
                    data-next-index="{{ count($property->plants ?? []) }}"
                    class="text-sm text-green-700 font-medium">+ Agregar planta</button>
        </div>
    </div>

    {{-- Áreas deportivas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="has_sport_areas" value="1" data-toggle="sport_areas_section"
                   {{ old('has_sport_areas', $property->has_sport_areas) ? 'checked' : '' }} class="rounded">
            <span class="font-semibold text-gray-800">¿Tiene áreas deportivas?</span>
        </label>
        <div id="sport_areas_section" class="space-y-2 {{ $property->has_sport_areas ? '' : 'hidden' }}">
            <div id="sport_areas_rows" class="space-y-2">
                @foreach ($property->sport_areas ?? [] as $row)
                    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
                        <select name="sport_areas[{{ $loop->index }}][type]" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
                            @foreach (\App\Models\Property::SPORT_AREA_TYPES as $value => $label)
                                <option value="{{ $value }}" {{ ($row['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="sport_areas[{{ $loop->index }}][quantity]" placeholder="Cant." value="{{ $row['quantity'] ?? '' }}" class="w-16 rounded-lg border-gray-300 text-sm py-2 px-2">
                        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" data-add="sport_areas_template" data-container="sport_areas_rows"
                    data-next-index="{{ count($property->sport_areas ?? []) }}"
                    class="text-sm text-green-700 font-medium">+ Agregar área deportiva</button>
        </div>
    </div>

    {{-- Tags --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-2">
        <label class="block text-sm font-medium text-gray-700">Tags</label>
        <input type="text" name="tags" placeholder="separados por coma, ej: riego automático, césped deteriorado"
               value="{{ old('tags', $property->tags->pluck('name')->join(', ')) }}"
               class="w-full rounded-lg border-gray-300 text-base py-2 px-3">
    </div>

    {{-- Notas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-2">
        <label class="block text-sm font-medium text-gray-700">Notas</label>
        <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 text-base py-2 px-3">{{ old('notes', $relevamiento->notes) }}</textarea>
    </div>

    <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-lg text-base">
        Enviar relevamiento
    </button>
</form>

{{-- Fotos: widget aparte del <form>, sube cada foto apenas se elige --}}
<div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-2">
    <label class="block text-sm font-medium text-gray-700">Fotos de la visita</label>
    <input type="file" id="photo-input" multiple accept="image/*" capture="environment" class="w-full text-sm">
    <div id="photo-grid" class="grid grid-cols-3 gap-2 mt-2">
        @foreach ($relevamiento->getMedia('photos') as $photo)
            <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100" data-photo-id="{{ $photo->id }}">
                <img src="{{ $photo->getUrl() }}" alt="Foto de la visita" class="w-full h-full object-cover">
                <button type="button" data-remove-photo class="absolute top-1 right-1 bg-black/60 text-white text-xs w-5 h-5 rounded-full leading-none">✕</button>
            </div>
        @endforeach
    </div>
</div>

{{--
    Templates para agregar filas dinámicamente. Usan el placeholder __INDEX__
    en vez de [] porque PHP no correlaciona automáticamente varios campos
    "name[][subcampo]" en la misma fila — cada uno se parsea como una fila
    nueva. El JS reemplaza __INDEX__ por un índice numérico compartido antes
    de insertar la fila.
--}}
<template id="garden_areas_template">
    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
        <input type="text" name="garden_areas[__INDEX__][name]" placeholder="Nombre" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
        <input type="number" step="0.01" name="garden_areas[__INDEX__][size]" placeholder="m²" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
    </div>
</template>

<template id="pools_template">
    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
        <select name="pools[__INDEX__][type]" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
            @foreach (\App\Models\Property::POOL_TYPES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="number" name="pools[__INDEX__][liters]" placeholder="Litros" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
        <input type="number" step="0.01" name="pools[__INDEX__][size_m2]" placeholder="m²" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
    </div>
</template>

<template id="trees_template">
    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
        <input type="text" name="trees[__INDEX__][species]" placeholder="Especie" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
        <input type="number" name="trees[__INDEX__][quantity]" placeholder="Cant." class="w-16 rounded-lg border-gray-300 text-sm py-2 px-2">
        <input type="number" step="0.01" name="trees[__INDEX__][height]" placeholder="Altura m" class="w-20 rounded-lg border-gray-300 text-sm py-2 px-2">
        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
    </div>
</template>

<template id="plants_template">
    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
        <input type="text" name="plants[__INDEX__][species]" placeholder="Especie" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
        <input type="number" name="plants[__INDEX__][quantity]" placeholder="Cant." class="w-16 rounded-lg border-gray-300 text-sm py-2 px-2">
        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
    </div>
</template>

<template id="sport_areas_template">
    <div data-row class="flex gap-2 items-start bg-gray-50 rounded-lg p-2">
        <select name="sport_areas[__INDEX__][type]" class="flex-1 rounded-lg border-gray-300 text-sm py-2 px-2">
            @foreach (\App\Models\Property::SPORT_AREA_TYPES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="number" name="sport_areas[__INDEX__][quantity]" placeholder="Cant." class="w-16 rounded-lg border-gray-300 text-sm py-2 px-2">
        <button type="button" data-remove class="text-red-600 px-2 py-2">✕</button>
    </div>
</template>

<script>
    document.querySelectorAll('[data-toggle]').forEach(function (checkbox) {
        var target = document.getElementById(checkbox.dataset.toggle);
        var sync = function () { target.classList.toggle('hidden', !checkbox.checked); };
        checkbox.addEventListener('change', sync);
    });

    document.querySelectorAll('[data-toggle-select]').forEach(function (select) {
        var target = document.getElementById(select.dataset.toggleSelect);
        var sync = function () { target.classList.toggle('hidden', select.value !== select.dataset.toggleSelectValue); };
        select.addEventListener('change', sync);
    });

    document.querySelectorAll('[data-add]').forEach(function (button) {
        button.addEventListener('click', function () {
            var template = document.getElementById(button.dataset.add);
            var container = document.getElementById(button.dataset.container);
            var nextIndex = parseInt(button.dataset.nextIndex, 10);
            var html = template.innerHTML.replace(/__INDEX__/g, nextIndex);
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            container.appendChild(wrapper.firstElementChild);
            button.dataset.nextIndex = nextIndex + 1;
        });
    });

    document.addEventListener('click', function (event) {
        if (event.target.matches('[data-remove]')) {
            event.target.closest('[data-row]').remove();
        }
    });
</script>

{{-- Autoguardado en tiempo real + subida de fotos al toque, para no perder
     lo cargado si se corta la conexión a mitad de la visita. --}}
<script>
    (function () {
        var form = document.getElementById('relevamiento-form');
        var statusEl = document.getElementById('autosave-status');
        var autosaveUrl = @json(route('relevador.autosave', $relevamiento));
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        var debounceTimer = null;
        var retryTimer = null;
        var saving = false;
        var dirty = false;

        function setStatus(text) {
            statusEl.textContent = text;
        }

        function scheduleRetry() {
            if (retryTimer) {
                return;
            }
            retryTimer = setTimeout(function () {
                retryTimer = null;
                save();
            }, 5000);
        }

        function save() {
            if (saving) {
                dirty = true;
                return;
            }
            saving = true;
            dirty = false;
            setStatus('Guardando…');

            fetch(autosaveUrl, {
                method: 'POST',
                body: new FormData(form),
                headers: {'Accept': 'application/json'},
            }).then(function (response) {
                saving = false;

                if (response.ok) {
                    setStatus('Guardado ✓');
                    if (dirty) {
                        save();
                    }
                    return;
                }

                // La request llegó al servidor pero falló — no es un problema
                // de conexión, reintentar con los mismos datos no va a
                // arreglarlo solo. Se distingue del catch() de abajo, que es
                // el que sí corresponde a "no hay señal".
                if (response.status === 419) {
                    setStatus('Tu sesión expiró — recargá la página para seguir');
                    return;
                }

                if (response.status === 403) {
                    setStatus('Este relevamiento ya no admite cambios');
                    return;
                }

                if (response.status === 422) {
                    response.json().then(function (body) {
                        var firstError = body && body.errors ? Object.values(body.errors)[0][0] : null;
                        setStatus(firstError ? ('No se pudo guardar: ' + firstError) : 'No se pudo guardar: revisá los datos cargados');
                    }).catch(function () {
                        setStatus('No se pudo guardar: revisá los datos cargados');
                    });
                    return;
                }

                console.error('Autoguardado: el servidor respondió ' + response.status);
                setStatus('Error al guardar, reintentando…');
                scheduleRetry();
            }).catch(function (error) {
                saving = false;
                console.error('Autoguardado: sin respuesta del servidor', error);
                setStatus('Sin conexión, reintentando…');
                scheduleRetry();
            });
        }

        form.addEventListener('change', function (event) {
            if (event.target.matches('select, input[type="checkbox"], input[type="radio"]')) {
                clearTimeout(debounceTimer);
                save();
            }
        });

        form.addEventListener('input', function (event) {
            if (event.target.matches('input[type="text"], input[type="number"], textarea')) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(save, 1000);
            }
        });

        window.addEventListener('online', function () {
            if (retryTimer) {
                clearTimeout(retryTimer);
                retryTimer = null;
            }
            save();
        });

        var photoInput = document.getElementById('photo-input');
        var photoGrid = document.getElementById('photo-grid');
        var photosBaseUrl = @json(route('relevador.photos.store', $relevamiento));

        photoInput.addEventListener('change', function () {
            Array.prototype.forEach.call(photoInput.files, function (file) {
                var data = new FormData();
                data.append('photo', file);
                data.append('_token', csrfToken);

                fetch(photosBaseUrl, {
                    method: 'POST',
                    body: data,
                    headers: {'Accept': 'application/json'},
                }).then(function (response) {
                    if (!response.ok) {
                        if (response.status === 419) {
                            throw new Error('Tu sesión expiró — recargá la página para seguir');
                        }
                        if (response.status === 403) {
                            throw new Error('Este relevamiento ya no admite cambios');
                        }
                        throw new Error('No se pudo subir la foto — probá de nuevo');
                    }
                    return response.json();
                }).then(function (photo) {
                    var cell = document.createElement('div');
                    cell.className = 'relative aspect-square rounded-lg overflow-hidden bg-gray-100';
                    cell.dataset.photoId = photo.id;
                    cell.innerHTML = '<img src="' + photo.url + '" alt="Foto de la visita" class="w-full h-full object-cover">'
                        + '<button type="button" data-remove-photo class="absolute top-1 right-1 bg-black/60 text-white text-xs w-5 h-5 rounded-full leading-none">✕</button>';
                    photoGrid.appendChild(cell);
                }).catch(function (error) {
                    console.error('Subida de foto:', error);
                    setStatus(error.message || 'No se pudo subir una foto, probá de nuevo');
                });
            });

            photoInput.value = '';
        });

        photoGrid.addEventListener('click', function (event) {
            if (!event.target.matches('[data-remove-photo]')) {
                return;
            }

            var cell = event.target.closest('[data-photo-id]');

            fetch(photosBaseUrl + '/' + cell.dataset.photoId, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
            }).then(function (response) {
                if (response.ok) {
                    cell.remove();
                }
            });
        });
    })();
</script>
