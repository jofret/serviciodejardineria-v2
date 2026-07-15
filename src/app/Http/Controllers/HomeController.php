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
            'popularTags',
            'testimonials'
        ));
    }
}