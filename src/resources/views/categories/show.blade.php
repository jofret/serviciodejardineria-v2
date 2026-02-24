@extends('layouts.app')

@section('meta_title', $metaTitle ?? $category->name . ' - Limpieza de Terrenos')
@section('meta_description', $metaDescription ?? $category->description ?? 'Trabajos de ' . $category->name . ' realizados en zona norte')

@section('content')
    {{-- Encabezado de categoría --}}
    <section class="bg-green-700 text-white rounded-lg p-8 mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="/" class="text-green-200 hover:text-white">Inicio</a>
            <span class="text-green-300">/</span>
            <span>{{ $category->name }}</span>
        </div>
        
        <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
        @if($category->description)
        <p class="text-green-100 max-w-2xl">{{ $category->description }}</p>
        @endif
    </section>

    {{-- Posts de la categoría --}}
    @if($posts->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <a href="/categoria/{{ $category->slug }}/{{ $post->slug }}" 
           class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden group">
            @if($post->getFirstMediaUrl('featured', 'thumb'))
            <img src="{{ $post->getFirstMediaUrl('featured', 'thumb') }}" 
                 alt="{{ $post->title }}"
                 class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
            @else
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                📸 Sin imagen
            </div>
            @endif
            
            <div class="p-4">
                <h2 class="font-bold text-lg mb-2 group-hover:text-green-700">{{ $post->title }}</h2>
                
                @if($post->location)
                <p class="text-gray-600 text-sm mb-2">📍 {{ $post->location }}</p>
                @endif
                
                <p class="text-gray-600 text-sm mb-3">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}</p>
                
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>{{ $post->formatted_date }}</span>
                    @if($post->tags->count() > 0)
                    <span class="bg-gray-100 px-2 py-1 rounded">
                        {{ $post->tags->count() }} etiquetas
                    </span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Paginación --}}
    <div class="mt-8">
        {{ $posts->links() }}
    </div>
    @else
    <div class="text-center py-12 bg-white rounded-lg">
        <p class="text-gray-500">No hay trabajos publicados en esta categoría aún.</p>
    </div>
    @endif
@endsection