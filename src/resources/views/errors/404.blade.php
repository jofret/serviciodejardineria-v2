@extends('layouts.app')

@section('meta_title', 'Página no encontrada - Limpieza de Terrenos')
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | La página que buscas no existe. Volvé al inicio.')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center">
        <div class="text-9xl font-bold text-green-700 mb-4">404</div>
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Página no encontrada</h1>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl">
            La página que estás buscando no existe o ha sido movida.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" 
               class="bg-green-700 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-800 transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-home mr-2"></i>
                Volver al inicio
            </a>
            <a href="{{ route('home') }}#contacto-formulario"
               class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-300 transition transform hover:scale-105">
                <i class="fas fa-envelope mr-2"></i>
                Contactar
            </a>
        </div>
    </div>
</div>
@endsection