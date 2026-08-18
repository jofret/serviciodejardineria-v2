@extends('layouts.app')

@section('meta_title', 'Enlace no disponible - AltoParque')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center">
        <div class="text-6xl mb-4">🔗</div>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Este enlace ya no está disponible</h1>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            Si necesitás ayuda con tu trabajo o presupuesto, escribinos por WhatsApp y te atendemos directo.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="https://wa.me/5491164640291?text=Hola%21%20Ten%C3%ADa%20un%20enlace%20guardado%20que%20ya%20no%20funciona%2C%20%C2%BFme%20pod%C3%A9s%20ayudar%3F"
               target="_blank"
               class="bg-green-700 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-800 transition transform hover:scale-105 shadow-lg">
                <i class="fab fa-whatsapp mr-2"></i>
                Escribirnos por WhatsApp
            </a>
            <a href="/"
               class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-300 transition transform hover:scale-105">
                <i class="fas fa-home mr-2"></i>
                Volver al inicio
            </a>
        </div>
    </div>
</div>
@endsection
