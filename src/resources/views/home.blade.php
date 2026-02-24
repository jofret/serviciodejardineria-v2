@extends('layouts.app')

@section('meta_title', 'Limpieza y Desmalezado de Terrenos | Servicios profesionales en zona norte')
@section('meta_description', 'Empresa líder en limpieza de terrenos, desmalezado y roza. Trabajamos en Pilar, Campana, Escobar, Tigre y zona norte. Presupuesto sin cargo.')

@section('content')
    {{-- Hero Section --}}
    <section class="bg-gradient-to-r from-green-700 to-green-600 text-white rounded-lg p-12 mb-12 text-center shadow-xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Limpieza y Desmalezado de Terrenos</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Servicio profesional en zona norte y Gran Buenos Aires. Respuesta rápida, presupuesto sin cargo.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/presupuesto" class="bg-yellow-500 text-gray-900 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-yellow-400 transition transform hover:scale-105">
                Solicitar presupuesto
            </a>
            <a href="/categoria/trabajos-realizados" class="bg-white text-green-700 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition transform hover:scale-105">
                Ver trabajos
            </a>
        </div>
    </section>

    {{-- Últimos trabajos destacados --}}
    @if($featuredPosts->count() > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Trabajos destacados</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredPosts as $post)
            <a href="/categoria/{{ $post->category->slug }}/{{ $post->slug }}" class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden group">
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
                    <span class="text-sm text-green-600 font-semibold">{{ $post->category->name }}</span>
                    <h3 class="font-bold text-lg mt-1 group-hover:text-green-700">{{ $post->title }}</h3>
                    @if($post->location)
                    <p class="text-gray-600 text-sm mt-1">📍 {{ $post->location }}</p>
                    @endif
                    <p class="text-gray-600 text-sm mt-2">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}</p>
                    <span class="text-sm text-gray-500 mt-2 block">{{ $post->formatted_date }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Categorías --}}
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Nuestros servicios</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($categories as $category)
            <a href="/categoria/{{ $category->slug }}" 
               class="bg-white p-6 rounded-lg shadow text-center hover:shadow-lg transition border-2 border-transparent hover:border-green-500">
                <div class="text-4xl mb-2">
                    @switch($category->slug)
                        @case('desmalezado') 🌿 @break
                        @case('limpieza') 🧹 @break
                        @case('roza') 🚜 @break
                        @case('precios') 💰 @break
                        @case('prevencion') 🔥 @break
                        @case('legal') ⚖️ @break
                        @case('consejos') 💡 @break
                        @case('zonas') 📍 @break
                        @default 📋
                    @endswitch
                </div>
                <h3 class="font-semibold">{{ $category->name }}</h3>
            </a>
            @endforeach
        </div>
    </section>

    {{-- Tags populares --}}
    @if($popularTags->count() > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Búsquedas populares</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($popularTags as $tag)
            <a href="/tag/{{ $tag->slug }}" 
               class="bg-gray-200 px-4 py-2 rounded-full text-sm hover:bg-green-600 hover:text-white transition">
                #{{ $tag->name }}
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Por qué elegirnos --}}
    <section class="bg-white rounded-lg shadow p-8 mb-12">
        <h2 class="text-2xl font-bold mb-8 text-center">¿Por qué elegirnos?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-4xl mb-2">⚡</div>
                <h3 class="font-bold mb-2">Respuesta rápida</h3>
                <p class="text-gray-600">Presupuesto en 24hs y ejecución inmediata</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-2">📸</div>
                <h3 class="font-bold mb-2">Antes/Después</h3>
                <p class="text-gray-600">Comprobamos nuestro trabajo con fotos reales</p>
            </div>
            <div class="text-center">
                <div class="text-4xl mb-2">✅</div>
                <h3 class="font-bold mb-2">Garantía</h3>
                <p class="text-gray-600">Servicio profesional con maquinaria adecuada</p>
            </div>
        </div>
    </section>
@endsection