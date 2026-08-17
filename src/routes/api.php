<?php

use App\Http\Controllers\Api\PostApiController;
use App\Http\Middleware\EnsureCentralApiToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API para el panel central (altoparque.com)
|--------------------------------------------------------------------------
| Dirección inversa a la integración de Claudia (donde este sitio es cliente
| de la API de altoparque.com, ver config('services.altoparque')): acá
| altoparque.com es el cliente y este sitio es el servidor.
*/
Route::middleware(EnsureCentralApiToken::class)->group(function () {
    Route::get('/posts', [PostApiController::class, 'index']);
    Route::get('/posts/{id}', [PostApiController::class, 'show']);
});
