@extends('layouts.app')

@section('meta_title', $metaTitle ?? $category->name . ' - Servicio de Jardinería')
@section('meta_description', 'Servicio de Jardinería WhatsApp ✅ 11 7178-9529 | ' . ($category->description ?? 'Trabajos de ' . $category->name . ' realizados en zona norte con fotos antes/después.') . ' | ' . $category->name . ', zona norte, CABA')
@section('meta_keywords', collect([strtolower($category->name), 'servicio de jardinería', 'zona norte', 'caba', strtolower($category->name) . ' pilar', strtolower($category->name) . ' san isidro'])->unique()->implode(', '))

{{-- Las páginas siguientes de una categoría duplican contenido ya indexado en la página 1 --}}
@if($posts->currentPage() > 1)
    @section('meta_robots', 'noindex, follow')
@endif

@php
    // Preguntas frecuentes por servicio real (no aplica a categorías de blog como Precios, Legal, Consejos, etc.)
    $serviceFaqs = [
        'desmalezado-de-terrenos' => [
            ['q' => '¿Qué es el desmalezado y cuándo es necesario?', 'a' => 'El desmalezado consiste en cortar y retirar la maleza, pastizales altos y arbustos de un terreno. Es necesario cuando el terreno está descuidado, antes de una construcción, o para cumplir con ordenanzas municipales de prevención de incendios.'],
            ['q' => '¿Cada cuánto tiempo hay que desmalezar un terreno?', 'a' => 'En zona norte y Gran Buenos Aires, con el clima húmedo, se recomienda desmalezar cada 2 o 3 meses en primavera-verano, y cada 4 a 6 meses en otoño-invierno, para evitar multas y mantener el terreno en condiciones.'],
            ['q' => '¿Trabajan con maquinaria propia?', 'a' => 'Sí, contamos con maquinaria propia (desmalezadoras, motoguadañas y equipos pesados según el tamaño del terreno) para resolver el trabajo en el menor tiempo posible.'],
            ['q' => '¿Cuánto cuesta el desmalezado de un terreno?', 'a' => 'El costo depende del tamaño del terreno, el tipo de maleza y el acceso al lugar. Escribinos por WhatsApp al 11 7178-9529 y te pasamos un presupuesto sin cargo.'],
        ],
        'poda-de-altura' => [
            ['q' => '¿Qué es la poda de altura y cuándo se necesita?', 'a' => 'Es la poda de árboles grandes o de difícil acceso, que requiere equipo especializado y trabajo en altura. Se recomienda cuando hay ramas secas, riesgo de caída, o el árbol interfiere con cables o construcciones.'],
            ['q' => '¿Trabajan con árboles muy altos o de difícil acceso?', 'a' => 'Sí, contamos con el equipamiento y la experiencia necesaria para podar árboles de gran altura de forma segura, incluso en espacios reducidos.'],
            ['q' => '¿Retiran las ramas y restos de la poda?', 'a' => 'Sí, el retiro y traslado de las ramas cortadas está incluido en el servicio, dejando el lugar limpio.'],
            ['q' => '¿Cuánto cuesta la poda de altura de un árbol?', 'a' => 'Depende de la altura del árbol, su ubicación y el acceso al lugar. Escribinos por WhatsApp al 11 7178-9529 y te pasamos un presupuesto sin cargo.'],
        ],
    ];
    $categoryFaqs = $serviceFaqs[$category->slug] ?? null;
@endphp

