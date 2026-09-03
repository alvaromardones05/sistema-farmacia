<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apertura_caja_id')->constrained('aperturas_caja')->onDelete('restrict');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('fecha_cierre');
            $table->decimal('saldo_inicial', 12, 2);
            $table->decimal('monto_vendido', 12, 2)->default(0);
            $table->decimal('monto_reembolso', 12, 2)->default(0);
            $table->decimal('monto_gastos', 12, 2)->default(0);
            $table->decimal('saldo_contado', 12, 2);
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->enum('estado', ['aceptado', 'con_diferencia', 'rechazado'])->default('aceptado');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};