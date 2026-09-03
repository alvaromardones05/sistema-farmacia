<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_tributarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->onDelete('cascade');
            $table->enum('tipo', ['boleta_electronica', 'factura_electronica'])->default('boleta_electronica');
            $table->bigInteger('folio');
            $table->dateTime('fecha_emision');
            $table->string('pdf_path', 255)->nullable();
            $table->enum('estado_sii', ['pendiente', 'aceptado', 'rechazado', 'simulado'])->default('simulado');
            $table->string('track_id', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_tributarios');
    }
};