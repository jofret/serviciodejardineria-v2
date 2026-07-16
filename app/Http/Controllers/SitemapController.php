<?php

namespace App\Http\Controllers;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        // Páginas estáticas
        $staticPages = [
            '/' => 1.0,
            '/publicaciones' => 0.9,
        ];

        foreach ($staticPages as $path => $priority) {
            $sitemap->add(
                Url::create($path)
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority($priority)
            );
        }

        // Categorías (solo activas)
        Category::active()->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create("/categoria/{$category->slug}")
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });

        // Posts publicados
        Post::where('is_published', true)
            ->where('published_at', '<=', now())
            ->get()
            ->each(function (Post $post) use ($sitemap) {
                $sitemap->add(
                    Url::create("/publicaciones/{$post->slug}")
                        ->setLastModificationDate($post->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.7)
                );
            });

        // Tags
        Tag::all()->each(function (Tag $tag) use ($sitemap) {
            $sitemap->add(
                Url::create("/tag/{$tag->slug}")
                    ->setLastModificationDate($tag->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6)
            );
        });

        return $sitemap->toResponse(request());
    }
}