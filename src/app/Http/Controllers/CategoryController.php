<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Muestra los posts de una categoría
     * URL: /categoria/desmalezado
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $posts = Post::with('category', 'tags')
            ->where('category_id', $category->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // Meta tags para SEO
        $metaTitle = $category->meta_title;
        $metaDescription = $category->meta_description;

        return view('categories.show', compact('category', 'posts', 'metaTitle', 'metaDescription'));
    }
}