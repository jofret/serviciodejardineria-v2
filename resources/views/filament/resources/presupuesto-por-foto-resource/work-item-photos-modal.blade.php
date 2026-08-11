@if ($photos->isNotEmpty())
    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
        @foreach ($photos as $photo)
            <a href="{{ $photo->getUrl() }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                <img src="{{ $photo->getUrl() }}" alt="Foto del ítem" class="w-full h-full object-cover">
            </a>
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500">Este ítem no tiene fotos cargadas.</p>
@endif
