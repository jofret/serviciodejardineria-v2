@extends('layouts.app')

@section('meta_title', 'Contacto - Limpieza y Desmalezado de Terrenos')
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | Contactanos para solicitar presupuesto sin cargo para limpieza de terrenos, desmalezado y roza en zona norte. Respuesta rápida garantizada. | Tags: contacto, presupuesto, desmalezado, limpieza, terrenos, whatsapp, zona norte')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-green-700 text-white p-6 text-center">
            <h1 class="text-3xl font-bold">Contacto</h1>
            <p class="text-green-100 mt-2">Solicitá tu presupuesto sin cargo</p>
        </div>

        <div class="p-8">
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('contacto.enviar') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-lg px-4 py-2 @error('name') border-red-500 @enderror" placeholder="Tu nombre" required>
                        @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Teléfono *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full border rounded-lg px-4 py-2 @error('phone') border-red-500 @enderror" placeholder="11 7178-9529" required>
                        @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg px-4 py-2 @error('email') border-red-500 @enderror" placeholder="tu@email.com" required>
                        @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Ubicación del terreno *</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Ej: Pilar, Escobar, Tigre..." class="w-full border rounded-lg px-4 py-2 @error('location') border-red-500 @enderror" required>
                        @error('location')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Tamaño aproximado</label>
                    <select name="size" class="w-full border rounded-lg px-4 py-2">
                        <option value="">Seleccioná una opción</option>
                        <option value="menos de 500m²">Menos de 500m²</option>
                        <option value="500 a 1000m²">500 a 1000m²</option>
                        <option value="1000 a 5000m²">1000 a 5000m²</option>
                        <option value="5000 a 10000m²">5000 a 10000m²</option>
                        <option value="más de 1 hectárea">Más de 1 hectárea</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Mensaje *</label>
                    <textarea name="message" rows="5" class="w-full border rounded-lg px-4 py-2 @error('message') border-red-500 @enderror" placeholder="Contanos qué necesitas..." required>{{ old('message') }}</textarea>
                    @error('message')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6 text-center text-gray-600 bg-green-50 p-4 rounded-lg">
                    <p class="mb-2">⚡ ¿Preferís hablar ya con nosotros?</p>
                    <a href="https://wa.me/5491171789529?text=Hola!%20Necesito%20información%20sobre%20limpieza%20de%20terrenos" target="_blank" class="inline-flex items-center bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        <i class="fab fa-whatsapp text-xl mr-2"></i> Contactar por WhatsApp
                    </a>
                </div>

                <button type="submit" class="w-full bg-green-700 text-white font-bold py-3 px-6 rounded-lg hover:bg-green-800 transition transform hover:scale-105 shadow-lg">
                    Enviar mensaje <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <h2 class="font-bold text-lg mb-4">También podés contactarnos directamente:</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center"><span class="text-2xl mr-3">📞</span><div><span class="font-semibold">Teléfono:</span> <a href="tel:+541171789529" class="text-green-700 hover:underline">11 7178-9529</a></div></div>
                    <div class="flex items-center"><span class="text-2xl mr-3">📧</span><div><span class="font-semibold">Email:</span> <a href="mailto:info@limpieza-terrenos.com.ar" class="text-green-700 hover:underline">info@limpieza-terrenos.com.ar</a></div></div>
                    <div class="flex items-center"><span class="text-2xl mr-3">📍</span><div><span class="font-semibold">Zonas:</span> Pilar, Escobar, Tigre, Zona Norte</div></div>
                    <div class="flex items-center"><span class="text-2xl mr-3">⏰</span><div><span class="font-semibold">Horario:</span> Lun a Sáb 8:00 - 18:00</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection