<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Relevador') - AltoParque</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind-generated.css') }}">
</head>
<body class="bg-gray-50 min-h-screen">
    @auth
    <header class="bg-green-700 text-white sticky top-0 z-10 shadow">
        <div class="px-4 py-3 flex items-center justify-between">
            <div>
                <span class="block text-xs text-green-100">Panel de relevador</span>
                <span class="block font-semibold">{{ auth()->user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('relevador.logout') }}">
                @csrf
                <button type="submit" class="text-sm bg-green-800 hover:bg-green-900 px-3 py-2 rounded-lg">
                    Salir
                </button>
            </form>
        </div>
    </header>
    @endauth

    <main class="px-4 py-4 max-w-lg mx-auto">
        @yield('content')
    </main>
</body>
</html>
