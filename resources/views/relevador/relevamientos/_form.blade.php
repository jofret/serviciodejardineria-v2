@php
    $property = $relevamiento->property;
    $isCustomPropertyType = $property->property_type && ! array_key_exists($property->property_type, \App\Models\Property::PROPERTY_TYPES);
    $selectedPropertyType = old('property_type', $isCustomPropertyType ? 'otro' : $property->property_type);
    $propertyTypeOther = old('property_type_other', $isCustomPropertyType ? $property->property_type : '');
@endphp

<form method="POST" action="{{ route('relevador.update', $relevamiento) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
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

    {{-- Fotos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-2">
        <label class="block text-sm font-medium text-gray-700">Fotos de la visita</label>
        <input type="file" name="photos[]" multiple accept="image/*" capture="environment"
               class="w-full text-sm">
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
