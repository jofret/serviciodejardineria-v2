{{-- Testimonios --}}
    <section id="testimonios" class="py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">¿Qué dicen nuestros clientes?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">La plena satisfacción de nuestros clientes es nuestro principal objetivo.</p>
            </div>

            <div x-data="{
                testimonios: [
                    { id:1, nombre:'Norma Gloria Damianoff', ubicacion:'Saavedra - CABA', tamaño:'5000m²', texto:'Excelente trabajo como siempre, prolijos, rápidos, muy amables.', imagen:'https://poda-de-altura.com.ar/image/BqkdMNRW854dyhRwmuladtLzaDESKxfdZ9kzQf8V.jpg?w=600', tipo:'Antes', rating:5 },
                    { id:2, nombre:'Iris', ubicacion:'Escobar', tamaño:'500m²', texto:'Brindaron el trabajo que quería. Con seguro y cláusula de No repetición de no repetición. Equipo de personas conocedoras de su tarea. Eficiencia y rapidez. Muy recomendables.', imagen:'https://poda-de-altura.com.ar/image/AwktPi3ghE0YoEOvNKd9ZMCZrk0MstwbuYUdm71m.jpg?w=600', tipo:'Después', rating:5 },
                    { id:3, nombre:'Guillermo', ubicacion:'Del Viso', tamaño:'2000 m2', texto:'Muy conforme con el servicio, quedó Muy bien la poda del roble. Muy buena la atención de Jofre. Muy recomendable.', imagen:'https://poda-de-altura.com.ar/image/5f0XMFcZcHyYafxZykFqcmM5PMeZ0TOhVJbEwGoA.jpg?w=600', tipo:'Resultado', rating:5 }
                ],
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
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden h-full hover:shadow-xl transition card-hover">
                                    <div class="relative h-48 overflow-hidden">
                                        <img :src="testimonio.imagen" :alt="'Terreno ' + testimonio.tipo" class="w-full h-full object-cover hover:scale-110 transition duration-500">
                                        <div class="absolute top-2 left-2 bg-green-600 text-white px-2 py-1 rounded-full text-xs font-bold" x-text="testimonio.tipo"></div>
                                    </div>
                                    <div class="p-6">
                                        <div class="text-yellow-400 flex mb-3">
                                            <template x-for="i in testimonio.rating"><i class="fas fa-star"></i></template>
                                        </div>
                                        <p class="text-gray-600 text-sm mb-4 italic line-clamp-4" x-text="testimonio.texto"></p>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-lg" x-text="testimonio.nombre.charAt(0)"></div>
                                            <div class="ml-3">
                                                <h4 class="font-bold text-gray-800" x-text="testimonio.nombre"></h4>
                                                <p class="text-xs text-gray-500"><span x-text="testimonio.ubicacion"></span> · <span x-text="testimonio.tamaño"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-green-700 hover:bg-green-700 hover:text-white transition z-10"><i class="fas fa-chevron-left text-xl"></i></button>
                <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-green-700 hover:bg-green-700 hover:text-white transition z-10"><i class="fas fa-chevron-right text-xl"></i></button>
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="(slide, index) in Array.from({ length: totalSlides })" :key="index">
                        <button @click="currentIndex = index" class="w-3 h-3 rounded-full transition-all duration-300" :class="currentIndex === index ? 'bg-green-700 w-6' : 'bg-gray-300 hover:bg-green-500'"></button>
                    </template>
                </div>
            </div>
        </div>
    </section>
