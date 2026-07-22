<div data-work-item data-item-id="{{ $item->id }}" class="border border-gray-200 rounded-lg p-3 space-y-2">
    <div class="flex items-center justify-between">
        <span class="text-xs font-medium text-gray-500">Ítem de trabajo</span>
        <button type="button" data-remove-item class="text-red-600 text-xs">Eliminar ítem</button>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción del trabajo</label>
        <textarea data-item-field="description" rows="2" placeholder="Ej: poda del árbol grande"
                  spellcheck="true" lang="es" autocorrect="on"
                  class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">{{ $item->description }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
        <textarea data-item-field="observations" rows="2" placeholder="Detalles adicionales"
                  spellcheck="true" lang="es" autocorrect="on"
                  class="w-full rounded-lg border border-gray-300 text-base py-2 px-3">{{ $item->observations }}</textarea>
    </div>

    <hr class="border-gray-200">
    <label class="flex items-center gap-2 text-sm text-gray-700 my-3">
        <input type="checkbox" data-item-field="includes_pickup" class="rounded" {{ $item->includes_pickup ? 'checked' : '' }}>
        Incluye retiro
    </label>
    <hr class="border-gray-200">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fotos del ítem</label>
        <input type="file" data-item-photo-input multiple accept="image/*" capture="environment" class="w-full text-sm">
        <div data-item-photo-grid class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-2">
            @foreach ($item->getMedia('photos') as $photo)
                <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100" data-photo-id="{{ $photo->id }}">
                    <img src="{{ $photo->getUrl() }}" alt="Foto del ítem" class="w-full h-full object-cover">
                    <button type="button" data-remove-item-photo class="absolute top-1 right-1 bg-black/60 text-white text-xs w-5 h-5 rounded-full leading-none">✕</button>
                </div>
            @endforeach
        </div>
    </div>
</div>
