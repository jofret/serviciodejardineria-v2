@extends('layouts.app')

@section('meta_title', $post->meta_title)
@section('meta_description', 'Limpieza y Desmalezado de terrenos WhatsApp ✅ 11 7178 9529 | ' . strip_tags($post->excerpt ?? Str::limit($post->content, 150)))
@section('og_image', $post->getFirstMediaUrl('featured', 'og-image') ?? asset('images/default-og.jpg'))

@section('content')
    <nav class="mb-6 text-sm text-gray-600">
        <a href="/" class="hover:text-green-700">Inicio</a> /
        <a href="/{{ $post->category->slug }}" class="hover:text-green-700">{{ $post->category->name }}</a> /
        <span class="text-gray-800">{{ $post->title }}</span>
    </nav>

    <article class="bg-white rounded-lg shadow-lg overflow-hidden p-8">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $post->title }}</h1>
        <div class="prose max-w-none">{!! nl2br(e($post->content)) !!}</div>
        
        @if($post->tags->count() > 0)
        <div class="mt-8">
            <h3 class="font-semibold mb-2">Etiquetas:</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <a href="/tag/{{ $tag->slug }}" class="bg-gray-200 px-3 py-1 rounded-full text-sm hover:bg-green-600 hover:text-white transition">#{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
@endsection