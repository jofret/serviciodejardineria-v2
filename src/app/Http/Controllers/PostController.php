<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Muestra un post individual
     * URL: /categoria/desmalezado/terreno-pilar
     */
    public function show($categorySlug, $postSlug)
    {
        $category = Category::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $post = Post::with(['category', 'tags'])
            ->where('category_id', $category->id)
            ->where('slug', $postSlug)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Posts relacionados (misma categoría, excluyendo el actual)
        $relatedPosts = Post::with('category')
            ->where('category_id', $category->id)
            ->where('id', '!=', $post->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get();

        // Meta tags para SEO (usando los accesores del modelo)
        $metaTitle = $post->meta_title;
        $metaDescription = $post->meta_description;

        return view('posts.show', compact('post', 'relatedPosts', 'metaTitle', 'metaDescription'));
    }

    /**
     * Listado general de posts
     * URL: /posts
     */
    public function index(Request $request)
    {
        $posts = Post::with('category', 'tags')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('content', 'LIKE', "%{$search}%")
                      ->orWhere('location', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->category, function($query, $categorySlug) {
                $query->whereHas('category', function($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('posts.index', compact('posts', 'categories'));
    }
}