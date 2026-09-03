<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rut', 12)->unique()->after('id');
            $table->string('apellidos', 100)->after('name');
            $table->string('telefono', 20)->nullable()->after('email');
            $table->string('numero_registro_tecnico', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rut', 'apellidos', 'telefono', 'numero_registro_tecnico', 'activo', 'must_change_password', 'last_login_at', 'deleted_at']);
        });
    }
};