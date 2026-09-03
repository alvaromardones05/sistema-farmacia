<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_interno', 30)->unique();
            $table->string('codigo_barra', 50)->nullable()->unique();
            $table->string('nombre', 150);
            $table->string('principio_activo', 150)->nullable();
            $table->string('forma_farmaceutica', 50)->nullable();
            $table->string('concentracion', 50)->nullable();
            $table->foreignId('categoria_id')->constrained('categorias_producto')->onDelete('restrict');
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->onDelete('set null');
            $table->string('unidad_medida', 20);
            $table->boolean('requiere_receta')->default(false);
            $table->enum('tipo_receta', ['ninguna', 'simple', 'retenida', 'cheque'])->default('ninguna');
            $table->boolean('es_controlado')->default(false);
            $table->string('registro_isp', 50)->nullable();
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_critico')->default(0);
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};