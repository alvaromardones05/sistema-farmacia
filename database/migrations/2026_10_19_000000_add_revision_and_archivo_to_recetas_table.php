<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'vigente', 'rechazada', 'utilizada', 'vencida', 'anulada'])
                ->default('pendiente')->change();
            $table->string('archivo_path', 255)->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('recetas')->whereIn('estado', ['pendiente', 'rechazada'])->exists()) {
            throw new RuntimeException('No se puede revertir mientras existan recetas pendientes o rechazadas.');
        }

        Schema::table('recetas', function (Blueprint $table) {
            $table->enum('estado', ['vigente', 'utilizada', 'vencida', 'anulada'])
                ->default('vigente')->change();
            $table->dropColumn('archivo_path');
        });
    }
};
