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

            $table->foreignId('paciente_id')
                ->nullable()
                ->constrained('pacientes')
                ->onDelete('restrict');

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('apertura_caja_id')
                ->constrained('aperturas_caja')
                ->onDelete('restrict');

            $table->foreignId('convenio_id')
                ->nullable()
                ->constrained('convenios')
                ->onDelete('set null');

            $table->dateTime('fecha_venta');

            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('descuento')->default(0);
            $table->unsignedBigInteger('impuesto')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            $table->enum(
                'estado',
                ['pendiente', 'completada', 'devuelta', 'anulada']
            )->default('pendiente');

            $table->text('observaciones')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};