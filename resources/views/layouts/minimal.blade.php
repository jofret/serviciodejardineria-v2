<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('meta_title', 'AltoParque')</title>
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="robots" content="@yield('meta_robots', 'noindex, nofollow')">

    <link rel="stylesheet" href="{{ asset('css/tailwind-generated.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Encabezado mínimo: solo marca, sin menú de navegación del sitio —
         estas páginas se acceden por link privado (presupuesto, conformidad,
         encuesta), no forman parte de la navegación del sitio de marketing. --}}
    <header class="bg-white border-b border-gray-200 py-4">
        <div class="container mx-auto px-4 flex items-center gap-2">
            <span class="text-2xl">🌿</span>
            <span class="text-lg font-bold text-green-800">ALTOPARQUE</span>
        </div>
    </header>

    <main class="py-8 px-4">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-6">
        &copy; {{ date('Y') }} AltoParque
    </footer>
</body>
</html>
