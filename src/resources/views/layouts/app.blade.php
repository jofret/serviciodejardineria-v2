<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- TÍTULO --}}
    <title>@yield('meta_title', 'Limpieza y Desmalezado de Terrenos en Zona Norte')</title>

    {{-- META DESCRIPTION --}}
    <meta name="description" content="@yield('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | Servicio profesional en zona norte y Gran Buenos Aires. Respuesta rápida, presupuesto sin cargo. | Tags: desmalezado, limpieza, roza, pilar, escobar, campos')">

    {{-- KEYWORDS --}}
    <meta name="keywords" content="limpieza de terrenos, desmalezado, roza de campos, zona norte, desmalezado pilar, limpieza escobar">

    {{-- AUTHOR Y ROBOTS --}}
    <meta name="author" content="Limpieza de Terrenos">
    <meta name="robots" content="index, follow">
    <meta name="geo.region" content="AR-B">
    <meta name="geo.placename" content="Buenos Aires">

    {{-- CANONICAL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- OPEN GRAPH --}}
    <meta property="og:title" content="@yield('meta_title', 'Limpieza y Desmalezado de Terrenos')">
    <meta property="og:description" content="@yield('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">

    {{-- TWITTER --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', 'Limpieza y Desmalezado de Terrenos')">
    <meta name="twitter:description" content="@yield('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    {{-- CSS Y FUENTES --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
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
</head>
<body class="bg-gray-50" x-data="{ mobileMenuOpen: false }">

    {{-- Header --}}
    <header class="bg-white shadow-md sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <a href="/" class="flex items-center space-x-2">
                    <span class="text-3xl">🌿</span>
                    <div>
                        <span class="text-xl font-bold text-green-800">Limpieza de Terrenos</span>
                        <span class="block text-xs text-gray-600">Profesionales en desmalezado</span>
                    </div>
                </a>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="tel:+541171789529" class="flex items-center text-green-700 font-bold">
                        <i class="fas fa-phone-alt mr-2"></i> 11 7178-9529
                    </a>
                    <a href="/contacto" class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">Contáctenos</a>
                </div>

                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-2xl">
                    <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
                </button>
            </div>

            <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden mt-4 pb-4 space-y-2">
                <a href="/" class="block py-2 px-4 hover:bg-green-50">Inicio</a>
                <a href="/servicios" class="block py-2 px-4 hover:bg-green-50">Servicios</a>
                <a href="/posts" class="block py-2 px-4 hover:bg-green-50">Trabajos</a>
                <a href="/contacto" class="block py-2 px-4 hover:bg-green-50">Contacto</a>
                <a href="/presupuesto" class="block py-2 px-4 bg-green-700 text-white rounded">Presupuesto</a>
            </div>
        </nav>
    </header>

    {{-- WhatsApp flotante --}}
    <a href="https://wa.me/5491171789529?text=Hola!%20Necesito%20información%20sobre%20limpieza%20de%20terrenos"
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
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <span class="text-2xl mr-2">🌿</span>
                        Limpieza de Terrenos
                    </h3>
                    <p class="text-gray-400">Profesionales en limpieza, desmalezado y mantenimiento de terrenos en zona norte y Gran Buenos Aires.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Enlaces rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-400 hover:text-white">Inicio</a></li>
                        <li><a href="/servicios" class="text-gray-400 hover:text-white">Servicios</a></li>
                        <li><a href="/posts" class="text-gray-400 hover:text-white">Trabajos</a></li>
                        <li><a href="/contacto" class="text-gray-400 hover:text-white">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Servicios</h4>
                    <ul class="space-y-2">
                        <li><a href="/desmalezado" class="text-gray-400 hover:text-white">Desmalezado</a></li>
                        <li><a href="/limpieza" class="text-gray-400 hover:text-white">Limpieza</a></li>
                        <li><a href="/roza" class="text-gray-400 hover:text-white">Roza</a></li>
                        <li><a href="/prevencion" class="text-gray-400 hover:text-white">Prevención</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contacto</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center"><i class="fas fa-phone text-green-500 w-5 mr-2"></i>11 7178-9529</li>
                        <li class="flex items-center"><i class="fab fa-whatsapp text-green-500 w-5 mr-2"></i>11 7178-9529</li>
                        <li class="flex items-center"><i class="fas fa-envelope text-green-500 w-5 mr-2"></i>info@limpieza-terrenos.com.ar</li>
                        <li class="flex items-center"><i class="fas fa-map-marker-alt text-green-500 w-5 mr-2"></i>Zona Norte, Buenos Aires</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
                &copy; {{ date('Y') }} Limpieza de Terrenos. Todos los derechos reservados.
                <br>
                <span class="text-sm">Desarrollado por JOfret</span>
            </div>
        </div>
    </footer>
    {{-- Datos estructurados JSON-LD --}}
    @stack('schema')
</body>
</html>