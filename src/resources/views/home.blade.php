@extends('layouts.app')

@section('meta_title', 'Limpieza y Desmalezado de Terrenos en Zona Norte')
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | Servicio profesional en zona norte y Gran Buenos Aires. Respuesta rápida, presupuesto sin cargo. | Tags: desmalezado, limpieza, roza, pilar, escobar, campos, terrenos, maquinaria')

@section('content')
    {{-- BANNER PRINCIPAL --}}
    <section id="inicio" class="relative text-white flex items-center overflow-hidden" style="min-height: calc(100vh - 80px);">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1589923188900-85dae523342b?w=1200" 
                 alt="Terreno con maleza" 
                 class="w-full h-full object-cover">
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
                    <a href="/posts" 
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

    {{-- Trabajos destacados --}}
    @if($featuredPosts->count() > 0)
    <section id="trabajos" class="py-16 bg-white rounded-xl shadow-sm mb-8">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Trabajos Destacados</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($featuredPosts as $post)
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover border border-gray-100">
                    <a href="/{{ $post->category->slug }}/{{ $post->slug }}">
                        @if($post->getFirstMediaUrl('featured', 'thumb'))
                        <img src="{{ $post->getFirstMediaUrl('featured', 'thumb') }}" 
                             alt="{{ $post->title }}"
                             class="w-full h-56 object-cover hover:opacity-90 transition">
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
                            <a href="/{{ $post->category->slug }}/{{ $post->slug }}" 
                               class="hover:text-green-700">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500"><i class="far fa-calendar mr-1"></i>{{ $post->formatted_date }}</span>
                            <a href="/{{ $post->category->slug }}/{{ $post->slug }}" 
                               class="text-green-700 hover:text-green-800 font-medium">
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
                <a href="/{{ $category->slug }}" 
                   class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition card-hover text-center">
                    <div class="text-5xl mb-4">
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
                    <h3 class="font-bold text-lg mb-2">{{ $category->name }}</h3>
                    <p class="text-gray-600 text-sm">{{ Str::limit($category->description, 60) }}</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonios --}}
    <section id="testimonios" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">¿Qué dicen nuestros clientes?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">La plena satisfacción de nuestros clientes es nuestro principal objetivo.</p>
            </div>

            <div x-data="{
                testimonios: [
                    { id:1, nombre:'Carlos Rodríguez', ubicacion:'Pilar', tamaño:'5000m²', texto:'Excelente servicio! Tenía el terreno abandonado hace años y lo dejaron impecable.', imagen:'https://images.unsplash.com/photo-1589923188900-85dae523342b?w=600', tipo:'Antes', rating:5 },
                    { id:2, nombre:'Martina González', ubicacion:'Escobar', tamaño:'2000m²', texto:'Rápidos y eficientes. Me salvaron de una multa municipal.', imagen:'https://images.unsplash.com/photo-1589923188650-aa8f6d441a9b?w=600', tipo:'Después', rating:5 },
                    { id:3, nombre:'Juan Pérez', ubicacion:'Campana', tamaño:'5 hectáreas', texto:'Contraté el servicio para un campo de 5 hectáreas. Dejaron todo impecable.', imagen:'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600', tipo:'Resultado', rating:5 }
                ],
                currentIndex: 0,
                autoplayInterval: null,
                getItemsPerSlide() { return window.innerWidth >= 1024 ? 3 : window.innerWidth >= 768 ? 2 : 1; },
                get totalSlides() { return Math.ceil(this.testimonios.length / this.getItemsPerSlide()); },
                next() { this.currentIndex = (this.currentIndex + 1) % this.totalSlides; },
                prev() { this.currentIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides; },
                startAutoplay() { this.autoplayInterval = setInterval(() => this.next(), 5000); },
                stopAutoplay() { clearInterval(this.autoplayInterval); },
                init() { this.startAutoplay(); }
            }"
            x-init="init()"
            @mouseenter="stopAutoplay()"
            @mouseleave="startAutoplay()"
            class="relative max-w-7xl mx-auto">
                <div class="relative overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-in-out"
                         :style="'transform: translateX(-' + (currentIndex * 100 / getItemsPerSlide()) + '%)'">
                        <template x-for="testimonio in testimonios" :key="testimonio.id">
                            <div class="flex-shrink-0 px-3" :style="'width: ' + (100 / getItemsPerSlide()) + '%'">
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden h-full hover:shadow-xl transition card-hover">
                                    <div class="relative h-48 overflow-hidden">
                                        <img :src="testimonio.imagen" :alt="'Terreno ' + testimonio.tipo" class="w-full h-full object-cover hover:scale-110 transition duration-500">
                                        <div class="absolute top-2 left-2 bg-green-600 text-white px-2 py-1 rounded-full text-xs font-bold" x-text="testimonio.tipo"></div>
                                    </div>
                                    <div class="p-6">
                                        <div class="text-yellow-400 flex mb-3">
                                            <template x-for="i in testimonio.rating"><i class="fas fa-star"></i></template>
                                        </div>
                                        <p class="text-gray-600 text-sm mb-4 italic line-clamp-4" x-text="testimonio.texto"></p>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-lg" x-text="testimonio.nombre.charAt(0)"></div>
                                            <div class="ml-3">
                                                <h4 class="font-bold text-gray-800" x-text="testimonio.nombre"></h4>
                                                <p class="text-xs text-gray-500"><span x-text="testimonio.ubicacion"></span> · <span x-text="testimonio.tamaño"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-green-700 hover:bg-green-700 hover:text-white transition z-10"><i class="fas fa-chevron-left text-xl"></i></button>
                <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-green-700 hover:bg-green-700 hover:text-white transition z-10"><i class="fas fa-chevron-right text-xl"></i></button>
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="(slide, index) in Array.from({ length: totalSlides })" :key="index">
                        <button @click="currentIndex = index" class="w-3 h-3 rounded-full transition-all duration-300" :class="currentIndex === index ? 'bg-green-700 w-6' : 'bg-gray-300 hover:bg-green-500'"></button>
                    </template>
                </div>
            </div>
        </div>
    </section>

    {{-- FORMULARIO DE CONTACTO CON PARALLAX --}}
    <div id="contacto-formulario" class="relative w-full py-16 my-8" 
         style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1589923188900-85dae523342b?w=1200') fixed center/cover;">
        
        <div class="container mx-auto px-4">
            <div class="flex justify-center">
                <div class="w-full md:w-2/3 lg:w-1/2">
                    <div class="bg-white rounded-xl shadow-2xl p-6 md:p-8 wow fadeIn" data-wow-delay="0.5s">
                        <h2 class="text-3xl font-bold text-center mb-6 text-gray-800">Contáctenos</h2>
                        
                        @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                        @endif

                        <form id="contact-form" action="{{ route('contacto.enviar') }}" method="POST">
                            @csrf
                            
                            {{-- Zona y localidad --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                {{-- Zona principal --}}
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Zona *</label>
                                    <select id="zona_principal" name="zona_principal" 
                                            class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('zona_principal') border-red-500 @enderror"
                                            required>
                                        <option value="">Seleccione zona...</option>
                                        <option value="CABA">CABA</option>
                                        <option value="Zona Norte">Zona Norte</option>
                                        <option value="Zona Oeste">Zona Oeste</option>
                                        <option value="Otra">Otra zona (especificar)</option>
                                    </select>
                                    @error('zona_principal')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                {{-- Partido / Localidad --}}
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Localidad / Partido *</label>
                                    <select id="partido" name="partido" 
                                            class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('partido') border-red-500 @enderror"
                                            required>
                                        <option value="">Primero seleccione zona</option>
                                    </select>
                                    @error('partido')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Campo para "Otra zona" (oculto por defecto) --}}
                            <div id="otra_zona_container" class="mb-4 hidden">
                                <label class="block text-gray-700 font-medium mb-1">Especificar otra zona/localidad *</label>
                                <input type="text" id="otra_zona" name="otra_zona"
                                       class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('otra_zona') border-red-500 @enderror"
                                       placeholder="Ej: La Plata, Berisso, Mar del Plata, etc.">
                                @error('otra_zona')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Datos personales --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Nombre *</label>
                                    <input type="text" name="name" value="{{ old('name') }}"
                                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                                           placeholder="Tu nombre" required>
                                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Email *</label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('email') border-red-500 @enderror"
                                           placeholder="tu@email.com" required>
                                    @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Teléfono *</label>
                                    <input type="tel" name="phone" value="{{ old('phone') }}"
                                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('phone') border-red-500 @enderror"
                                           placeholder="11 7178-9529" required>
                                    @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Servicio *</label>
                                    <select name="service" class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Seleccione...</option>
                                        <option value="desmalezado">Desmalezado</option>
                                        <option value="limpieza">Limpieza de Terrenos</option>
                                        <option value="roza">Roza</option>
                                        <option value="prevencion">Prevención de Incendios</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Mensaje --}}
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-1">Mensaje *</label>
                                <textarea name="message" rows="4"
                                          class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 @error('message') border-red-500 @enderror"
                                          placeholder="Escribí tu consulta..." required>{{ old('message') }}</textarea>
                                @error('message')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Botón --}}
                            <div class="text-center mt-6">
                                <x-honey recaptcha/>
                                
                                <button type="submit" class="bg-green-700 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-800 transition transform hover:scale-105 shadow-lg inline-flex items-center">
                                    Enviar Ahora <i class="fas fa-paper-plane ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Script para manejar zonas y scroll --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const zonaPrincipal = document.getElementById('zona_principal');
        const partido = document.getElementById('partido');
        const otraZonaContainer = document.getElementById('otra_zona_container');
        const otraZona = document.getElementById('otra_zona');
        const form = document.getElementById('contact-form');

        // Definir las localidades por zona
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

        // Ajustar required antes de enviar
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

        // Si hay un error de validación, restaurar el estado
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

        // SCROLL AL FORMULARIO SI HAY MENSAJES
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
                <a href="/tag/{{ $tag->slug }}" class="bg-white px-4 py-2 rounded-full text-gray-700 hover:bg-green-700 hover:text-white transition shadow-sm">
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
                <a href="#contacto-formulario" class="bg-white text-green-800 px-8 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition">
                    <i class="fas fa-envelope mr-2"></i> Enviar consulta
                </a>
                <a href="https://wa.me/5491171789529" target="_blank" class="bg-green-600 text-white px-8 py-4 rounded-lg text-lg font-bold hover:bg-green-700 transition border-2 border-white">
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
            "image" => asset('images/logo.jpg'),
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
                "target" => url('/posts?search={search_term_string}'),
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

