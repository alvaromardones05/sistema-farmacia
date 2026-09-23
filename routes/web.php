<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\Web\UsuarioController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;

// Página principal
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


// =====================================================
// RUTAS PROTEGIDAS
// =====================================================

Route::middleware(['auth', 'verified'])->group(function () {

    // =================================================
    // DASHBOARD
    // =================================================

    // Todos los usuarios autenticados pueden acceder
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
    // QUÍMICO + TÉCNICO FARMACÉUTICO
    // PRODUCTOS - LOTES - VENTAS
    // =================================================

    Route::middleware([
        'role:Químico Farmacéutico|Técnico Farmacéutico'
    ])->group(function () {

        // PRODUCTOS
        Route::resource('productos', ProductoController::class);

        // LOTES
        Route::resource('lotes', LoteController::class);

        // VENTAS
        Route::resource('ventas', VentaController::class);
    });


    // =================================================
    // ADMINISTRADOR
    // USUARIOS
    // =================================================

    Route::middleware('role:Administrador')->group(function () {

        Route::resource('usuarios', UsuarioController::class);
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

    // LOTES
    Route::middleware('role:Químico Farmacéutico|Técnico Farmacéutico|Bodeguero')->group(function () {

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

// Lectura: quien tenga ver_stock (Técnico y Químico, y quien más se agregue después)
Route::middleware('permission:ver_stock')->group(function () {

    Route::get('/inventario', [InventarioController::class, 'index'])
        ->name('inventario.index');

    Route::get('/inventario/{producto}/stock', [InventarioController::class, 'stockPorUbicacion'])
        ->name('inventario.stock');
    
    Route::get('/inventario/alertas', [InventarioController::class, 'alertas'])
        ->name('inventario.alertas');   

    Route::get('/inventario/proximos-vencer', [InventarioController::class, 'proximosAVencer'])
        ->name('inventario.proximos-vencer');

    Route::get('/inventario/historial', [InventarioController::class, 'historial'])
        ->name('inventario.historial');

});

// Escritura: quien tenga gestionar_stock (Técnico)
Route::middleware('permission:gestionar_stock')->group(function () {

    Route::post('/inventario/transferir', [InventarioController::class, 'transferir'])
        ->name('inventario.transferir');

    Route::get('/inventario/ajustar', [InventarioController::class, 'mostrarAjustar'])
        ->name('inventario.ajustar.form');

    Route::post('/inventario/ajuste', [InventarioController::class, 'ajuste'])
        ->name('inventario.ajuste');
    
    Route::get('/inventario/transferir', [InventarioController::class, 'mostrarTransferir'])
    ->name('inventario.transferir.form');

    

});

});


// Autenticación de Breeze
require __DIR__.'/auth.php';