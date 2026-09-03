<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_subidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_original', 255);
            $table->string('nombre_almacenado', 255);
            $table->string('mime_type', 100);
            $table->bigInteger('tamano_bytes');
            $table->string('hash_sha256', 64);
            $table->enum('tipo_documento', ['receta', 'otro'])->default('otro');
            $table->boolean('validado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_subidos');
    }
};