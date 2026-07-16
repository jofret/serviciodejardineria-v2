@extends('layouts.app')

@section('meta_title', $metaTitle ?? 'Posts etiquetados con ' . $tag->name)
@section('meta_description', 'AltoParque WhatsApp ✅ 11 7178-9529 | Trabajos etiquetados con ' . $tag->name . ' en zona norte.')
@section('meta_keywords', strtolower($tag->name) . ', servicio de jardinería, zona norte, caba')

{{-- Las páginas siguientes de un tag duplican contenido ya indexado en la página 1 --}}
@if($posts->currentPage() > 1)
    @section('meta_robots', 'noindex, follow')
@endif

@section('content')
    <section class="bg-green-700 text-white p-8 mb-8">
        <div class="container mx-auto px-4">
            <div class="flex items-center gap-4 mb-4">
                <a href="/" class="text-green-200 hover:text-white">Inicio</a>
                <span class="text-green-300">/</span>
                <span>Etiqueta: {{ $tag->name }}</span>
            </div>
            <h1 class="text-3xl font-bold mb-2">#{{ $tag->name }}</h1>
            <p class="text-green-100">{{ $posts->total() }} trabajos encontrados</p>
        </div>
    </section>

    <div class="container mx-auto px-4">
        @if($posts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($posts as $post)
            <a href="{{ route('post.show', $post) }}" class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden">
                @if($post->getFirstMediaUrl('featured', 'thumb'))<img src="{{ $post->getFirstMediaUrl('featured', 'thumb') }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">@endif
                <div class="p-4">
                    <span class="text-sm text-green-600">{{ $post->category->name }}</span>
                    <h2 class="font-bold text-lg mt-1">{{ $post->title }}</h2>
                    @if($post->location)<p class="text-gray-600 text-sm mt-1">📍 {{ $post->location }}</p>@endif
                    <p class="text-gray-500 text-sm mt-2">{{ $post->formatted_date }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
        @else
        <div class="text-center py-12 bg-white rounded-lg"><p class="text-gray-500">No hay trabajos con esta etiqueta aún.</p></div>
        @endif
    </div>
@endsection

@php
    $tagSchema = [
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "Posts etiquetados con #" . $tag->name,
        "description" => "Trabajos de jardinería etiquetados con " . $tag->name . " en zona norte",
        "url" => url()->current(),
        "keywords" => $tag->name
    ];

    $breadcrumbTag = [
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
                "name" => $tag->name,
                "item" => url()->current()
            ]
        ]
    ];
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode($tagSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbTag, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush