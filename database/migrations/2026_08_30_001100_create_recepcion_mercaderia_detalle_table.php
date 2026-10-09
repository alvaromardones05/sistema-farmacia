<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepcion_mercaderia_detalle', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recepcion_mercaderia_id')
                ->constrained('recepciones_mercaderia')
                ->onDelete('cascade');

            $table->foreignId('orden_compra_detalle_id')
                ->constrained('orden_compra_detalle')
                ->onDelete('restrict');

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('restrict');

            $table->foreignId('lote_id')
                ->nullable()
                ->constrained('lotes')
                ->onDelete('restrict');

            $table->integer('cantidad_recibida');
            $table->unsignedBigInteger('precio_unitario');

            $table->string('numero_lote', 100);
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_mercaderia_detalle');
    }
};