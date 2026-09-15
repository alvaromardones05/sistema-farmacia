<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\Web\UsuarioController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\DashboardController;

// Página principal.
// Muestra la vista de bienvenida de Laravel directamente.
Route::view('/', 'welcome')->name('home');

// =====================================================
// RUTAS PROTEGIDAS
// =====================================================

Route::middleware('auth')->group(function () {

    // =================================================
    // DASHBOARD
    // =================================================

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    // =================================================
    // PERFIL
    // =================================================

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    // =================================================
    // PRODUCTOS
    // Técnico + Químico Farmacéutico
    // =================================================

    Route::middleware('role:Químico Farmacéutico|Técnico Farmacéutico')->group(function () {

        // Listado de productos
        Route::get('/productos', [ProductoController::class, 'index'])
            ->name('productos.index');

        // Formulario para crear un producto
        Route::get('/productos/crear', [ProductoController::class, 'create'])
            ->name('productos.create');

        // Guarda un producto nuevo
        Route::post('/productos', [ProductoController::class, 'store'])
            ->name('productos.store');

        // Formulario para editar un producto
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])
            ->name('productos.edit');

        // Actualiza un producto
        Route::put('/productos/{producto}', [ProductoController::class, 'update'])
            ->name('productos.update');

        // Muestra un producto
        Route::get('/productos/{producto}', [ProductoController::class, 'show'])
            ->name('productos.show');

        // Elimina un producto
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->name('productos.destroy');
    });


    // =================================================
    // VENTAS
    // Técnico + Químico Farmacéutico
    // =================================================

    Route::middleware('role:Químico Farmacéutico|Técnico Farmacéutico')->group(function () {

        // Lista las ventas registradas
        Route::get('/ventas', [VentaController::class, 'index'])
            ->name('ventas.index');

        // Muestra el formulario para crear una venta
        Route::get('/ventas/crear', [VentaController::class, 'create'])
            ->name('ventas.create');

        // Guarda una nueva venta
        Route::post('/ventas', [VentaController::class, 'store'])
            ->name('ventas.store');

        // Muestra el detalle de una venta
        Route::get('/ventas/{venta}', [VentaController::class, 'show'])
            ->name('ventas.show');

        // Anula una venta existente
        Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular'])
            ->name('ventas.anular');

        // Muestra el reporte diario de ventas
        Route::get('/ventas/reporte-diario', [VentaController::class, 'reporteDiario'])
            ->name('ventas.reporte-diario');
    });


    // =================================================
    // USUARIOS
    // Solo Administrador
    // =================================================

    Route::middleware('role:Administrador')->group(function () {

        // Lista los usuarios
        Route::get('/usuarios', [UsuarioController::class, 'index'])
            ->name('usuarios.index');

        // Formulario para crear un usuario
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])
            ->name('usuarios.create');

        // Guarda un usuario nuevo
        Route::post('/usuarios', [UsuarioController::class, 'store'])
            ->name('usuarios.store');

        // Formulario para editar un usuario
        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])
            ->name('usuarios.edit');

        // Actualiza un usuario
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])
            ->name('usuarios.update');

        // Elimina un usuario
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])
            ->name('usuarios.destroy');
    });


    // =================================================
    // LOTES
    // Bodeguero + Técnico + Químico Farmacéutico
    // =================================================

    Route::middleware('role:Químico Farmacéutico|Técnico Farmacéutico|Bodeguero')->group(function () {

        // Lista los lotes
        Route::get('/lotes', [LoteController::class, 'index'])
            ->name('lotes.index');

        // Formulario para crear un lote
        Route::get('/lotes/crear', [LoteController::class, 'create'])
            ->name('lotes.create');

        // Guarda un lote nuevo
        Route::post('/lotes', [LoteController::class, 'store'])
            ->name('lotes.store');

        // Formulario para editar un lote
        Route::get('/lotes/{lote}/editar', [LoteController::class, 'edit'])
            ->name('lotes.edit');

        // Actualiza un lote
        Route::put('/lotes/{lote}', [LoteController::class, 'update'])
            ->name('lotes.update');

        // Elimina un lote
        Route::delete('/lotes/{lote}', [LoteController::class, 'destroy'])
            ->name('lotes.destroy');
    });


    // =================================================
    // INVENTARIO
    // Usuarios con permiso gestionar_stock
    // =================================================

    Route::middleware('permission:gestionar_stock')->group(function () {

        // Muestra el inventario general
        Route::get('/inventario', [InventarioController::class, 'index'])
            ->name('inventario.index');

        // Transfiere stock entre ubicaciones
        Route::post('/inventario/transferir', [InventarioController::class, 'transferir'])
            ->name('inventario.transferir');

        // Realiza un ajuste de stock
        Route::post('/inventario/ajuste', [InventarioController::class, 'ajuste'])
            ->name('inventario.ajuste');

        // Consulta el stock de un producto por ubicación
        Route::get('/inventario/{producto}/stock', [InventarioController::class, 'stockPorUbicacion'])
            ->name('inventario.stock');
    });

});


// =====================================================
// AUTENTICACIÓN DE LARAVEL BREEZE
// =====================================================

require __DIR__.'/auth.php';