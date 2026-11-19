
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar la restricción CHECK anterior.
        DB::statement('
            ALTER TABLE recetas
            DROP CONSTRAINT IF EXISTS recetas_estado_check
        ');

        // Crear la nueva restricción con los estados permitidos.
        DB::statement("
            ALTER TABLE recetas
            ADD CONSTRAINT recetas_estado_check
            CHECK (estado IN (
                'pendiente',
                'vigente',
                'rechazada',
                'utilizada',
                'vencida',
                'anulada'
            ))
        ");

        // Agregar la ruta del archivo adjunto.
        Schema::table('recetas', function (Blueprint $table) {
            $table->string('archivo_path', 255)->nullable();
        });
    }

    public function down(): void
    {
        // Evitar perder información al revertir.
        if (DB::table('recetas')
            ->whereIn('estado', ['pendiente', 'rechazada'])
            ->exists()) {
            throw new RuntimeException(
                'No se puede revertir mientras existan recetas pendientes o rechazadas.'
            );                  
        }

        // Restaurar la restricción anterior.
        DB::statement('
            ALTER TABLE recetas
            DROP CONSTRAINT IF EXISTS recetas_estado_check
        ');

        DB::statement("
            ALTER TABLE recetas
            ADD CONSTRAINT recetas_estado_check
            CHECK (estado IN (
                'vigente',
                'utilizada',
                'vencida',
                'anulada'
            ))
        ");

        // Eliminar la columna agregada.
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn('archivo_path');
        });
    }
};
