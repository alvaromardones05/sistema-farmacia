<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones_mercaderia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('orden_compra_id')
                ->nullable()
                ->constrained('ordenes_compra')
                ->onDelete('set null');

            // Usuario que registra físicamente la recepción
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('restrict');

            // Usuario que posteriormente revisa
            $table->foreignId('revisado_por')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->dateTime('fecha_recepcion');
            $table->dateTime('fecha_revision')->nullable();

            $table->string('numero_comprobante', 100)->nullable();

            // Estado de aprobación
            $table->enum('estado', [
                'pendiente',
                'aprobada',
                'rechazada'
            ])->default('pendiente');

            // Indica si efectivamente llegó toda la orden o solo una parte
            $table->enum('tipo_recepcion', [
                'completa',
                'parcial'
            ])->nullable();

            $table->text('motivo_rechazo')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones_mercaderia');
    }
};