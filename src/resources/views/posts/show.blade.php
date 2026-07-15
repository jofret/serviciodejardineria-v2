@extends('layouts.app')

@section('meta_title', $post->meta_title ?? $post->title)
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | ' . strip_tags($post->excerpt ?? Str::limit($post->content, 150)))

@php
    $featuredMedia = $post->getFirstMedia('featured');
    $ogImageUrl = $featuredMedia ? ($featuredMedia->getUrl('webp') ?? $featuredMedia->getUrl()) : asset('images/default-og.jpg');
@endphp
@section('og_image', $ogImageUrl)

@section('content')
    {{-- Breadcrumbs --}}
    <div class="container mx-auto px-4 py-4">
        <nav class="text-sm text-gray-600">
            <a href="/" class="hover:text-green-700">Inicio</a> /
            <a href="/{{ $post->category->slug }}" class="hover:text-green-700">{{ $post->category->name }}</a> /
            <span class="text-gray-800">{{ $post->title }}</span>
        </nav>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-12">
            {{-- Contenido principal --}}
            <div class="lg:w-2/3">
                <article class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 md:p-8">
                        {{-- Imagen destacada (usando Spatie) --}}
                        @if($post->getFirstMedia('featured'))
                            <div class="mb-6 rounded-lg overflow-hidden">
                                <a href="{{ $post->getFirstMediaUrl('featured') }}" 
                                   data-lightbox="post-gallery" 
                                   data-title="{{ $post->title }} - Imagen destacada">
                                    <img src="{{ $post->getFirstMediaUrl('featured', 'thumb') ?? $post->getFirstMediaUrl('featured') }}" 
                                         alt="{{ $post->title }}" 
                                         loading="eager"
                                         class="w-full h-auto object-cover cursor-pointer hover:opacity-95 transition">
                                </a>
                            </div>
                        @endif

                        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $post->title }}</h1>

                        @if($post->subtitle)
                            <p class="text-xl text-gray-600 mb-6">{{ $post->subtitle }}</p>
                        @endif

                        <div class="prose max-w-none">
                            {!! $post->content !!}
                        </div>

                        {{-- Galería con lightbox (usando Spatie) --}}
                        @if($post->getMedia('gallery')->count() > 0)
                            <div class="mt-12">
                                <h3 class="text-2xl font-bold mb-4">Galería de imágenes</h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="gallery-container">
                                    @php
                                        $gallery = $post->getMedia('gallery');
                                        $total = $gallery->count();
                                        $initial = 6;
                                        $remaining = $total - $initial;
                                    @endphp

                                    {{-- Primeras 6 imágenes --}}
                                    @foreach($gallery->take($initial) as $index => $image)
                                        <a href="{{ $image->getUrl() }}" 
                                           data-lightbox="post-gallery" 
                                           data-title="{{ $post->title }} - Imagen {{ $index+1 }}">
                                            <div class="rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                                                <img src="{{ $image->getUrl('thumb') ?? $image->getUrl() }}" 
                                                     alt="{{ $post->title }} - imagen {{ $index+1 }}"
                                                     loading="lazy"
                                                     class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                                            </div>
                                        </a>
                                    @endforeach

                                    {{-- Imágenes restantes ocultas --}}
                                    @if($remaining > 0)
                                        <div id="hidden-gallery" style="display: none;">
                                            @foreach($gallery->skip($initial) as $index => $image)
                                                <a href="{{ $image->getUrl() }}" 
                                                   data-lightbox="post-gallery" 
                                                   data-title="{{ $post->title }} - Imagen {{ $initial + $index + 1 }}">
                                                    <div class="rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                                                        <img src="{{ $image->getUrl('thumb') ?? $image->getUrl() }}" 
                                                             alt="{{ $post->title }} - imagen {{ $initial + $index + 1 }}"
                                                             loading="lazy"
                                                             class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                        <div class="flex justify-center mt-4">
                                            <button id="show-all-gallery" class="bg-green-700 text-white px-6 py-2 rounded-lg hover:bg-green-800 transition">
                                                Ver todas ({{ $remaining }} más)
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Etiquetas del post --}}
                        @if($post->tags->count() > 0)
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <h3 class="font-semibold mb-2">Etiquetas de este post:</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($post->tags as $tag)
                                        <a href="/tag/{{ $tag->slug }}" class="bg-gray-200 px-3 py-1 rounded-full text-sm hover:bg-green-600 hover:text-white transition">
                                            #{{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            </div>

            {{-- Sidebar --}}
            <aside class="lg:w-1/3 space-y-8">
                {{-- Contacto rápido (igual) --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-phone-alt text-green-600"></i>
                        Contacto
                    </h3>
                    <div class="space-y-3 text-gray-600">
                        <p class="flex items-center gap-2"><i class="fas fa-map-marker-alt w-5 text-green-600"></i> Buenos Aires, Argentina</p>
                        <p class="flex items-center gap-2"><i class="fab fa-whatsapp w-5 text-green-600"></i> <a href="https://wa.me/5491171789529" class="hover:text-green-700">11 7178-9529</a></p>
                        <p class="flex items-center gap-2"><i class="fas fa-envelope w-5 text-green-600"></i> <a href="mailto:info@serviciodejardineria.com.ar" class="hover:text-green-700">info@serviciodejardineria.com.ar</a></p>
                        <p class="flex items-center gap-2"><i class="fab fa-facebook-f w-5 text-green-600"></i> <a href="https://www.facebook.com/cortamospastoyjardines" target="_blank" class="hover:text-green-700">Síguenos</a></p>
                    </div>
                </div>

                {{-- Categorías (igual) --}}
                @php
                    $categories = App\Models\Category::withCount('posts')->orderBy('name')->get();
                @endphp
                @if($categories->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-xl font-bold mb-4">Categorías</h3>
                    <ul class="space-y-2">
                        @foreach($categories as $cat)
                            <li><a href="/{{ $cat->slug }}" class="text-gray-600 hover:text-green-700 transition flex justify-between"><span>{{ $cat->name }}</span><span class="text-sm text-gray-400">({{ $cat->posts_count }})</span></a></li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Etiquetas populares (igual) --}}
                @php
                    $popularTags = App\Models\Tag::withCount('posts')->orderBy('posts_count', 'desc')->limit(10)->get();
                @endphp
                @if($popularTags->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-xl font-bold mb-4">Etiquetas populares</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($popularTags as $tag)
                            <a href="/tag/{{ $tag->slug }}" class="bg-gray-100 hover:bg-green-700 hover:text-white px-3 py-1 rounded-full text-sm transition">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Últimas publicaciones (igual) --}}
                @php
                    $recentPosts = App\Models\Post::where('is_published', true)
                                    ->where('id', '!=', $post->id)
                                    ->latest('published_at')
                                    ->limit(5)
                                    ->get();
                @endphp
                @if($recentPosts->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-xl font-bold mb-4">Últimas publicaciones</h3>
                    <div class="space-y-3">
                        @foreach($recentPosts as $recent)
                            <div class="border-b border-gray-100 last:border-0 pb-3 last:pb-0">
                                <a href="{{ url($recent->category->slug . '/' . $recent->slug) }}" class="block hover:text-green-700 transition">
                                    <p class="font-medium">{{ $recent->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $recent->formatted_date }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </aside>
        </div>
    </div>

    {{-- FORMULARIO DE CONTACTO (igual al de home) --}}
    @include('partials.contact-form')

    {{-- TESTIMONIOS (igual al de home) --}}
    @include('partials.testimonios')

    {{-- Script para el formulario de contacto (igual al de home) --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const zonaPrincipal = document.getElementById('zona_principal');
        const partido = document.getElementById('partido');
        const otraZonaContainer = document.getElementById('otra_zona_container');
        const otraZona = document.getElementById('otra_zona');
        const form = document.getElementById('contact-form');

        const localidades = {
            'CABA': ['Palermo', 'Belgrano', 'Recoleta', 'Puerto Madero', 
                     'Caballito', 'Almagro', 'Villa Crespo', 'Colegiales',
                     'Nuñez', 'Saavedra', 'Villa Urquiza', 'Villa Devoto'],
            'Zona Norte': ['Pilar', 'Escobar', 'Tigre', 'San Isidro', 'Vicente López',
                           'San Fernando', 'San Martín', 'Malvinas Argentinas', 'José C. Paz'],
            'Zona Oeste': ['Moreno', 'Merlo', 'Morón', 'Ituzaingó', 'Hurlingham',
                           'La Matanza', 'Tres de Febrero', 'San Miguel']
        };

        if (zonaPrincipal) {
            zonaPrincipal.addEventListener('change', function() {
                const selected = this.value;
                partido.innerHTML = '<option value="">Seleccione localidad...</option>';
                partido.disabled = false;
                partido.required = true;
                otraZonaContainer.classList.add('hidden');
                otraZona.required = false;

                if (selected === 'Otra') {
                    partido.disabled = true;
                    partido.required = false;
                    otraZonaContainer.classList.remove('hidden');
                    otraZona.required = true;
                } else if (selected && localidades[selected]) {
                    localidades[selected].forEach(function(l) {
                        const option = document.createElement('option');
                        option.value = l;
                        option.textContent = l;
                        partido.appendChild(option);
                    });
                }
            });

            if (form) {
                form.addEventListener('submit', function() {
                    if (zonaPrincipal.value === 'Otra') {
                        partido.required = false;
                        otraZona.required = true;
                    } else {
                        partido.required = true;
                        otraZona.required = false;
                    }
                });
            }

            @if(old('zona_principal'))
                setTimeout(() => {
                    zonaPrincipal.value = "{{ old('zona_principal') }}";
                    zonaPrincipal.dispatchEvent(new Event('change'));
                    @if(old('partido'))
                        setTimeout(() => {
                            partido.value = "{{ old('partido') }}";
                        }, 100);
                    @endif
                }, 100);
            @endif
        }

        @if(session('success') || $errors->any())
            setTimeout(function() {
                const formulario = document.getElementById('contacto-formulario');
                if (formulario) {
                    formulario.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        @endif
    });
    </script>
@endsection

@php
    $featuredMedia = $post->getFirstMedia('featured');
    $ogImageUrl = $featuredMedia ? ($featuredMedia->getUrl('webp') ?? $featuredMedia->getUrl()) : asset('images/default-og.jpg');

    $blogPosting = [
        "@context" => "https://schema.org",
        "@type" => "BlogPosting",
        "headline" => $post->title,
        "description" => $post->excerpt ?? strip_tags(Str::limit($post->content, 150)),
        "image" => $ogImageUrl,
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