<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_cobertura_producto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convenio_id')
                ->constrained('convenios')
                ->onDelete('cascade');

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('cascade');

            // Porcentaje que paga el paciente.
            // 0 = cobertura 100%, 100 = sin cobertura.
            $table->decimal('copago_pct', 5, 2)->default(100);

            // Si existe, reemplaza al porcentaje.
            $table->decimal('copago_fijo', 12, 2)->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->unique([
                'convenio_id',
                'producto_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_cobertura_producto');
    }
};