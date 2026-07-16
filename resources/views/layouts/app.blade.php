<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- TÍTULO --}}
    <title>@yield('meta_title', 'Servicio de Jardinería en Zona Norte y CABA')</title>

    {{-- META DESCRIPTION --}}
    <meta name="description" content="@yield('meta_description', 'Corte de pasto, poda de altura, desmalezado y mantenimiento de jardines ✅ +54 11 7178-9529 | Servicio profesional en zona norte, CABA y Gran Buenos Aires. Presupuesto sin cargo.')">

    {{-- KEYWORDS --}}
    <meta name="keywords" content="@yield('meta_keywords', 'servicio de jardinería, corte de pasto, poda de altura, desmalezado de terrenos, mantenimiento de jardines, zona norte, caba, buenos aires')">

    {{-- AUTHOR Y ROBOTS --}}
    <meta name="author" content="Servicio de Jardinería">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <meta name="geo.region" content="AR-B">
    <meta name="geo.placename" content="Buenos Aires">

    {{-- CANONICAL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- OPEN GRAPH --}}
    <meta property="og:title" content="@yield('meta_title', 'Servicio de Jardinería en Zona Norte y CABA')">
    <meta property="og:description" content="@yield('meta_description', 'Corte de pasto, poda de altura, desmalezado y mantenimiento de jardines ✅ +54 11 7178-9529')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">

    {{-- TWITTER --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', 'Servicio de Jardinería en Zona Norte y CABA')">
    <meta name="twitter:description" content="@yield('meta_description', 'Corte de pasto, poda de altura, desmalezado y mantenimiento de jardines ✅ +54 11 7178-9529')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    {{-- 
        CSS Y FUENTES OPTIMIZADAS 
        - Tailwind CSS se carga con defer (no bloquea renderizado)
        - Font Awesome y Alpine también con defer
        - jQuery y Lightbox2 son necesarios para galería
    --}}
    
    <link rel="stylesheet" href="{{ asset('css/tailwind-generated.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- jQuery (necesario para Lightbox2) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Lightbox2 (mesa de luz) --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    {{-- Google Fonts (optimizada para no bloquear renderizado) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    </noscript>

    <style>
        html {
            scroll-behavior: smooth;
        }
        .whatsapp-float { 
            position: fixed; 
            bottom: 30px; 
            right: 30px; 
            z-index: 100; 
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        }
        .line-clamp-4 {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    {{-- Google reCAPTCHA (cargado con defer para no bloquear) --}}
    @if(config('services.recaptcha.site_key'))
    <script defer src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        // Esperar a que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contact-form');
            
            if (form && typeof grecaptcha !== 'undefined') {
                // Generar token al cargar la página
                grecaptcha.ready(function() {
                    console.log('✅ reCAPTCHA listo');
                });
                
                // Al enviar el formulario
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'submit'}).then(function(token) {
                            // Agregar token al formulario
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'honey_recaptcha_token';
                            input.value = token;
                            form.appendChild(input);
                            
                            // Enviar formulario
                            form.submit();
                        });
                    });
                });
            }
        });
    </script>
    @endif

