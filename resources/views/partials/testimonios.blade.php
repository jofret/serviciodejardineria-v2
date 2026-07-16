@php $testimonials = ($testimonials ?? collect())->filter(fn ($t) => filled($t->comment) && $t->customer); @endphp
@if ($testimonials->isNotEmpty())
{{-- Testimonios --}}
    <section id="testimonios" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">¿Qué dicen nuestros clientes?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">La plena satisfacción de nuestros clientes es nuestro principal objetivo.</p>
            </div>

            <div x-data="{
                testimonios: @js($testimonials->map(fn ($t) => [
                    'id' => $t->id,
                    'nombre' => $t->customer->name,
                    'inicial' => Str::upper(Str::substr($t->customer->name, 0, 1)),
                    'ubicacion' => $t->customer->zone ?: $t->customer->zona_principal,
                    'texto' => $t->comment,
                    'postTitulo' => $t->post && $t->post->is_published && $t->post->category ? $t->post->title : null,
                    'postUrl' => $t->post && $t->post->is_published && $t->post->category
                        ? route('post.show', $t->post)
                        : null,
                ])->values()),
                currentIndex: 0,
                autoplayInterval: null,
                getItemsPerSlide() { return window.innerWidth >= 1024 ? 3 : window.innerWidth >= 768 ? 2 : 1; },
                get totalSlides() { return Math.ceil(this.testimonios.length / this.getItemsPerSlide()); },
                next() { this.currentIndex = (this.currentIndex + 1) % this.totalSlides; },
                prev() { this.currentIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides; },
                startAutoplay() { this.autoplayInterval = setInterval(() => this.next(), 5000); },
                stopAutoplay() { clearInterval(this.autoplayInterval); },
                init() { this.startAutoplay(); }
            }"
            x-init="init()"
            @mouseenter="stopAutoplay()"
            @mouseleave="startAutoplay()"
            class="relative max-w-7xl mx-auto">
                <div class="relative overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-in-out"
                         :style="'transform: translateX(-' + (currentIndex * 100 / getItemsPerSlide()) + '%)'">
                        <template x-for="testimonio in testimonios" :key="testimonio.id">
                            <div class="flex-shrink-0 px-3" :style="'width: ' + (100 / getItemsPerSlide()) + '%'">
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden h-full hover:shadow-xl transition card-hover flex flex-col">
                                    <div class="p-6 flex flex-col flex-1">
                                        <div class="text-green-600 text-3xl leading-none mb-2">&ldquo;</div>
                                        <p class="text-gray-600 text-sm mb-4 italic line-clamp-5 flex-1" x-text="testimonio.texto"></p>
                                        <template x-if="testimonio.postTitulo">
                                            <a :href="testimonio.postUrl" class="text-xs font-semibold text-green-700 hover:text-green-800 mb-4 inline-block">
                                                Ver el trabajo: <span x-text="testimonio.postTitulo"></span> →
                                            </a>
                                        </template>
                                        <div class="flex items-center mt-auto">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-lg" x-text="testimonio.inicial"></div>
                                            <div class="ml-3">
                                                <h4 class="font-bold text-gray-800" x-text="testimonio.nombre"></h4>
                                                <template x-if="testimonio.ubicacion">
                                                    <p class="text-xs text-gray-500" x-text="testimonio.ubicacion"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <template x-if="testimonios.length > getItemsPerSlide()">
                    <div>
                        <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-green-700 hover:bg-green-700 hover:text-white transition z-10"><i class="fas fa-chevron-left text-xl"></i></button>
                        <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-green-700 hover:bg-green-700 hover:text-white transition z-10"><i class="fas fa-chevron-right text-xl"></i></button>
                        <div class="flex justify-center mt-8 space-x-2">
                            <template x-for="(slide, index) in Array.from({ length: totalSlides })" :key="index">
                                <button @click="currentIndex = index" class="w-3 h-3 rounded-full transition-all duration-300" :class="currentIndex === index ? 'bg-green-700 w-6' : 'bg-gray-300 hover:bg-green-500'"></button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
@endif
