<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * Consumido por altoparque.com (panel central) para poblar el Select de
 * post_id en SurveyResource — ver App\Services\SatelliteSites\PostLookupService
 * del lado central. Protegido por EnsureCentralApiToken, no por Sanctum.
 */
class PostApiController extends Controller
{
    /**
     * GET /api/posts?search=texto
     */
    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');

        $posts = Post::query()
            ->published()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('title')
            ->limit(50)
            ->get(['id', 'title']);

        return response()->json(['data' => $posts]);
    }

    /**
     * GET /api/posts/{id}
     */
    public function show(int $id)
    {
        $post = Post::query()->published()->find($id, ['id', 'title']);

        if (! $post) {
            return response()->json(['message' => 'Post no encontrado.'], 404);
        }

        return response()->json(['data' => $post]);
    }
}
