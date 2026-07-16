@php $testimonials = ($testimonials ?? collect())->filter(fn ($t) => filled($t->comment) && $t->customer); @endphp
@if ($testimonials->isNotEmpty())
{{-- Testimonios: dos columnas alineadas a la izquierda (título+bajada / testimonio
     único con navegación) -- mismo layout que serviciodejardineria.com.ar hoy en
     producción, con la paleta y tipografía del sitio nuevo. --}}
    <section id="testimonios" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-start">
                <div class="lg:col-span-5">
                    <p class="text-green-700 font-semibold mb-2">Testimonios</p>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-5">¿Qué dicen nuestros clientes?</h2>
                    <p class="text-lg text-gray-600">La plena satisfacción de nuestros clientes es nuestro principal objetivo.</p>
                </div>

                <div class="lg:col-span-7"
                    x-data="{
                        testimonios: @js($testimonials->map(fn ($t) => [
                            'id' => $t->id,
                            'nombre' => $t->customer->name,
                            'avatar' => $t->gender === 'masculino' ? asset('images/avatar-man.jpg') : asset('images/avatar-woman.jpg'),
                            'profesion' => $t->occupation,
                            'texto' => $t->comment,
                            'postTitulo' => $t->post && $t->post->is_published && $t->post->category ? $t->post->title : null,
                            'postUrl' => $t->post && $t->post->is_published && $t->post->category
                                ? route('post.show', $t->post)
                                : null,
                        ])->values()),
                        currentIndex: 0,
                        autoplayInterval: null,
                        next() { this.currentIndex = (this.currentIndex + 1) % this.testimonios.length; },
                        prev() { this.currentIndex = (this.currentIndex - 1 + this.testimonios.length) % this.testimonios.length; },
                        startAutoplay() { this.autoplayInterval = setInterval(() => this.next(), 6000); },
                        stopAutoplay() { clearInterval(this.autoplayInterval); },
                        init() { if (this.testimonios.length > 1) this.startAutoplay(); }
                    }"
                    x-init="init()"
                    @mouseenter="stopAutoplay()"
                    @mouseleave="startAutoplay()">
                    <div class="flex items-start gap-3 md:gap-6">
                        <template x-if="testimonios.length > 1">
                            <button @click="prev()" aria-label="Testimonio anterior"
                                class="shrink-0 mt-1 w-10 h-10 rounded-full flex items-center justify-center text-green-700 hover:bg-green-50 transition">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                        </template>

                        <div class="relative overflow-hidden flex-1">
                            <div class="flex transition-transform duration-500 ease-in-out"
                                 :style="'transform: translateX(-' + (currentIndex * 100) + '%)'">
                                <template x-for="testimonio in testimonios" :key="testimonio.id">
                                    <div class="w-full flex-shrink-0">
                                        <img :src="testimonio.avatar" :alt="testimonio.nombre"
                                             class="w-16 h-16 rounded-full object-cover mb-4">
                                        <p class="text-gray-700 text-base md:text-lg italic mb-3" x-text="testimonio.texto"></p>
                                        <h4 class="font-bold text-gray-800" x-text="testimonio.nombre"></h4>
                                        <template x-if="testimonio.profesion">
                                            <p class="text-sm text-green-700" x-text="testimonio.profesion"></p>
                                        </template>
                                        <template x-if="testimonio.postTitulo">
                                            <a :href="testimonio.postUrl" class="text-xs font-semibold text-green-700 hover:text-green-800 mt-2 inline-block">
                                                Ver el trabajo: <span x-text="testimonio.postTitulo"></span> →
                                            </a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <template x-if="testimonios.length > 1">
                            <button @click="next()" aria-label="Siguiente testimonio"
                                class="shrink-0 mt-1 w-10 h-10 rounded-full flex items-center justify-center text-green-700 hover:bg-green-50 transition">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </template>
                    </div>

                    <template x-if="testimonios.length > 1">
                        <div class="mt-4 text-sm text-gray-500" x-text="(currentIndex + 1) + ' / ' + testimonios.length"></div>
                    </template>
                </div>
            </div>
        </div>
    </section>
@endif