</head>
<body class="bg-gray-50" x-data="{ mobileMenuOpen: false }">

    {{-- Header --}}
    <header class="bg-white shadow-md sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                {{-- Logo --}}
                <a href="/" class="flex items-center space-x-2">
                    <span class="text-3xl">🌿</span>
                    <div>
                        <span class="text-xl font-bold text-green-800">SERVICIO DE JARDINERÍA</span>
                        <span class="block text-xs text-gray-600">Corte de pasto, poda y mantenimiento de jardines</span>
                    </div>
                </a>

                {{-- Menú desktop con anclas --}}
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#inicio" class="text-gray-700 hover:text-green-700 font-medium transition">Inicio</a>
                    <a href="#servicios" class="text-gray-700 hover:text-green-700 font-medium transition">Servicios</a>
                    <a href="#trabajos" class="text-gray-700 hover:text-green-700 font-medium transition">Trabajos</a>
                    <a href="#contacto-formulario" class="text-gray-700 hover:text-green-700 font-medium transition">Contacto</a>
                    <a href="https://wa.me/5491171789529?text=Hola%21%20Necesito%20informaci%C3%B3n%20sobre%20los%20servicios%20de%20jardiner%C3%ADa"
                       target="_blank"
                       class="flex items-center text-green-700 font-bold hover:text-green-800 transition">
                        <i class="fab fa-whatsapp mr-2"></i> 11 7178-9529
                    </a>
                    <a href="#contacto-formulario" class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800 transition">
                        Presupuesto
                    </a>
                </div>

                {{-- Botón menú móvil --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-2xl">
                    <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
                </button>
            </div>

            {{-- Menú móvil con anclas --}}
            <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden mt-4 pb-4 space-y-2">
                <a href="#inicio" class="block py-2 px-4 hover:bg-green-50">Inicio</a>
                <a href="#servicios" class="block py-2 px-4 hover:bg-green-50">Servicios</a>
                <a href="#trabajos" class="block py-2 px-4 hover:bg-green-50">Trabajos</a>
                <a href="#contacto" class="block py-2 px-4 hover:bg-green-50">Contacto</a>
                <a href="tel:+541171789529" class="block py-2 px-4 text-green-700 font-bold">
                    <i class="fas fa-phone-alt mr-2"></i> 11 7178-9529
                </a>
                <a href="#contacto-formulario" class="block py-2 px-4 bg-green-700 text-white rounded">Presupuesto</a>
            </div>
        </nav>
    </header>

    {{-- WhatsApp flotante --}}
    <a href="https://wa.me/5491171789529?text=Hola!%20Necesito%20información%20sobre%20servicios%20de%20jardinería"
       target="_blank"
       class="whatsapp-float bg-green-600 text-white p-4 rounded-full shadow-lg hover:bg-green-700 transition transform hover:scale-110">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>

    {{-- Contenido principal --}}
    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                {{-- Columna 1: Info de la empresa --}}
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <span class="text-2xl mr-2">🌿</span>
                        Servicio de Jardinería
                    </h3>
                    <p class="text-gray-400">Corte de pasto, poda de altura, desmalezado y mantenimiento de jardines en CABA - Gran Buenos Aires - Zona Norte.</p>
                </div>

                {{-- Columna 2: Enlaces rápidos (anclas) --}}
                <div>
                    <h4 class="font-semibold mb-4">Enlaces rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="#inicio" class="text-gray-400 hover:text-white transition">Inicio</a></li>
                        <li><a href="#servicios" class="text-gray-400 hover:text-white transition">Servicios</a></li>
                        <li><a href="#trabajos" class="text-gray-400 hover:text-white transition">Trabajos</a></li>
                        <li><a href="#contacto-formulario" class="text-gray-400 hover:text-white transition">Contacto</a></li>
                    </ul>
                </div>

                {{-- Columna 3: Servicios dinámicos desde la BD --}}
                <div>
                    <h4 class="font-semibold mb-4">Servicios</h4>
                    <ul class="space-y-2">
                        @php
                            $servicios = App\Models\Category::where('is_active', true)
                                ->orderBy('order')
                                ->get();
                        @endphp
                        
                        @foreach($servicios as $servicio)
                            <li>
                                <a href="{{ route('category.show', $servicio) }}" class="text-gray-400 hover:text-white transition">
                                    {{ $servicio->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Columna 4: Contacto --}}
                <div>
                    <h4 class="font-semibold mb-4">Contacto</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-phone text-green-500 w-5 mr-2"></i>
                            <span class="text-gray-400">11 7178-9529</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-whatsapp text-green-500 w-5 mr-2"></i>
                            <span class="text-gray-400">11 7178-9529</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-green-500 w-5 mr-2"></i>
                            <span class="text-gray-400">info@serviciodejardineria.com.ar</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt text-green-500 w-5 mr-2"></i>
                            <span class="text-gray-400">Zona Norte, Buenos Aires</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-facebook text-green-500 w-5 mr-2"></i>
                            <a href="https://www.facebook.com/cortamospastoyjardines" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition">Facebook</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
                &copy; {{ date('Y') }} Servicio de Jardinería. Todos los derechos reservados.
                <br>
                <span class="text-sm">Desarrollado por JOfret</span>
            </div>
        </div>
    </footer>

    {{-- Datos estructurados JSON-LD --}}
    @stack('schema')
</body>
</html>