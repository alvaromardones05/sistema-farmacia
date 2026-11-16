<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canjes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->foreignId('lote_original_id')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('ubicacion_origen_id')->constrained('ubicaciones_almacen')->onDelete('restrict');
            $table->foreignId('movimiento_salida_id')->nullable()->constrained('movimientos_stock')->onDelete('restrict');
            $table->foreignId('movimiento_entrada_id')->nullable()->constrained('movimientos_stock')->onDelete('restrict');
            $table->unsignedInteger('cantidad_solicitada');
            $table->foreignId('solicitado_por')->constrained('users')->onDelete('restrict');
            $table->dateTime('fecha_solicitud');
            $table->text('motivo_solicitud');
            $table->uuid('idempotency_token')->unique();
            $table->string('estado', 30)->default('pendiente')->index();
            $table->foreignId('decidido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('fecha_decision')->nullable();
            $table->text('motivo_decision')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('fecha_cierre')->nullable();
            $table->timestamps();
            $table->index(['lote_original_id', 'ubicacion_origen_id', 'estado'], 'canjes_stock_abierto_index');
        });

        Schema::create('canje_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canje_id')->constrained('canjes')->onDelete('restrict');
            $table->foreignId('movimiento_stock_id')->constrained('movimientos_stock')->onDelete('restrict');
            $table->unsignedInteger('cantidad');
            $table->dateTime('fecha_entrega');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->string('numero_documento', 100)->nullable();
            $table->uuid('idempotency_token')->unique();
            $table->timestamps();
        });

        Schema::create('canje_reposiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canje_id')->constrained('canjes')->onDelete('restrict');
            $table->foreignId('movimiento_stock_id')->constrained('movimientos_stock')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->unsignedInteger('cantidad');
            $table->dateTime('fecha_recepcion');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->string('numero_documento', 100)->nullable();
            $table->uuid('idempotency_token')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canje_reposiciones');
        Schema::dropIfExists('canje_entregas');
        Schema::dropIfExists('canjes');
    }
};