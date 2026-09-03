<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devolucion_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_id')->constrained('devoluciones')->onDelete('cascade');
            $table->foreignId('venta_detalle_id')->constrained('venta_detalle')->onDelete('restrict');
            $table->integer('cantidad_devuelta');
            $table->decimal('monto_devuelto', 12, 2);
            $table->boolean('retorna_a_stock')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devolucion_detalle');
    }
};