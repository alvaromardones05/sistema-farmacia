<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_reporte', 50);
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->json('parametros')->nullable();
            $table->enum('formato', ['pdf', 'excel'])->default('pdf');
            $table->string('archivo_path', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};