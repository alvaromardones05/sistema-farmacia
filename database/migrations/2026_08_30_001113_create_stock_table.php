<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones_almacen')->onDelete('restrict');
            $table->integer('cantidad');
            $table->timestamps();
            $table->index(['producto_id', 'ubicacion_id']);
            $table->unique(['producto_id', 'lote_id', 'ubicacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};