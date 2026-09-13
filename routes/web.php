<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\Web\UsuarioController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\DashboardController;

// Página principal
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


// RUTAS PROTEGIDAS
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // =================================================
    // PERFIL
    // =================================================

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // PRODUCTOS
    // Técnico + Químico Farmacéutico

    Route::middleware('role:Químico Farmacéutico|Técnico Farmacéutico')->group(function () {

        Route::get('/productos', [ProductoController::class, 'index'])
            ->name('productos.index');

        Route::get('/productos/crear', [ProductoController::class, 'create'])
            ->name('productos.create');

        Route::post('/productos', [ProductoController::class, 'store'])
            ->name('productos.store');

        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])
            ->name('productos.edit');

        Route::put('/productos/{producto}', [ProductoController::class, 'update'])
            ->name('productos.update');

        Route::get('/productos/{producto}', [ProductoController::class, 'show'])
            ->name('productos.show');

        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->name('productos.destroy');

    });


    // =================================================
    // VENTAS
    // Técnico + Químico 
    // =================================================

    Route::middleware('role:Químico Farmacéutico|Técnico Farmacéutico')->group(function () {

        Route::get('/ventas', [VentaController::class, 'index'])
            ->name('ventas.index');

        Route::get('/ventas/crear', [VentaController::class, 'create'])
            ->name('ventas.create');

        Route::post('/ventas', [VentaController::class, 'store'])
            ->name('ventas.store');

        Route::get('/ventas/{venta}', [VentaController::class, 'show'])
            ->name('ventas.show');

    });


    // =================================================
    // USUARIOS
    // Solo Administrador
    // =================================================

    Route::middleware('role:Administrador')->group(function () {

        Route::get('/usuarios', [UsuarioController::class, 'index'])
            ->name('usuarios.index');

        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])
            ->name('usuarios.create');

        Route::post('/usuarios', [UsuarioController::class, 'store'])
            ->name('usuarios.store');

        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])
            ->name('usuarios.edit');

        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])
            ->name('usuarios.update');

        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])
            ->name('usuarios.destroy');

    });


    // =================================================
    // LOTES
    // Bodeguero + Químico 
    // =================================================

    Route::middleware('Químico Farmacéutico|Técnico Farmacéutico|Bodeguero')->group(function () {

        Route::get('/lotes', [LoteController::class, 'index'])
            ->name('lotes.index');

        Route::get('/lotes/crear', [LoteController::class, 'create'])
            ->name('lotes.create');

        Route::post('/lotes', [LoteController::class, 'store'])
            ->name('lotes.store');

        Route::get('/lotes/{lote}/editar', [LoteController::class, 'edit'])
            ->name('lotes.edit');

        Route::put('/lotes/{lote}', [LoteController::class, 'update'])
            ->name('lotes.update');

        Route::delete('/lotes/{lote}', [LoteController::class, 'destroy'])
            ->name('lotes.destroy');

    });

});


// Autenticación de Breeze
require __DIR__.'/auth.php';