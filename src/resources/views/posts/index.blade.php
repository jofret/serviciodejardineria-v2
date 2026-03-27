@extends('layouts.app')

@section('meta_title', 'Todos los trabajos - Limpieza de Terrenos')
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | Galería completa de trabajos de limpieza y desmalezado realizados en zona norte. Antes y después reales. | Tags: desmalezado, limpieza, terrenos, maquinaria, zona norte, pilar, escobar, campos')

@section('content')
    <section class="bg-green-700 text-white rounded-lg p-8 mb-8">
        <h1 class="text-3xl font-bold mb-2">Todos los trabajos</h1>
        <p class="text-green-100">Conocé nuestros trabajos realizados en zona norte y Gran Buenos Aires</p>
    </section>

    {{-- Filtros --}}
    <div class="mb-6 flex flex-wrap gap-4">
        <select class="border rounded-lg px-4 py-2" onchange="window.location.href = this.value">
            <option value="{{ url('/posts') }}">Todas las categorías</option>
            @foreach($categories as $cat)
                <option value="{{ url('/posts?category=' . $cat->slug) }}" 
                    {{ request('category') == $cat->slug ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>

        <form method="GET" class="flex-1">
            <input type="text" name="search" placeholder="Buscar trabajos..." value="{{ request('search') }}" class="border rounded-lg px-4 py-2 w-full">
        </form>
    </div>

    {{-- Grid de posts --}}
    @if($posts->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <a href="/{{ $post->category->slug }}/{{ $post->slug }}" class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden group">
            {{-- Imagen destacada optimizada (prioriza WebP) --}}
            @if($post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}" 
                     alt="{{ $post->title }}" 
                     class="w-full h-48 object-cover group-hover:scale-105 transition">
            @elseif($post->gallery_images && count($post->gallery_images) > 0)
                <img src="{{ Storage::url($post->gallery_images[0]) }}" 
                     alt="{{ $post->title }}" 
                     class="w-full h-48 object-cover group-hover:scale-105 transition">
            @else
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">📸 Sin imagen</div>
            @endif
            
            <div class="p-4">
                <span class="text-sm text-green-600 font-semibold">{{ $post->category->name }}</span>
                <h2 class="font-bold text-lg mt-1">{{ $post->title }}</h2>
                @if($post->location)<p class="text-gray-600 text-sm mt-1">📍 {{ $post->location }}</p>@endif
                <p class="text-gray-500 text-sm mt-2">{{ $post->formatted_date }}</p>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
    @else
    <div class="text-center py-12 bg-white rounded-lg"><p class="text-gray-500">No hay trabajos publicados aún.</p></div>
    @endif
@endsection

@php
    $collectionPage = [
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "Todos los trabajos de limpieza de terrenos",
        "description" => "Galería completa de trabajos de limpieza y desmalezado realizados en zona norte y Gran Buenos Aires",
        "url" => url()->current(),
        "mainEntity" => [
            "@type" => "ItemList",
            "itemListElement" => []
        ]
    ];

    foreach ($posts as $index => $post) {
        $position = $index + 1 + (($posts->currentPage() - 1) * $posts->perPage());
        $collectionPage['mainEntity']['itemListElement'][] = [
            "@type" => "ListItem",
            "position" => $position,
            "url" => url('/' . $post->category->slug . '/' . $post->slug),
            "name" => $post->title
        ];
    }

    $breadcrumbList = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type" => "ListItem",
                "position" => 1,
                "name" => "Inicio",
                "item" => url('/')
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "name" => "Todos los trabajos",
                "item" => url()->current()
            ]
        ]
    ];
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode($collectionPage, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbList, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush