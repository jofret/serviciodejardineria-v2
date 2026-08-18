<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovedLinkController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

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
| Estructura: /serviciode/pilar
| Prefijo nuevo (antes /tag/pilar) — sin redirect desde el viejo porque
| estas páginas se crearon en este rewrite y no había nada indexado.
*/
Route::prefix('serviciode')->name('tag.')->group(function () {
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
|             /servicio-de/{slug}      (listado por categoría)
| El prefijo de categoría era /categoria/{slug} y pasó a /servicio-de/{slug}
| (los slugs en sí no cambiaron, siguen preservando el contenido migrado del
| sitio legacy). Se deja un redirect 301 desde /categoria/{slug} porque esas
| URLs sí llegaron a estar posicionadas en buscadores.
*/
Route::get('/publicaciones', [PostController::class, 'index'])->name('posts.index');
Route::get('/publicaciones/{post:slug}', [PostController::class, 'show'])->name('post.show');
Route::get('/servicio-de/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::redirect('/categoria/{slug}', '/servicio-de/{slug}', 301);

/*
|--------------------------------------------------------------------------
| Contacto (envío del formulario embebido en home/posts vía #contacto-formulario)
|--------------------------------------------------------------------------
*/
Route::post('/contacto/enviar', [ContactController::class, 'send'])
    ->middleware(['honey', 'honey-recaptcha'])
    ->name('contacto.enviar');

/*
|--------------------------------------------------------------------------
| Links viejos (encuesta/presupuesto/conformidad) — sistema mudado al panel
| central de Altoparque
|--------------------------------------------------------------------------
| Estas rutas ya no tienen su controller/modelo real (ver borrado de
| Survey/ServiceOrder/WorkOrder): quedan enlaces sueltos de WhatsApp/email
| de antes de la migración. En vez de un 500, se muestra un aviso simple.
*/
Route::get('/encuesta/{token}', [MovedLinkController::class, 'show'])->name('survey.show');
Route::post('/encuesta/{token}', [MovedLinkController::class, 'show'])->name('survey.store');
Route::get('/presupuesto/{token}', [MovedLinkController::class, 'show'])->name('budget.show');
Route::post('/presupuesto/{token}/aceptar', [MovedLinkController::class, 'show'])->name('budget.accept');
Route::get('/presupuesto/{token}/descargar', [MovedLinkController::class, 'show'])->name('budget.download');
Route::get('/conformidad/{token}', [MovedLinkController::class, 'show'])->name('conformity.show');
Route::post('/conformidad/{token}/confirmar', [MovedLinkController::class, 'show'])->name('conformity.confirm');

/*
|--------------------------------------------------------------------------
| sitemap
|--------------------------------------------------------------------------
*/

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Webhook de WhatsApp Business Cloud API (Claudia)
|--------------------------------------------------------------------------
| Meta llama a estas dos rutas: GET para verificar la URL al dar de alta el
| webhook, POST por cada mensaje entrante. Sin CSRF (ver bootstrap/app.php)
| porque Meta no manda token de sesión de Laravel.
*/
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'receive'])->name('whatsapp.webhook.receive');

/*
|--------------------------------------------------------------------------
| Fallback (página 404 personalizada)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
