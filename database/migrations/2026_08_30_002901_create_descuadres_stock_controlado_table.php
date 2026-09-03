<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descuadres_stock_controlado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->integer('cantidad_esperada');
            $table->integer('cantidad_contada');
            $table->integer('diferencia');
            $table->dateTime('fecha_inventario');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->enum('estado', ['registrado', 'investigado', 'resuelto', 'rechazado'])->default('registrado');
            $table->text('explicacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuadres_stock_controlado');
    }
};