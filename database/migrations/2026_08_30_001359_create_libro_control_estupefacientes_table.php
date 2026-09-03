<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libro_control_estupefacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('movimiento_stock_id')->constrained('movimientos_stock')->onDelete('restrict');
            $table->enum('tipo_libro', ['entrada', 'salida'])->default('salida');
            $table->bigInteger('numero_folio');
            $table->dateTime('fecha_registro');
            $table->integer('cantidad_movida');
            $table->foreignId('usuario_isp_id')->constrained('users')->onDelete('restrict');
            $table->text('observaciones')->nullable();
            $table->boolean('editable')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tipo_libro', 'numero_folio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libro_control_estupefacientes');
    }
};