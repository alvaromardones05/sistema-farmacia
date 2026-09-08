<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// PRODUCTOS - Técnico y Químico Farmacéutico
Route::prefix('productos')->name('productos.')->group(function () {
    Route::get('/', [ProductoController::class, 'index'])->name('index');
    Route::get('/{producto}', [ProductoController::class, 'show'])->name('show');
});

// VENTAS - Técnico y Químico Farmacéutico
Route::prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/', [VentaController::class, 'index'])->name('index');
    Route::get('/crear', [VentaController::class, 'create'])->name('create');
    Route::post('/', [VentaController::class, 'store'])->name('store');
    Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
});

// USUARIOS - Solo Administrador
Route::prefix('usuarios')->name('usuarios.')->middleware('can:administrar-sistema')->group(function () {
    Route::get('/', [UsuarioController::class, 'index'])->name('index');
    Route::get('/crear', [UsuarioController::class, 'create'])->name('create');
    Route::post('/', [UsuarioController::class, 'store'])->name('store');
    Route::get('/{user}/editar', [UsuarioController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UsuarioController::class, 'update'])->name('update');
});

require __DIR__.'/auth.php';
