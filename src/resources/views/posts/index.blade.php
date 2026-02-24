@extends('layouts.app')

@section('meta_title', 'Todos los trabajos - Limpieza de Terrenos')
@section('meta_description', 'Galería completa de trabajos de limpieza y desmalezado realizados en zona norte')

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
            <input type="text" 
                   name="search" 
                   placeholder="Buscar trabajos..." 
                   value="{{ request('search') }}"
                   class="border rounded-lg px-4 py-2 w-full">
        </form>
    </div>

    {{-- Grid de posts --}}
    @if($posts->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <a href="/categoria/{{ $post->category->slug }}/{{ $post->slug }}" 
           class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden group">
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                📸 Trabajo realizado
            </div>
            
            <div class="p-4">
                <span class="text-sm text-green-600 font-semibold">{{ $post->category->name }}</span>
                <h2 class="font-bold text-lg mt-1">{{ $post->title }}</h2>
                @if($post->location)
                <p class="text-gray-600 text-sm mt-1">📍 {{ $post->location }}</p>
                @endif
                <p class="text-gray-500 text-sm mt-2">{{ $post->formatted_date }}</p>
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
        <p class="text-gray-500">No hay trabajos publicados aún.</p>
    </div>
    @endif
@endsection