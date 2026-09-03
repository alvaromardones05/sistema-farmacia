<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->enum('tipo_alerta', ['stock_bajo', 'stock_critico', 'vencimiento_proximo', 'vencido'])->default('stock_bajo');
            $table->integer('cantidad_actual')->nullable();
            $table->enum('estado', ['activa', 'resuelta', 'descartada'])->default('activa');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['producto_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_stock');
    }
};