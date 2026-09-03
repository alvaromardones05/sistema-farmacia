<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stock')->onDelete('restrict');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones_almacen')->onDelete('restrict');
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste', 'transferencia', 'devolucion'])->default('entrada');
            $table->integer('cantidad_movida');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->string('referencia_documento', 100)->nullable();
            $table->text('motivo')->nullable();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
    }
};