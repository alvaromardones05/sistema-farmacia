<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permisos = [
            'gestionar_usuarios',
            'gestionar_stock',
            'autorizar_libro_isp',
            'validar_recetas',
            'anular_ventas',
            'ver_reportes_globales',
            'cerrar_caja',
            'gestionar_productos',
            'gestionar_precios',
            'gestionar_convenios',
        ];

        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Crear roles y asignar permisos
        $adminRole = Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($permisos);

        $bodegueroRole = Role::create(['name' => 'Bodeguero', 'guard_name' => 'web']);
        $bodegueroRole->givePermissionTo(['gestionar_stock', 'validar_recetas', 'anular_ventas', 'cerrar_caja']);

        $tecnicoRole = Role::create(['name' => 'Técnico Farmacéutico', 'guard_name' => 'web']);
        $tecnicoRole->givePermissionTo(['gestionar_stock', 'validar_recetas', 'ver_reportes_globales']);

        $quimicoRole = Role::create(['name' => 'Químico Farmacéutico', 'guard_name' => 'web']);
        $quimicoRole->givePermissionTo(['autorizar_libro_isp', 'gestionar_productos', 'gestionar_precios', 'ver_reportes_globales']);

        // Ejecutar otros seeders
        $this->call([
            UsuariosSeeder::class,
            UbicacionesSeeder::class,
            CategoriasSeeder::class,
            LaboratoriosSeeder::class,
            ProductosSeeder::class,
            ProveedoresSeeder::class,
            ConveniosSeeder::class,
            CajaSeeder::class,
        ]);
    }
}