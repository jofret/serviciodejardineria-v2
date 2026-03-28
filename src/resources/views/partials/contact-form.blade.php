<div id="contacto-formulario" class="relative w-full py-16 my-8 overflow-hidden">
    {{-- Imagen de fondo responsive con WebP (efecto parallax) --}}
    <div class="absolute inset-0 z-0">
        <picture>
            {{-- Para pantallas grandes (>= 1024px) --}}
            <source srcset="{{ asset('images/banner.webp') }}" media="(min-width: 1024px)" type="image/webp">
            {{-- Para pantallas pequeñas (< 1024px) --}}
            <source srcset="{{ asset('images/banner-768w.webp') }}" media="(max-width: 1023px)" type="image/webp">
            {{-- Fallback (si el navegador no soporta WebP) --}}
            <img src="{{ asset('images/banner-768w.webp') }}" alt="Fondo" class="w-full h-full object-cover fixed top-0 left-0" style="position: fixed;">
        </picture>
        {{-- Overlay negro para legibilidad del texto --}}
        <div class="absolute inset-0 bg-black bg-opacity-70"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
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