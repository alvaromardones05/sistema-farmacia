<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apertura_caja_id')->constrained('aperturas_caja')->onDelete('restrict');
            $table->enum('tipo', ['venta', 'pago', 'reembolso', 'deposito', 'gasto'])->default('venta');
            $table->decimal('monto', 12, 2);
            $table->string('referencia_documento', 100)->nullable();
            $table->text('concepto')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};