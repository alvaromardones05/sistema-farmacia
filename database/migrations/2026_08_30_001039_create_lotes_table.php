<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->string('numero_lote', 100)->unique();
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento');
            $table->integer('cantidad_inicial');
            $table->enum('estado', ['activo', 'agotado', 'vencido'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['producto_id', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};