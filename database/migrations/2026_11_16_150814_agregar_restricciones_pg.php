<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE stock ADD CONSTRAINT stock_cantidad_no_negativa CHECK (cantidad >= 0)'
        );

        Schema::table('ventas', function (Blueprint $table) {
            $table->index(['fecha_venta', 'estado'], 'ventas_fecha_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_fecha_estado_idx');
        });

        DB::statement('ALTER TABLE stock DROP CONSTRAINT stock_cantidad_no_negativa');
    }
};
