<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 12)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('registro_profesional', 100)->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->string('telefo no', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('consultorio', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};