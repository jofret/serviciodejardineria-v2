<?php

use App\Http\Controllers\Relevador\AuthController;
use App\Http\Controllers\Relevador\RelevamientoController;
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
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    });
});
