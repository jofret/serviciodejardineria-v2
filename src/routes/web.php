<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Rutas principales
|--------------------------------------------------------------------------
*/

// Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Rutas semánticas (sin /categoria) - URLs limpias
|--------------------------------------------------------------------------
| Estructura: /desmalezado
|           /desmalezado/terreno-en-pilar
*/

// Listado de posts por categoría
Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('category.show');

// Post individual dentro de una categoría
Route::get('/{category:slug}/{post:slug}', [PostController::class, 'show'])->name('post.show');

/*
|--------------------------------------------------------------------------
| Rutas para tags (etiquetas)
|--------------------------------------------------------------------------
| Estructura: /tag/pilar
*/

Route::prefix('tag')->name('tag.')->group(function () {
    Route::get('/{tag:slug}', [TagController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Páginas estáticas
|--------------------------------------------------------------------------
*/

Route::view('/servicios', 'pages.services')->name('servicios');
Route::view('/contacto', 'pages.contact')->name('contacto');
Route::view('/presupuesto', 'pages.quote')->name('presupuesto');

/*
|--------------------------------------------------------------------------
| Listado general de posts
|--------------------------------------------------------------------------
*/

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

/*
|--------------------------------------------------------------------------
| Contacto (formulario y envío)
|--------------------------------------------------------------------------
*/

Route::get('/contacto', [ContactController::class, 'show'])->name('contacto');
Route::post('/contacto/enviar', [ContactController::class, 'send'])->name('contacto.enviar');

/*
|--------------------------------------------------------------------------
| Fallback (página 404 personalizada)
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return view('errors.404');
});