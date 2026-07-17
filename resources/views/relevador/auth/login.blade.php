@extends('relevador.layout')

@section('title', 'Ingresar')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow p-6">
        <div class="text-center mb-6">
            <span class="text-4xl">🌿</span>
            <h1 class="text-lg font-bold text-green-800 mt-2">Panel de relevador</h1>
            <p class="text-sm text-gray-500">AltoParque</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm p-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('relevador.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600 text-base py-3 px-3">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" name="password" id="password" required
                       class="w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600 text-base py-3 px-3">
            </div>
            <button type="submit"
                    class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-lg text-base">
                Ingresar
            </button>
        </form>
    </div>
</div>
@endsection
