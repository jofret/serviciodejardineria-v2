@extends('layouts.app')

@section('meta_title', 'AltoParque - Jardinería en Zona Norte y CABA')
@section('meta_description', 'Corte de pasto, poda de altura, desmalezado y mantenimiento de jardines WhatsApp ✅ 11 7178-9529 | Servicio profesional en Zona Norte de Buenos Aires y CABA. Respuesta rápida, presupuesto sin cargo.')
@section('meta_keywords', 'servicio de jardinería, corte de pasto, poda de altura, desmalezado de terrenos, mantenimiento de jardines, zona norte, caba, buenos aires')

@section('content')
    {{-- BANNER PRINCIPAL (sin lazy loading, es LCP) --}}
    <section id="inicio" class="relative text-white flex items-center overflow-hidden" style="min-height: calc(100vh - 80px);">
        <div class="absolute inset-0 z-0">
            <picture>
                <source srcset="{{ asset('images/banner.webp') }}" media="(min-width: 1024px)" type="image/webp">
                <source srcset="{{ asset('images/banner-768w.webp') }}" media="(max-width: 1023px)" type="image/webp">
                <img src="{{ asset('images/banner-768w.webp') }}" 
                     alt="Corte de pasto profesional"
                     class="w-full h-full object-cover"
                     loading="eager">
            </picture>
            <div class="absolute inset-0 bg-gradient-to-r from-green-900/90 to-green-800/80"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 py-12">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-block bg-yellow-500 text-gray-900 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                    ⭐ Servicio profesional en Zona Norte y CABA
                </div>

                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight drop-shadow-lg">
                    Corte de Pasto y <br>
                    <span class="text-yellow-300">Mantenimiento de Jardines</span>
                </h1>

                <p class="text-xl md:text-2xl mb-10 text-green-100 max-w-3xl mx-auto drop-shadow-lg">
                    Servicio profesional en Zona Norte de Buenos Aires y CABA. Respuesta rápida, presupuesto sin cargo.
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

    {{-- Buen Servicio / Equipo Dedicado / 24/7 Atención: mismo bloque "Top Feature" que
         serviciodejardineria.com.ar hoy en producción (includes/slider.blade.php),
         con la paleta y tipografía del sitio nuevo. --}}
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6 flex items-center gap-4">
                    <div class="shrink-0 w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check text-green-700 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Buen Servicio</h3>
                        <p class="text-sm text-gray-600">Tenemos precios justos y brindamos un buen servicio</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6 flex items-center gap-4">
                    <div class="shrink-0 w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-users text-green-700 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Equipo Dedicado</h3>
                        <p class="text-sm text-gray-600">Disfrutamos lo que hacemos</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6 flex items-center gap-4">
                    <div class="shrink-0 w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-phone text-green-700 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">24/7 Atención</h3>
                        <p class="text-sm text-gray-600">Atendemos su comunicación en cualquier horario</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Servicio de Corte de Pasto: misma estructura de contenido y layout que
         serviciodejardineria.com.ar hoy en producción (includes/que-hacemos.blade.php),
         con la paleta y tipografía del sitio nuevo. --}}
    <section id="que-hacemos" class="py-16 bg-white rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-end">
                <div class="lg:col-span-3">
                    <img src="{{ asset('images/corte-de-pasto-zona-norte.jpg') }}"
                         alt="Servicio de corte de pasto en zona norte"
                         class="rounded-xl w-full h-full object-cover shadow-md"
                         loading="lazy">
                </div>

                <div class="lg:col-span-6">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Servicio de Corte de Pasto</h2>
                    <p class="text-lg text-gray-600 mb-6">Puede contactarnos por WhatsApp. Podemos concertar una cita para ver el trabajo a realizar, no cobramos por el presupuesto. Atendemos casas, quintas y empresas.</p>
                    <a href="https://wa.me/5491171789529?text=Hola!%20Quiero%20informaci%C3%B3n%20sobre%20el%20servicio%20de%20corte%20de%20pasto"
                       target="_blank"
                       class="inline-flex items-center gap-2 bg-green-700 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-800 transition">
                        <i class="fab fa-whatsapp text-xl"></i> WhatsApp
                    </a>
                </div>

                <div class="lg:col-span-3 space-y-8">
                    <div class="border-l-4 border-green-600 pl-4">
                        <i class="fas fa-award text-3xl text-green-700 mb-3"></i>
                        <h4 class="font-bold text-gray-800 mb-1">Servicio Garantizado</h4>
                        <p class="text-sm text-gray-600">La plena satisfacción de nuestros clientes es nuestro principal objetivo.</p>
                    </div>
                    <div class="border-l-4 border-green-600 pl-4">
                        <i class="fas fa-users text-3xl text-green-700 mb-3"></i>
                        <h4 class="font-bold text-gray-800 mb-1">Equipo Dedicado</h4>
                        <p class="text-sm text-gray-600">Nos gusta nuestro trabajo, disfrutamos lo que hacemos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section: números reales de la propia base, nada inventado --}}
    @php
        $statTrabajos = App\Models\Post::where('is_published', true)->count();
        $statTestimonios = App\Models\Survey::whereNotNull('comment')->where('comment', '!=', '')->count();
        $statServicios = App\Models\Category::where('is_active', true)->count();
    @endphp
    <section class="py-16 bg-white rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-green-700">{{ $statTrabajos }}+</div>
                    <div class="text-gray-600">Trabajos realizados</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-green-700">{{ $statTestimonios }}</div>
                    <div class="text-gray-600">Clientes satisfechos</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-green-700">{{ $statServicios }}</div>
                    <div class="text-gray-600">Servicios de jardinería</div>
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
                            @case('corte-de-pasto-y-jardineria') 🌱 @break
                            @case('desmalezado-de-terrenos') 🌿 @break
                            @case('corte-de-cercos-y-enredaderas') ✂️ @break
                            @case('poda-de-altura') 🌳 @break
                            @case('corte-de-pasto-con-tractor') 🚜 @break
                            @case('nivelado-de-terreno') 📏 @break
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

    {{-- Publicaciones por categoría: mismo patrón que serviciodejardineria.com.ar hoy
         en producción (web/blog.blade.php) -- un bloque por categoría con posts reales,
         cada categoría linkeando a /categoria/{slug} y cada post a /publicaciones/{slug}.
         A diferencia del original (3 categorías hardcodeadas), acá se recorren todas
         las categorías activas que tengan al menos un post publicado. --}}
    @foreach($categoryPosts as $group)
    <section class="py-16 bg-white rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-xl mx-auto mb-12">
                <p class="text-green-700 font-semibold mb-2">Publicaciones</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">{{ $group['heading'] }}</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($group['posts'] as $post)
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
                        <a href="{{ route('category.show', $group['category']) }}" class="text-sm text-green-600 font-semibold hover:text-green-700">
                            {{ $group['category']->name }}
                        </a>
                        <h3 class="font-bold text-xl mt-1 mb-4">
                            <a href="{{ route('post.show', $post) }}" class="hover:text-green-700">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <a href="{{ route('post.show', $post) }}"
                           class="text-green-700 hover:text-green-800 font-medium"
                           aria-label="Ver más detalles de {{ $post->title }}">
                            Ver más <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endforeach

    @if($categoryPosts->isNotEmpty())
    <div class="text-center mb-8">
        <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 bg-green-700 text-white px-8 py-4 rounded-lg font-bold hover:bg-green-800 transition">
            Ver Todas <i class="fas fa-search"></i>
        </a>
    </div>
    @endif

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
            <h2 class="text-3xl font-bold mb-4">¿Necesitás mantener tu jardín impecable?</h2>
            <p class="text-xl mb-8 text-green-200">Servicio profesional en Zona Norte de Buenos Aires y CABA. Respuesta rápida, presupuesto sin cargo.</p>
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
            "name" => "AltoParque",
            "description" => "Servicio profesional en Zona Norte de Buenos Aires y CABA. Respuesta rápida, presupuesto sin cargo.",
            "image" => asset('images/og-default.jpg'),
            "telephone" => "+54 11 7178-9529",
            "email" => "info@serviciodejardineria.com.ar",
            "sameAs" => ["https://www.facebook.com/cortamospastoyjardines"],
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
            "name" => "AltoParque",
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