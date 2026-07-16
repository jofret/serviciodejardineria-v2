@extends('layouts.app')

@section('meta_title', 'Limpieza y Desmalezado de Terrenos en Zona Norte')
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | Servicio profesional en zona norte y Gran Buenos Aires. Respuesta rápida, presupuesto sin cargo. | Tags: desmalezado, limpieza, roza, pilar, escobar, campos, terrenos, maquinaria')
@section('meta_keywords', 'limpieza de terrenos, desmalezado, roza de campos, zona norte, desmalezado pilar, limpieza escobar, presupuesto desmalezado, prevención de incendios')

@section('content')
    {{-- BANNER PRINCIPAL (sin lazy loading, es LCP) --}}
    <section id="inicio" class="relative text-white flex items-center overflow-hidden" style="min-height: calc(100vh - 80px);">
        <div class="absolute inset-0 z-0">
            <picture>
                <source srcset="{{ asset('images/banner.webp') }}" media="(min-width: 1024px)" type="image/webp">
                <source srcset="{{ asset('images/banner-768w.webp') }}" media="(max-width: 1023px)" type="image/webp">
                <img src="{{ asset('images/banner-768w.webp') }}" 
                     alt="Terreno con maleza" 
                     class="w-full h-full object-cover"
                     loading="eager">
            </picture>
            <div class="absolute inset-0 bg-gradient-to-r from-green-900/90 to-green-800/80"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 py-12">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-block bg-yellow-500 text-gray-900 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                    ⭐ +15 años de experiencia
                </div>

                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight drop-shadow-lg">
                    Limpieza y Desmalezado <br>
                    <span class="text-yellow-300">de Terrenos</span>
                </h1>

                <p class="text-xl md:text-2xl mb-10 text-green-100 max-w-3xl mx-auto drop-shadow-lg">
                    Servicio profesional en zona norte y Gran Buenos Aires. 
                    Respuesta rápida, presupuesto sin cargo.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#contacto-formulario" 
                       class="group bg-yellow-500 text-gray-900 px-8 py-4 rounded-lg text-lg font-bold hover:bg-yellow-400 transition-all transform hover:scale-105 shadow-xl">
                        <i class="fas fa-calculator group-hover:rotate-12 transition"></i>
                        Solicitar presupuesto
                    </a>
                    <a href="{{ route('posts.index') }}"
                       class="group bg-white text-green-700 px-8 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
                        <i class="fas fa-images group-hover:scale-110 transition"></i>
                        Ver trabajos realizados
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-16 bg-white rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-green-700">500+</div>
                    <div class="text-gray-600">Terrenos limpiados</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-green-700">15+</div>
                    <div class="text-gray-600">Años de experiencia</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-green-700">10</div>
                    <div class="text-gray-600">Localidades</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-green-700">24/7</div>
                    <div class="text-gray-600">Respuesta urgente</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trabajos destacados (con Spatie y lazy loading) --}}
    @if($featuredPosts->count() > 0)
    <section id="trabajos" class="py-16 bg-white rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Trabajos Destacados</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($featuredPosts as $post)
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover border border-gray-100">
                    <a href="{{ route('post.show', $post) }}" aria-label="Ver trabajo: {{ $post->title }}">
                        @if($post->getFirstMediaUrl('featured', 'thumb'))
                            <div class="w-full h-56 overflow-hidden">
                                <img src="{{ $post->getFirstMediaUrl('featured', 'thumb') }}" 
                                     alt="{{ $post->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        @elseif($post->getFirstMedia('gallery'))
                            <div class="w-full h-56 overflow-hidden">
                                <img src="{{ $post->getFirstMediaUrl('gallery', 'thumb') }}" 
                                     alt="{{ $post->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div class="w-full h-56 bg-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                        @endif
                    </a>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-green-600 font-semibold">{{ $post->category->name }}</span>
                            @if($post->location)
                                <span class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $post->location }}</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-xl mb-2">
                            <a href="{{ route('post.show', $post) }}" class="hover:text-green-700">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4 line-clamp-3">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500"><i class="far fa-calendar mr-1"></i>{{ $post->formatted_date }}</span>
                            <a href="{{ route('post.show', $post) }}" 
                               class="text-green-700 hover:text-green-800 font-medium"
                               aria-label="Ver más detalles de {{ $post->title }}">
                                Ver más <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Servicios --}}
    <section id="servicios" class="py-16 bg-gray-50 rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Nuestros Servicios</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 justify-items-center max-w-4xl mx-auto">
                @foreach($categories as $category)
                <a href="{{ route('category.show', $category) }}"
                   class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition card-hover text-center"
                   aria-label="Servicio de {{ $category->name }}">
                    <div class="text-5xl mb-4">
                        @switch($category->slug)
                            @case('desmalezado-de-terrenos') 🌿 @break
                            @case('limpieza-de-terrenos') 🧹 @break
                            @case('poda-de-altura') 📍 @break
                            @case('precios') 💰 @break
                            @case('prevencion') 🔥 @break
                            @case('legal') ⚖️ @break
                            @case('consejos') 💡 @break
                            @case('zonas') 🚜 @break
                            @default 📋
                        @endswitch
                    </div>
                    <h3 class="font-bold text-lg mb-2">{{ $category->name }}</h3>
                    <p class="text-gray-600 text-sm">{{ Str::limit($category->description, 60) }}</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonios --}}
    @include('partials.testimonios')
    
    {{-- FORMULARIO DE CONTACTO --}}
    @include('partials.contact-form', ['serviceCategories' => $categories])

    {{-- Script para manejar zonas y scroll --}}
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

        @if(session('success') || $errors->any())
            setTimeout(function() {
                const formulario = document.getElementById('contacto-formulario');
                if (formulario) {
                    formulario.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }
            }, 100);
        @endif
    });
    </script>

    {{-- Tags populares --}}
    @if($popularTags->count() > 0)
    <section class="py-16 bg-gray-50 rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Búsquedas populares</h2>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach($popularTags as $tag)
                <a href="/tag/{{ $tag->slug }}" class="bg-white px-4 py-2 rounded-full text-gray-700 hover:bg-green-700 hover:text-white transition shadow-sm" aria-label="Ver trabajos etiquetados como #{{ $tag->name }}">
                    #{{ $tag->name }}
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA Section --}}
    <section class="py-16 bg-green-800 text-white rounded-xl shadow-lg mb-8">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">¿Necesitas limpiar un terreno?</h2>
            <p class="text-xl mb-8 text-green-200">Respondemos en menos de 24 horas</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#contacto-formulario" class="bg-white text-green-800 px-8 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition" aria-label="Enviar consulta por correo">
                    <i class="fas fa-envelope mr-2"></i> Enviar consulta
                </a>
                <a href="https://wa.me/5491171789529" target="_blank" class="bg-green-600 text-white px-8 py-4 rounded-lg text-lg font-bold hover:bg-green-700 transition border-2 border-white" aria-label="Contactar por WhatsApp">
                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp directo
                </a>
            </div>
        </div>
    </section>

    {{-- SCHEMAS --}}
    @php
        $localBusiness = [
            "@context" => "https://schema.org",
            "@type" => "LocalBusiness",
            "name" => "Limpieza de Terrenos",
            "image" => asset('images/og-default.jpg'),
            "telephone" => "+54 11 7178-9529",
            "email" => "info@serviciodejardineria.com.ar",
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => "Buenos Aires",
                "addressRegion" => "Buenos Aires",
                "addressCountry" => "AR"
            ],
            "openingHours" => "Mo-Sa 08:00-18:00",
            "priceRange" => "$$",
            "areaServed" => ["CABA", "Zona Norte", "Gran Buenos Aires"]
        ];

        $webSite = [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => "Limpieza de Terrenos",
            "url" => url('/'),
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => route('posts.index') . '?search={search_term_string}',
                "query-input" => "required name=search_term_string"
            ]
        ];
    @endphp

    @push('schema')
    <script type="application/ld+json">
    {!! json_encode($localBusiness, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($webSite, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endpush
@endsection