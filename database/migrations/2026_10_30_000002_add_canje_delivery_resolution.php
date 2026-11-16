<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canje_entregas', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_enviada')->default(0)->after('cantidad');
            $table->unsignedInteger('cantidad_aceptada')->default(0)->after('cantidad_enviada');
            $table->unsignedInteger('cantidad_rechazada')->default(0)->after('cantidad_aceptada');
            $table->string('documento_key', 64)->nullable()->after('numero_documento');
        });

        DB::table('canje_entregas')->where('cantidad_enviada', 0)->update([
            'cantidad_enviada' => DB::raw('cantidad'),
            'cantidad_aceptada' => DB::raw('cantidad'),
            'cantidad_rechazada' => 0,
        ]);

        Schema::table('canje_entregas', function (Blueprint $table) {
            $table->unique(['canje_id', 'documento_key'], 'canje_entregas_documento_unico');
        });

        Schema::table('canje_reposiciones', function (Blueprint $table) {
            $table->string('documento_key', 64)->nullable()->after('numero_documento');
            $table->unique(['canje_id', 'documento_key', 'lote_id'], 'canje_reposiciones_documento_lote_unico');
        });

        Schema::create('canje_devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canje_id')->constrained('canjes')->onDelete('restrict');
            $table->foreignId('canje_entrega_id')->constrained('canje_entregas')->onDelete('restrict');
            $table->foreignId('movimiento_stock_id')->constrained('movimientos_stock')->onDelete('restrict');
            $table->unsignedInteger('cantidad');
            $table->dateTime('fecha_devolucion');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->string('numero_documento', 100);
            $table->string('documento_key', 64);
            $table->uuid('idempotency_token')->unique();
            $table->timestamps();
            $table->unique(['canje_id', 'canje_entrega_id', 'documento_key'], 'canje_devoluciones_documento_entrega_unico');
        });

        Schema::create('canje_respuestas_entrega', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canje_id')->constrained('canjes')->onDelete('restrict');
            $table->foreignId('canje_entrega_id')->constrained('canje_entregas')->onDelete('restrict');
            $table->unsignedInteger('cantidad_aceptada');
            $table->unsignedInteger('cantidad_rechazada');
            $table->dateTime('fecha_respuesta');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->string('numero_documento', 100);
            $table->string('documento_key', 64);
            $table->uuid('idempotency_token')->unique();
            $table->timestamps();
            $table->unique(['canje_id', 'canje_entrega_id', 'documento_key'], 'canje_respuestas_documento_entrega_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canje_devoluciones');
        Schema::dropIfExists('canje_respuestas_entrega');

        Schema::table('canje_reposiciones', function (Blueprint $table) {
            $table->dropUnique('canje_reposiciones_documento_lote_unico');
            $table->dropColumn('documento_key');
        });

        Schema::table('canje_entregas', function (Blueprint $table) {
            $table->dropUnique('canje_entregas_documento_unico');
            $table->dropColumn(['cantidad_enviada', 'cantidad_aceptada', 'cantidad_rechazada', 'documento_key']);
        });
    }
};