<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SurveyController;

/*
|--------------------------------------------------------------------------
| Rutas principales
|--------------------------------------------------------------------------
*/

// Página de inicio
Route::get('/', [HomeController::class, 'index'])->name('home');

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
| Rutas semánticas (sin /categoria) - URLs limpias
|--------------------------------------------------------------------------
| Estructura: /desmalezado
|           /desmalezado/terreno-en-pilar
*/
Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/{category:slug}/{post:slug}', [PostController::class, 'show'])->name('post.show');

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
Route::post('/contacto/enviar', [App\Http\Controllers\ContactController::class, 'send'])
    ->middleware(['honey', 'honey-recaptcha'])
    ->name('contacto.enviar');

// Encuestas públicas
Route::get('/encuesta/{token}', [App\Http\Controllers\SurveyController::class, 'show'])->name('survey.show');
Route::post('/encuesta/{token}', [App\Http\Controllers\SurveyController::class, 'store'])->name('survey.store');

/*
|--------------------------------------------------------------------------
| Fallback (página 404 personalizada)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return view('errors.404');
});