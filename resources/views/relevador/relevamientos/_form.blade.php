@php
    $property = $relevamiento->property;
    $currentPropertyType = $relevamiento->property_type ?? $property->property_type;
    $isCustomPropertyType = $currentPropertyType && ! array_key_exists($currentPropertyType, \App\Models\Property::PROPERTY_TYPES);
    $selectedPropertyType = old('property_type', $isCustomPropertyType ? 'otro' : $currentPropertyType);
    $propertyTypeOther = old('property_type_other', $isCustomPropertyType ? $currentPropertyType : '');

    $selectedToolNames = old('work_tools', $relevamiento->workTools->pluck('name')->all());
    $availableTools = \App\Models\WorkTool::where('is_active', true)
        ->orWhereIn('id', $relevamiento->workTools->pluck('id'))
        ->orderBy('order')
        ->orderBy('name')
        ->get();
    $extraCustomToolNames = array_diff($selectedToolNames, $availableTools->pluck('name')->all());
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
        <h2 class="font-semibold text-gray-800">Datos Generales</h2>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Propiedad</label>
            <p class="text-xs text-gray-500 mb-1">Cargado por administración: {{ $property->property_type_label ?? '—' }}. Corregí acá si al llegar a la visita ves algo distinto — no modifica el dato original.</p>
            <select name="property_type" id="property_type" data-toggle-select="property_type_other_wrap"
                    data-toggle-select-value="otro" class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">
                @foreach (\App\Models\Property::PROPERTY_TYPES as $value => $label)
                    <option value="{{ $value }}" {{ $selectedPropertyType === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div id="property_type_other_wrap" class="{{ $selectedPropertyType === 'otro' ? '' : 'hidden' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">Especificar Tipo de Propiedad</label>
            <input type="text" name="property_type_other" value="{{ $propertyTypeOther }}"
                   spellcheck="true" lang="es" autocorrect="on"
                   class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Superficie Total (m²)</label>
            <input type="number" step="0.01" name="total_area" value="{{ old('total_area', $property->total_area) }}"
                   class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">
        </div>
    </div>

    {{-- Trabajo a realizar: fuera del guardado del <form> — cada ítem se crea,
         edita y le suben fotos por su cuenta contra el endpoint de items. --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <h2 class="font-semibold text-gray-800">Trabajo a Realizar</h2>
        <p class="text-xs text-gray-500">Cargá cada tarea como un ítem aparte (ej. "poda del árbol grande", "limpieza del cerco"), con su propia descripción, observaciones y fotos.</p>

        <div id="work-items" class="space-y-3">
            @foreach ($relevamiento->workItems as $item)
                @include('relevador.relevamientos._work_item', ['item' => $item])
            @endforeach
        </div>

        <button type="button" id="add-work-item" class="text-sm text-green-700 font-medium">+ Agregar ítem de trabajo</button>
    </div>

    {{-- Cláusula de no-repetición --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 divide-y divide-gray-100">
        <label class="flex items-center justify-between gap-3 cursor-pointer py-2">
            <span class="font-semibold text-gray-800">Requiere Cláusula de No-Repetición</span>
            <input type="checkbox" name="requires_non_compete_clause" value="1" class="sr-only peer"
                   {{ old('requires_non_compete_clause', $relevamiento->requires_non_compete_clause) ? 'checked' : '' }}>
            <span class="relative w-11 h-6 shrink-0 rounded-full bg-gray-300 peer-checked:bg-green-600 transition-colors duration-200
                         after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-200
                         peer-checked:after:translate-x-5"></span>
        </label>
    </div>

    {{--
        Template del ítem nuevo: se clona en el cliente sin pegarle todavía al
        servidor. Un ítem recién agregado no tiene data-item-id — solo se
        crea (ensureItemCreated) la primera vez que tiene algo real que
        guardar (texto tipeado o una foto), así nunca queda un registro vacío
        en la base si el relevador agrega un ítem y no llega a cargar nada.
    --}}
    <template id="work_item_template">
        <div data-work-item class="border border-gray-200 rounded-lg p-3 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500">Ítem de Trabajo</span>
                <button type="button" data-remove-item class="text-red-600 text-xs">Eliminar ítem</button>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción del Trabajo</label>
                <textarea data-item-field="description" rows="2" placeholder="Ej: poda del árbol grande"
                          spellcheck="true" lang="es" autocorrect="on"
                          class="w-full rounded-lg border border-gray-300 text-base py-2 px-3"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                <textarea data-item-field="observations" rows="2" placeholder="Detalles adicionales"
                          spellcheck="true" lang="es" autocorrect="on"
                          class="w-full rounded-lg border border-gray-300 text-base py-2 px-3"></textarea>
            </div>
            <hr class="border-gray-200">
            <label class="flex items-center gap-2 text-sm text-gray-700 my-3">
                <input type="checkbox" data-item-field="includes_pickup" class="rounded">
                Incluye retiro
            </label>
            <hr class="border-gray-200">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fotos del Ítem</label>
                <input type="file" data-item-photo-input multiple accept="image/*" capture="environment" class="w-full text-sm">
                <div data-item-photo-grid class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2"></div>
            </div>
        </div>
    </template>

    {{-- Herramientas de trabajo --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <label class="block font-semibold text-gray-800">Herramientas para Realizar el Trabajo</label>
        <div id="work-tools-list" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach ($availableTools as $tool)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="work_tools[]" value="{{ $tool->name }}" class="rounded"
                           {{ in_array($tool->name, $selectedToolNames, true) ? 'checked' : '' }}>
                    {{ $tool->name }}
                </label>
            @endforeach
            @foreach ($extraCustomToolNames as $customName)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="work_tools[]" value="{{ $customName }}" class="rounded" checked>
                    {{ $customName }}
                </label>
            @endforeach
        </div>
        <div class="flex gap-2 pt-1">
            <input type="text" id="work-tool-custom-input" placeholder="¿No está en la lista? Escribila acá"
                   spellcheck="true" lang="es" autocorrect="on"
                   class="flex-1 rounded-lg border border-gray-300 text-base py-2 px-3">
            <button type="button" id="work-tool-custom-add" class="text-sm text-green-700 font-medium whitespace-nowrap px-2">+ Agregar</button>
        </div>
    </div>

    {{-- Notas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-2">
        <label class="block text-sm font-medium text-gray-700">Notas</label>
        <textarea name="notes" rows="3" spellcheck="true" lang="es" autocorrect="on" class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">{{ old('notes', $relevamiento->notes) }}</textarea>
    </div>

    {{-- Trabajadores, duración y precio --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <div>
            <label class="block font-semibold text-gray-800 mb-1">Trabajadores para la Obra</label>
            <input type="number" step="1" min="0" name="workers_count" value="{{ old('workers_count', $relevamiento->workers_count) }}"
                   required class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">
        </div>
        <div>
            <label class="block font-semibold text-gray-800 mb-1">Duración Aproximada de la Obra (días)</label>
            <input type="number" step="1" min="0" name="estimated_duration_days" value="{{ old('estimated_duration_days', $relevamiento->estimated_duration_days) }}"
                   required class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">
        </div>
        <div>
            <label class="block font-semibold text-gray-800 mb-1">Precio Estimativo</label>
            <input type="text" inputmode="decimal" id="estimated_price_display"
                   required class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">
            <input type="hidden" name="estimated_price" id="estimated_price"
                   value="{{ old('estimated_price', $relevamiento->estimated_price) }}">
        </div>
    </div>

    <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-lg text-base">
        Enviar relevamiento
    </button>
</form>

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

    (function () {
        var list = document.getElementById('work-tools-list');
        var input = document.getElementById('work-tool-custom-input');
        var addButton = document.getElementById('work-tool-custom-add');

        function addCustomTool() {
            var name = input.value.trim();
            if (!name) {
                return;
            }

            var checkbox = Array.prototype.find.call(list.querySelectorAll('input[type="checkbox"]'), function (cb) {
                return cb.value.toLowerCase() === name.toLowerCase();
            });

            if (!checkbox) {
                var label = document.createElement('label');
                label.className = 'flex items-center gap-2 text-sm text-gray-700';
                checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'work_tools[]';
                checkbox.className = 'rounded';
                checkbox.value = name;
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(name));
                list.appendChild(label);
            }

            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change', {bubbles: true}));
            input.value = '';
        }

        addButton.addEventListener('click', addCustomTool);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addCustomTool();
            }
        });
    })();

    // Formatea "Precio Estimativo" con punto de miles y coma decimal (ej.
    // "1.234.567,50") mientras se escribe. El input visible no tiene name
    // — el valor real (sin puntos, con punto decimal) va al input oculto
    // que es el que efectivamente se envía y valida en el servidor.
    (function () {
        var display = document.getElementById('estimated_price_display');
        var hidden = document.getElementById('estimated_price');

        function splitDigitsAndDecimal(cleaned) {
            var firstComma = cleaned.indexOf(',');
            var intDigits = (firstComma === -1 ? cleaned : cleaned.slice(0, firstComma)).replace(/\D/g, '');
            var decDigits = firstComma === -1 ? '' : cleaned.slice(firstComma + 1).replace(/\D/g, '').slice(0, 2);
            return {intDigits: intDigits, decDigits: decDigits, hasComma: firstComma !== -1};
        }

        function formatDisplayValue(parts) {
            var intFormatted = parts.intDigits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            if (!parts.hasComma) {
                return intFormatted;
            }
            return intFormatted + ',' + parts.decDigits;
        }

        function toRawValue(parts) {
            if (!parts.intDigits && !parts.decDigits) {
                return '';
            }
            var intDigits = parts.intDigits || '0';
            return parts.decDigits ? intDigits + '.' + parts.decDigits : intDigits;
        }

        function sync() {
            var cleaned = display.value.replace(/[^\d,]/g, '');
            var parts = splitDigitsAndDecimal(cleaned);
            display.value = formatDisplayValue(parts);
            hidden.value = toRawValue(parts);
        }

        display.addEventListener('input', sync);

        if (hidden.value) {
            display.value = formatDisplayValue(splitDigitsAndDecimal(String(hidden.value).replace('.', ',')));
        }
    })();
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

    })();
</script>

{{-- Ítems de "Trabajo a realizar": un ítem recién agregado no existe en el
     servidor hasta que tiene algo real que guardar (texto tipeado o una
     foto) — ensureItemCreated() se encarga de crearlo recién en ese
     momento, así nunca queda un registro vacío en la base si se agrega un
     ítem y no se llega a cargar nada. Los ítems ya existentes (cargados
     por el foreach de arriba) ya tienen data-item-id de entrada. --}}
<script>
    (function () {
        var container = document.getElementById('work-items');
        var addButton = document.getElementById('add-work-item');
        var template = document.getElementById('work_item_template');
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        var itemsBaseUrl = @json(route('relevador.items.store', $relevamiento));
        var debounceTimers = {};
        var creationPromises = {};
        var nextClientId = 0;

        function itemUrl(itemId) {
            return itemsBaseUrl + '/' + itemId;
        }

        function itemPhotosUrl(itemId) {
            return itemsBaseUrl + '/' + itemId + '/fotos';
        }

        function getClientId(itemEl) {
            if (!itemEl.dataset.clientId) {
                itemEl.dataset.clientId = 'c' + (nextClientId++);
            }
            return itemEl.dataset.clientId;
        }

        // Los checkboxes (ej. "Incluye retiro") no tienen el estado en
        // .value (eso es fijo, sea cual sea el checked) — hay que leer
        // .checked y mandar '1'/'0' explícito.
        function fieldValue(el) {
            return el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
        }

        function collectFields(itemEl) {
            var data = {};
            itemEl.querySelectorAll('[data-item-field]').forEach(function (el) {
                data[el.dataset.itemField] = fieldValue(el);
            });
            return data;
        }

        function hasAnyContent(fields) {
            return Object.keys(fields).some(function (key) {
                var value = fields[key];
                // '0' es el estado por defecto de "Incluye retiro" sin
                // tocar — no cuenta como contenido real (si no, cualquier
                // ítem nuevo "vacío" se crearía solo por tener ese campo).
                return value && value.trim() && value !== '0';
            });
        }

        // Crea el ítem en el servidor la primera vez que hace falta (texto
        // real o una foto). Si ya se está creando (dos campos debounceados
        // casi al mismo tiempo), reutiliza la misma promesa en vez de
        // duplicar el registro.
        function ensureItemCreated(itemEl, initialFields) {
            if (itemEl.dataset.itemId) {
                return Promise.resolve(itemEl.dataset.itemId);
            }

            var clientId = getClientId(itemEl);
            if (creationPromises[clientId]) {
                return creationPromises[clientId];
            }

            var data = new FormData();
            data.append('_token', csrfToken);
            Object.keys(initialFields || {}).forEach(function (key) {
                data.append(key, initialFields[key]);
            });

            creationPromises[clientId] = fetch(itemsBaseUrl, {
                method: 'POST',
                body: data,
                headers: {'Accept': 'application/json'},
            }).then(function (response) {
                return response.json();
            }).then(function (item) {
                itemEl.dataset.itemId = item.id;
                delete creationPromises[clientId];
                return item.id;
            });

            return creationPromises[clientId];
        }

        addButton.addEventListener('click', function () {
            container.appendChild(template.content.cloneNode(true));
        });

        // Los campos de texto disparan "input" mientras se escribe; el
        // checkbox de "Incluye retiro" dispara "change" al tildarlo — ambos
        // eventos pasan por acá y comparten el mismo debounce por campo.
        function scheduleItemFieldSave(target) {
            var field = target.dataset.itemField;
            if (!field) {
                return;
            }

            var itemEl = target.closest('[data-work-item]');
            var clientId = getClientId(itemEl);
            var timerKey = clientId + ':' + field;
            var value = fieldValue(target);

            clearTimeout(debounceTimers[timerKey]);
            debounceTimers[timerKey] = setTimeout(function () {
                if (itemEl.dataset.itemId) {
                    var data = new FormData();
                    data.append('_token', csrfToken);
                    data.append(field, value);

                    fetch(itemUrl(itemEl.dataset.itemId), {
                        method: 'POST',
                        body: data,
                        headers: {'Accept': 'application/json'},
                    });
                    return;
                }

                var fields = collectFields(itemEl);
                if (hasAnyContent(fields)) {
                    ensureItemCreated(itemEl, fields);
                }
            }, 1000);
        }

        container.addEventListener('input', function (event) {
            if (event.target.dataset.itemField) {
                scheduleItemFieldSave(event.target);
            }
        });

        container.addEventListener('change', function (event) {
            if (event.target.matches('[data-item-field][type="checkbox"]')) {
                scheduleItemFieldSave(event.target);
                return;
            }

            if (!event.target.matches('[data-item-photo-input]')) {
                return;
            }

            var input = event.target;
            var itemEl = input.closest('[data-work-item]');
            var grid = itemEl.querySelector('[data-item-photo-grid]');
            // input.files es una FileList "viva": si se lee la referencia acá
            // pero se la recorre recién dentro del .then() de más abajo (async),
            // el input.value = '' de unas líneas después ya la vació para
            // cuando el callback corre, y la foto nunca llega a subirse sin
            // ningún error visible. Por eso se copia a un array común ya
            // mismo, antes de cualquier operación asincrónica.
            var files = Array.prototype.slice.call(input.files);
            input.value = '';

            ensureItemCreated(itemEl, collectFields(itemEl)).then(function (itemId) {
                files.forEach(function (file) {
                    var data = new FormData();
                    data.append('photo', file);
                    data.append('_token', csrfToken);

                    fetch(itemPhotosUrl(itemId), {
                        method: 'POST',
                        body: data,
                        headers: {'Accept': 'application/json'},
                    }).then(function (response) {
                        return response.json();
                    }).then(function (photo) {
                        var cell = document.createElement('div');
                        cell.className = 'relative aspect-square rounded-lg overflow-hidden bg-gray-100';
                        cell.dataset.photoId = photo.id;
                        cell.innerHTML = '<img src="' + photo.url + '" alt="Foto del ítem" class="w-full h-full object-cover">'
                            + '<button type="button" data-remove-item-photo class="absolute top-1 right-1 bg-black/60 text-white text-xs w-5 h-5 rounded-full leading-none">✕</button>';
                        grid.appendChild(cell);
                    });
                });
            });
        });

        container.addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-item]')) {
                var itemEl = event.target.closest('[data-work-item]');

                if (!itemEl.dataset.itemId) {
                    // Nunca se guardó en el servidor, no hay nada que borrar ahí.
                    itemEl.remove();
                    return;
                }

                if (! confirm('¿Eliminar este ítem de trabajo?')) {
                    return;
                }

                fetch(itemUrl(itemEl.dataset.itemId), {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
                }).then(function (response) {
                    if (response.ok) {
                        itemEl.remove();
                    }
                });

                return;
            }

            if (event.target.matches('[data-remove-item-photo]')) {
                var cell = event.target.closest('[data-photo-id]');
                var itemId = event.target.closest('[data-work-item]').dataset.itemId;

                fetch(itemPhotosUrl(itemId) + '/' + cell.dataset.photoId, {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
                }).then(function (response) {
                    if (response.ok) {
                        cell.remove();
                    }
                });
            }
        });
    })();
</script>
