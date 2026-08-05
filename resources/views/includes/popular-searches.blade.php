@php
    $popularSearchCategories = \App\Models\Category::active()
        ->whereHas('posts', fn ($q) => $q->where('is_published', true))
        ->orderBy('order')
        ->get();

    $popularSearchCategorySlugs = $popularSearchCategories->pluck('slug');

    $popularSearchItems = $popularSearchCategories->map(fn ($category) => [
            'label' => $category->name,
            'url' => route('category.show', $category),
        ])
        ->concat(
            \App\Models\Tag::withCount('posts')
                ->whereHas('posts', fn ($q) => $q->where('is_published', true))
                ->orderByDesc('posts_count')
                ->limit(15)
                ->get()
                ->reject(fn ($tag) => $popularSearchCategorySlugs->contains($tag->slug))
                ->map(fn ($tag) => [
                    'label' => '#' . $tag->name,
                    'url' => route('tag.show', $tag),
                ])
        );
@endphp

@if($popularSearchItems->isNotEmpty())
<section class="py-16 bg-gray-50 rounded-xl shadow-sm mb-8">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-8">Búsquedas populares</h2>
        <div class="flex flex-wrap justify-center gap-3">
            @foreach($popularSearchItems as $item)
                <a href="{{ $item['url'] }}" class="bg-white px-4 py-2 rounded-full text-gray-700 hover:bg-green-700 hover:text-white transition shadow-sm">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
