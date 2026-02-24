<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Muestra los posts que tienen un tag específico
     * URL: /tag/pilar
     */
    public function show($slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->with('category')
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // Meta tags para SEO
        $metaTitle = $tag->meta_title ?? "Posts etiquetados con {$tag->name}";
        $metaDescription = $tag->meta_description ?? "Trabajos de limpieza etiquetados con {$tag->name}. Ver fotos antes/después.";

        return view('tags.show', compact('tag', 'posts', 'metaTitle', 'metaDescription'));
    }
}