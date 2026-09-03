<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativos_libro', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_libro', ['entrada', 'salida'])->unique();
            $table->bigInteger('proximo_folio')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativos_libro');
    }
};