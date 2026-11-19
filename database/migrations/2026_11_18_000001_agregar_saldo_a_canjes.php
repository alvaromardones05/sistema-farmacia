<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canjes', function (Blueprint $table) {
            // Cantidad pedida originalmente, solo se llena si el canje se cerró con saldo
            $table->unsignedInteger('cantidad_original')->nullable()->after('cantidad_solicitada');
            // Movimiento de entrada del saldo (a su ubicación de origen o a Cuarentena)
            $table->foreignId('movimiento_saldo_id')
                ->nullable()
                ->after('movimiento_entrada_id')
                ->constrained('movimientos_stock')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('canjes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('movimiento_saldo_id');
            $table->dropColumn('cantidad_original');
        });
    }
};
