<?php

use App\Http\Controllers\Relevador\AuthController;
use App\Http\Controllers\Relevador\RelevamientoController;
use App\Http\Controllers\Relevador\RelevamientoWorkItemController;
use App\Http\Middleware\EnsureUserIsRelevador;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel del relevador (mobile-first, fuera del admin de Filament)
|--------------------------------------------------------------------------
*/
Route::prefix('relevador')->name('relevador.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');

    Route::middleware(EnsureUserIsRelevador::class)->group(function () {
        Route::get('/', [RelevamientoController::class, 'index'])->name('dashboard');
        Route::get('/{relevamiento}', [RelevamientoController::class, 'show'])->name('show');
        Route::post('/{relevamiento}', [RelevamientoController::class, 'update'])->name('update');
        Route::post('/{relevamiento}/autoguardar', [RelevamientoController::class, 'autosave'])->name('autosave');
        Route::post('/{relevamiento}/fotos', [RelevamientoController::class, 'uploadPhoto'])->name('photos.store');
        Route::delete('/{relevamiento}/fotos/{media}', [RelevamientoController::class, 'deletePhoto'])->name('photos.destroy');

        Route::post('/{relevamiento}/items', [RelevamientoWorkItemController::class, 'store'])->name('items.store');
        Route::post('/{relevamiento}/items/{item}', [RelevamientoWorkItemController::class, 'update'])->name('items.update');
        Route::delete('/{relevamiento}/items/{item}', [RelevamientoWorkItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/{relevamiento}/items/{item}/fotos', [RelevamientoWorkItemController::class, 'uploadPhoto'])->name('items.photos.store');
        Route::delete('/{relevamiento}/items/{item}/fotos/{media}', [RelevamientoWorkItemController::class, 'deletePhoto'])->name('items.photos.destroy');

        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    });
});
