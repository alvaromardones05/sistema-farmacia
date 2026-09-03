<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_venta', 50)->unique();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('restrict');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('apertura_caja_id')->constrained('aperturas_caja')->onDelete('restrict');
            $table->foreignId('convenio_id')->nullable()->constrained('convenios')->onDelete('set null');
            $table->dateTime('fecha_venta');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('estado', ['pendiente', 'completada', 'devuelta', 'anulada'])->default('completada');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};