@section('content')
    <section class="bg-green-700 text-white rounded-lg p-8 mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="/" class="text-green-200 hover:text-white">Inicio</a>
            <span class="text-green-300">/</span>
            <span>{{ $category->name }}</span>
        </div>
        <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
        @if($category->description)<p class="text-green-100 max-w-2xl">{{ $category->description }}</p>@endif
    </section>

    @if($posts->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <a href="{{ route('post.show', $post) }}" class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden group">
            @if($post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
            @elseif($post->gallery_images && count($post->gallery_images) > 0)
                <img src="{{ Storage::url($post->gallery_images[0]) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
            @else
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">📸 Sin imagen</div>
            @endif
            
            <div class="p-4">
                <h2 class="font-bold text-lg mb-2 group-hover:text-green-700">{{ $post->title }}</h2>
                @if($post->location)<p class="text-gray-600 text-sm mb-2">📍 {{ $post->location }}</p>@endif
                <p class="text-gray-600 text-sm mb-3">{{ $post->excerpt ?? Str::limit(strip_tags($post->content), 100) }}</p>
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>{{ $post->formatted_date }}</span>
                    @if($post->tags->count() > 0)<span class="bg-gray-100 px-2 py-1 rounded">{{ $post->tags->count() }} etiquetas</span>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
    @else
    <div class="text-center py-12 bg-white rounded-lg"><p class="text-gray-500">No hay trabajos publicados en esta categoría aún.</p></div>
    @endif

    @if($categoryFaqs)
    <section class="mt-12">
        <h2 class="text-2xl font-bold mb-6 text-green-800">Preguntas frecuentes sobre {{ strtolower($category->name) }}</h2>
        <div class="space-y-4">
            @foreach($categoryFaqs as $faq)
            <details class="bg-white rounded-lg shadow p-4">
                <summary class="font-semibold cursor-pointer text-gray-800">{{ $faq['q'] }}</summary>
                <p class="text-gray-600 mt-2">{{ $faq['a'] }}</p>
            </details>
            @endforeach
        </div>
    </section>
    @endif
@endsection


@php
    // Datos estructurados para CollectionPage (lista de posts)
    $collectionSchema = [
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => $category->name,
        "description" => $category->description ?? 'Trabajos de ' . $category->name,
        "url" => url()->current(),
        "mainEntity" => [
            "@type" => "ItemList",
            "itemListElement" => []
        ]
    ];

    // Agregar cada post como elemento de la lista
    foreach ($posts as $index => $post) {
        $collectionSchema["mainEntity"]["itemListElement"][] = [
            "@type" => "ListItem",
            "position" => $index + 1,
            "url" => route('post.show', $post)
        ];
    }

    // Datos estructurados para BreadcrumbList
    $breadcrumbSchema = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type" => "ListItem",
                "position" => 1,
                "name" => "Inicio",
                "item" => url('/')
            ],
            [
                "@type" => "ListItem",
                "position" => 2,
                "name" => $category->name,
                "item" => url()->current()
            ]
        ]
    ];

    // Datos estructurados para Service (solo en categorías que son servicios reales, no temas de blog)
    if ($categoryFaqs) {
        $serviceSchema = [
            "@context" => "https://schema.org",
            "@type" => "Service",
            "serviceType" => $category->name,
            "name" => $category->name . ' - Servicio de Jardinería',
            "description" => $category->description ?? 'Servicio profesional de ' . strtolower($category->name) . ' en zona norte y Gran Buenos Aires.',
            "url" => url()->current(),
            "provider" => [
                "@type" => "LocalBusiness",
                "name" => "Servicio de Jardinería",
                "telephone" => "+54 11 7178-9529",
                "image" => asset('images/og-default.jpg'),
                "address" => [
                    "@type" => "PostalAddress",
                    "addressLocality" => "Buenos Aires",
                    "addressRegion" => "Buenos Aires",
                    "addressCountry" => "AR"
                ]
            ],
            "areaServed" => ["CABA", "Zona Norte", "Gran Buenos Aires"]
        ];
    }

    // Datos estructurados para FAQPage (misma lista que se muestra visible en la página)
    if ($categoryFaqs) {
        $faqSchema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => array_map(function ($faq) {
                return [
                    "@type" => "Question",
                    "name" => $faq['q'],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $faq['a']
                    ]
                ];
            }, $categoryFaqs)
        ];
    }
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode($collectionSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@if($categoryFaqs)
<script type="application/ld+json">
{!! json_encode($serviceSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush
