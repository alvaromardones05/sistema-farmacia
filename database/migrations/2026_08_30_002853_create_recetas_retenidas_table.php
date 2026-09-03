<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas_retenidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->onDelete('restrict');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->dateTime('fecha_retencion');
            $table->string('razon_retencion', 255);
            $table->enum('estado', ['retenida', 'dispensada', 'devuelta'])->default('retenida');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas_retenidas');
    }
};