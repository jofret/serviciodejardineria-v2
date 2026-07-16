@extends('layouts.app')

@section('meta_title', 'Encuesta de satisfacción - AltoParque')
@section('meta_description', 'Ayudanos a mejorar contándonos tu experiencia con nuestro servicio.')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-green-700 text-white p-6 text-center">
            <h1 class="text-3xl font-bold">¡Gracias por confiar en nosotros!</h1>
            <p class="text-green-100 mt-2">Ayudanos a mejorar con tu opinión</p>
        </div>

        <div class="p-8">
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
            @endif

            {{-- Datos del cliente precargados --}}
            <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-lg">
                        {{ substr($survey->customer->name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-semibold">{{ $survey->customer->name }}</p>
                        <p class="text-sm text-gray-600">{{ $survey->customer->phone }}</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600">📅 Encuesta enviada el {{ $survey->sent_at->format('d/m/Y') }}</p>
            </div>

            <form method="POST" action="{{ url('/encuesta/' . $survey->token) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Género --}}
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Género *</label>
                        <div class="space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="masculino" class="form-radio text-green-600" {{ old('gender') == 'masculino' ? 'checked' : '' }}>
                                <span class="ml-2">Masculino</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="femenino" class="form-radio text-green-600" {{ old('gender') == 'femenino' ? 'checked' : '' }}>
                                <span class="ml-2">Femenino</span>
                            </label>
                        </div>
                        @error('gender')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Ocupación --}}
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Ocupación</label>
                        <input type="text" name="occupation" value="{{ old('occupation') }}" 
                               class="w-full border rounded-lg px-4 py-2 @error('occupation') border-red-500 @enderror" 
                               placeholder="Ej: Comerciante, Docente, Jubilado...">
                        @error('occupation')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Fecha de cumpleaños --}}
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Fecha de cumpleaños</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <select name="birthday_day" class="w-full border rounded-lg px-4 py-2 @error('birthday_day') border-red-500 @enderror">
                                <option value="">Día</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ old('birthday_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <select name="birthday_month" class="w-full border rounded-lg px-4 py-2 @error('birthday_month') border-red-500 @enderror">
                                <option value="">Mes</option>
                                <option value="Enero" {{ old('birthday_month') == 'Enero' ? 'selected' : '' }}>Enero</option>
                                <option value="Febrero" {{ old('birthday_month') == 'Febrero' ? 'selected' : '' }}>Febrero</option>
                                <option value="Marzo" {{ old('birthday_month') == 'Marzo' ? 'selected' : '' }}>Marzo</option>
                                <option value="Abril" {{ old('birthday_month') == 'Abril' ? 'selected' : '' }}>Abril</option>
                                <option value="Mayo" {{ old('birthday_month') == 'Mayo' ? 'selected' : '' }}>Mayo</option>
                                <option value="Junio" {{ old('birthday_month') == 'Junio' ? 'selected' : '' }}>Junio</option>
                                <option value="Julio" {{ old('birthday_month') == 'Julio' ? 'selected' : '' }}>Julio</option>
                                <option value="Agosto" {{ old('birthday_month') == 'Agosto' ? 'selected' : '' }}>Agosto</option>
                                <option value="Septiembre" {{ old('birthday_month') == 'Septiembre' ? 'selected' : '' }}>Septiembre</option>
                                <option value="Octubre" {{ old('birthday_month') == 'Octubre' ? 'selected' : '' }}>Octubre</option>
                                <option value="Noviembre" {{ old('birthday_month') == 'Noviembre' ? 'selected' : '' }}>Noviembre</option>
                                <option value="Diciembre" {{ old('birthday_month') == 'Diciembre' ? 'selected' : '' }}>Diciembre</option>
                            </select>
                        </div>
                    </div>
                    @error('birthday_day')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    @error('birthday_month')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Comentario --}}
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">¿Cómo fue tu experiencia? *</label>
                    <textarea name="comment" rows="5" class="w-full border rounded-lg px-4 py-2 @error('comment') border-red-500 @enderror" 
                              placeholder="Contanos qué te pareció nuestro servicio...">{{ old('comment') }}</textarea>
                    @error('comment')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Botón --}}
                <div class="text-center">
                    <button type="submit" class="bg-green-700 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-800 transition transform hover:scale-105 shadow-lg">
                        Enviar mi opinión
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Tu opinión nos ayuda a mejorar. ¡Gracias por tu tiempo!</p>
            </div>
        </div>
    </div>
</div>
@endsection