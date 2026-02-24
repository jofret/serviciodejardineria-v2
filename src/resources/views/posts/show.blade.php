@extends('layouts.app')

@section('meta_title', $post->meta_title)
@section('meta_description', $post->meta_description)
@section('og_image', $post->getFirstMediaUrl('featured', 'og-image') ?? asset('images/default-og.jpg'))

@section('content')
    {{-- Migas de pan --}}
    <nav class="mb-6 text-sm text-gray-600">
        <a href="/" class="hover:text-green-700">Inicio</a>
        <span class="mx-2">/</span>
        <a href="/categoria/{{ $post->category->slug }}" class="hover:text-green-700">
            {{ $post->category->name }}
        </a>
        <span class="mx-2">/</span>
        <span class="text-gray-800">{{ $post->title }}</span>
    </nav>

    <article class="bg-white rounded-lg shadow-lg overflow-hidden">
        {{-- Imagen destacada --}}
        @if($post->getFirstMediaUrl('featured'))
        <div class="relative h-96 w-full">
            <img src="{{ $post->getFirstMediaUrl('featured') }}" 
                 alt="{{ $post->title }}"
                 class="w-full h-full object-cover">
            
            @if($post->has_before_after)
            <span class="absolute top-4 left-4 bg-green-600 text-white px-3 py-1 rounded-full text-sm">
                📸 Antes/Después
            </span>
            @endif
        </div>
        @endif

        <div class="p-8">
            {{-- Título y metadatos --}}
            <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $post->title }}</h1>
            
            <div class="flex flex-wrap gap-4 text-gray-600 mb-6 pb-6 border-b">
                @if($post->location)
                <span>📍 {{ $post->location }}</span>
                @endif
                
                <span>📅 {{ $post->formatted_date }}</span>
                
                @if($post->client_name)
                <span>👤 {{ $post->client_name }}</span>
                @endif
            </div>

            {{-- Detalles del trabajo --}}
            @if($post->project_size || $post->project_duration || $post->machinery_used)
            <div class="bg-gray-50 p-4 rounded-lg mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($post->project_size)
                <div class="text-center">
                    <div class="text-2xl mb-1">📏</div>
                    <div class="font-semibold">{{ $post->project_size }}</div>
                    <div class="text-sm text-gray-600">Superficie</div>
                </div>
                @endif
                
                @if($post->project_duration)
                <div class="text-center">
                    <div class="text-2xl mb-1">⏱️</div>
                    <div class="font-semibold">{{ $post->project_duration }}</div>
                    <div class="text-sm text-gray-600">Duración</div>
                </div>
                @endif
                
                @if($post->machinery_used)
                <div class="text-center">
                    <div class="text-2xl mb-1">🚜</div>
                    <div class="font-semibold">{{ $post->machinery_used }}</div>
                    <div class="text-sm text-gray-600">Maquinaria</div>
                </div>
                @endif
            </div>
            @endif

            {{-- Contenido principal --}}
            <div class="prose max-w-none mb-8">
                {!! nl2br(e($post->content)) !!}
            </div>

            {{-- Galería de imágenes (antes/después) --}}
            @if($post->getMedia('gallery')->count() > 0)
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Galería de imágenes</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($post->getMedia('gallery') as $image)
                    <a href="{{ $image->getUrl() }}" target="_blank" class="block">
                        <img src="{{ $image->getUrl('thumb') }}" 
                             alt="Galería {{ $loop->iteration }}"
                             class="w-full h-32 object-cover rounded-lg hover:opacity-90 transition">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tags --}}
            @if($post->tags->count() > 0)
            <div class="mb-8">
                <h3 class="font-semibold mb-2">Etiquetas:</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                    <a href="/tag/{{ $tag->slug }}" 
                       class="bg-gray-200 px-3 py-1 rounded-full text-sm hover:bg-green-600 hover:text-white transition">
                        #{{ $tag->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Botón de WhatsApp --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                <p class="text-lg mb-3">📱 ¿Necesitas un trabajo similar?</p>
                <a href="https://wa.me/549XXXXXXXXX?text=Hola,%20vi%20el%20trabajo%20{{ urlencode($post->title) }}%20y%20quiero%20un%20presupuesto%20para%20mi%20terreno"
                   target="_blank"
                   class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transition">
                    Consultar por WhatsApp
                </a>
            </div>
        </div>
    </article>

    {{-- Posts relacionados --}}
    @if($relatedPosts->count() > 0)
    <section class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Trabajos relacionados</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($relatedPosts as $related)
            <a href="/categoria/{{ $related->category->slug }}/{{ $related->slug }}" 
               class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                @if($related->getFirstMediaUrl('featured', 'thumb'))
                <img src="{{ $related->getFirstMediaUrl('featured', 'thumb') }}" 
                     alt="{{ $related->title }}"
                     class="w-full h-32 object-cover">
                @endif
                <div class="p-3">
                    <h3 class="font-semibold text-sm">{{ $related->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif
@endsection