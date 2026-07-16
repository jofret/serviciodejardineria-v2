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
| Rutas semánticas heredadas del sitio actual (serviciodejardineria.com.ar)
|--------------------------------------------------------------------------
| Estas URLs están posicionadas en buscadores y no deben cambiar de forma.
| A diferencia del patrón nativo de limpieza-terrenos (categoría en la raíz,
| post anidado bajo categoría), acá el post NO lleva segmento de categoría.
| Estructura: /publicaciones           (listado)
|             /publicaciones/{slug}    (detalle de post)
|             /categoria/{slug}        (listado por categoría)
*/
Route::get('/publicaciones', [PostController::class, 'index'])->name('posts.index');
Route::get('/publicaciones/{post:slug}', [PostController::class, 'show'])->name('post.show');
Route::get('/categoria/{category:slug}', [CategoryController::class, 'show'])->name('category.show');

/*
|--------------------------------------------------------------------------
| Contacto (envío del formulario embebido en home/posts vía #contacto-formulario)
|--------------------------------------------------------------------------
*/
Route::post('/contacto/enviar', [ContactController::class, 'send'])
    ->middleware(['honey', 'honey-recaptcha'])
    ->name('contacto.enviar');

// Encuestas públicas
Route::get('/encuesta/{token}', [App\Http\Controllers\SurveyController::class, 'show'])->name('survey.show');
Route::post('/encuesta/{token}', [App\Http\Controllers\SurveyController::class, 'store'])->name('survey.store');

/*
|--------------------------------------------------------------------------
| sitemap
|--------------------------------------------------------------------------
*/


// Sitemap
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);



/*
|--------------------------------------------------------------------------
| Fallback (página 404 personalizada)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

