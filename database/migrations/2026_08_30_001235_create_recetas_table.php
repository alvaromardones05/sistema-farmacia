<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_receta', 100)->unique();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('restrict');
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('restrict');
            $table->dateTime('fecha_emision');
            $table->dateTime('fecha_vencimiento');
            $table->enum('tipo', ['simple', 'retenida', 'cheque'])->default('simple');
            $table->enum('estado', ['vigente', 'utilizada', 'vencida', 'anulada'])->default('vigente');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};