<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Services\Altoparque\AltoparqueApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class HomeController extends Controller
{
    /**
     * Página de inicio
     */
    public function index(AltoparqueApiClient $altoparque)
    {
        // Últimos 6 posts destacados
        $featuredPosts = Post::with('category')
            ->where('is_published', true)
            ->where('is_featured', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(6)
            ->get();

        // Últimos 9 posts en general
        $latestPosts = Post::with('category')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(9)
            ->get();

        // Categorías activas
        $categories = Category::where('is_active', true)
            ->orderBy('order')
            ->get();

        // Publicaciones para la home: solo los 3 bloques de servicio que pidió el
        // usuario (mismos títulos que serviciodejardineria.com.ar hoy en producción),
        // cada uno mapeado a su categoría real -- no se muestran los otros 3 categorías.
        $homeServiceBlocks = [
            ['slug' => 'corte-de-pasto-y-jardineria', 'heading' => 'Servicio de Corte de Pasto'],
            ['slug' => 'poda-de-altura', 'heading' => 'Servicio de Poda de Altura'],
            ['slug' => 'desmalezado-de-terrenos', 'heading' => 'Servicio Desmalezado y limpieza de Terrenos'],
        ];

        $categoryPosts = collect($homeServiceBlocks)->map(function ($block) {
            $category = Category::where('slug', $block['slug'])->first();

            if (! $category) {
                return null;
            }

            $posts = Post::where('category_id', $category->id)
                ->where('is_published', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get();

            if ($posts->isEmpty()) {
                return null;
            }

            return [
                'category' => $category,
                'heading' => $block['heading'],
                'posts' => $posts,
            ];
        })->filter()->values();

        // Testimonios reales: ahora viven en altoparque.com (Survey ya no se
        // crea localmente) — se piden por API y se arma un objeto con la
        // misma forma que esperaba partials/testimonios.blade.php cuando
        // leía Eloquent local, para no tener que tocar esa vista.
        $allTestimonials = $this->testimonialsFromAltoparque($altoparque);
        $testimonials = $allTestimonials->take(9);
        // Antes home.blade.php contaba Survey::whereNotNull('comment') directo
        // (modelo ya borrado) para el stat "Clientes satisfechos" — se cuenta
        // acá sobre la misma respuesta de la API, sin el límite de 9 del carrusel.
        $testimonialsCount = $allTestimonials->count();

        return view('home', compact(
            'featuredPosts',
            'latestPosts',
            'categories',
            'categoryPosts',
            'testimonials',
            'testimonialsCount'
        ));
    }

    /**
     * Si la API central no responde, la home no debe caerse entera por
     * eso — se loguea y se muestra la home sin la sección de testimonios.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function testimonialsFromAltoparque(AltoparqueApiClient $altoparque)
    {
        try {
            $testimonials = collect($altoparque->testimonials());
        } catch (Throwable $e) {
            Log::warning('No se pudieron obtener los testimonios desde Altoparque.', [
                'error' => $e->getMessage(),
            ]);

            return collect();
        }

        $posts = Post::with('category')
            ->whereIn('id', $testimonials->pluck('post_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $testimonials
            ->filter(fn (array $t) => filled($t['comment']) && filled($t['customer_name']))
            ->map(fn (array $t) => (object) [
                'id' => $t['id'],
                'gender' => $t['gender'],
                'comment' => $t['comment'],
                'occupation' => $t['occupation'],
                'customer' => (object) ['name' => $t['customer_name']],
                'post' => $t['post_id'] ? $posts->get($t['post_id']) : null,
            ])
            ->values();
    }
}