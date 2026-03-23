@extends('layouts.app')

@section('meta_title', $post->meta_title ?? $post->title)
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | ' . strip_tags($post->excerpt ?? Str::limit($post->content, 150)))

@section('og_image', $post->featured_image ? Storage::url($post->featured_image) : asset('images/default-og.jpg'))

@section('content')
    <nav class="mb-6 text-sm text-gray-600">
        <a href="/" class="hover:text-green-700">Inicio</a> /
        <a href="/{{ $post->category->slug }}" class="hover:text-green-700">{{ $post->category->name }}</a> /
        <span class="text-gray-800">{{ $post->title }}</span>
    </nav>

    <article class="bg-white rounded-lg shadow-lg overflow-hidden p-8">
        <!-- Imagen destacada -->
        @if($post->featured_image)
            <div class="mb-6 rounded-lg overflow-hidden">
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto object-cover">
            </div>
        @endif

        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $post->title }}</h1>
        
        @if($post->subtitle)
            <p class="text-xl text-gray-600 mb-6">{{ $post->subtitle }}</p>
        @endif

        <div class="prose max-w-none">{!! nl2br(e($post->content)) !!}</div>

        <!-- Galería de imágenes -->
        @if($post->gallery_images && count($post->gallery_images) > 0)
            <div class="mt-8">
                <h3 class="font-semibold mb-4">Galería de imágenes</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($post->gallery_images as $image)
                        <div class="rounded-lg overflow-hidden shadow-sm">
                            <img src="{{ Storage::url($image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($post->tags->count() > 0)
        <div class="mt-8">
            <h3 class="font-semibold mb-2">Etiquetas:</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <a href="/tag/{{ $tag->slug }}" class="bg-gray-200 px-3 py-1 rounded-full text-sm hover:bg-green-600 hover:text-white transition">#{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
@endsection

@php
    $featuredImageUrl = $post->featured_image ? Storage::url($post->featured_image) : asset('images/default-post.jpg');

    $blogPosting = [
        "@context" => "https://schema.org",
        "@type" => "BlogPosting",
        "headline" => $post->title,
        "description" => $post->excerpt ?? strip_tags(Str::limit($post->content, 150)),
        "image" => $featuredImageUrl,
        "datePublished" => $post->published_at->toIso8601String(),
        "dateModified" => $post->updated_at->toIso8601String(),
        "author" => [
            "@type" => "Organization",
            "name" => "Limpieza de Terrenos",
            "url" => url('/')
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "Limpieza de Terrenos",
            "logo" => [
                "@type" => "ImageObject",
                "url" => asset('images/logo.jpg')
            ]
        ],
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => url()->current()
        ]
    ];

    $breadcrumbPost = [
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
                "name" => $post->category->name,
                "item" => url('/' . $post->category->slug)
            ],
            [
                "@type" => "ListItem",
                "position" => 3,
                "name" => $post->title,
                "item" => url()->current()
            ]
        ]
    ];
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode($blogPosting, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbPost, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush