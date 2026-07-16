<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Survey;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Página de inicio
     */
    public function index()
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

        // Tags populares (con más posts)
        $popularTags = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(15)
            ->get();

        // Testimonios reales: encuestas de satisfacción publicadas por el admin
        $testimonials = Survey::with(['customer', 'post.category'])
            ->where('is_published', true)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest('answered_at')
            ->limit(9)
            ->get();

        return view('home', compact(
            'featuredPosts',
            'latestPosts',
            'categories',
            'categoryPosts',
            'popularTags',
            'testimonials'
        ));
    }
}