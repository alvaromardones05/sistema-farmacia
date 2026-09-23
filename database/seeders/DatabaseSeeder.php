<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        //PERMISOS DEL SISTEMA

        $permisos = [
            'gestionar_usuarios',
            'gestionar_productos',
            'gestionar_precios',
            'gestionar_stock',
            'ver_stock',
            'gestionar_convenios',
            'gestionar_recetas',
            'validar_recetas',
            'autorizar_libro_isp',
            'anular_ventas',
            'cerrar_caja',
            'ver_reportes_globales',
        ];

        /*
        |--------------------------------------------------------------------------
        | CREAR PERMISOS
        |--------------------------------------------------------------------------
        | firstOrCreate evita duplicar permisos si ejecutamos el seeder
        | más de una vez.
        */

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ADMINISTRADOR
        |--------------------------------------------------------------------------
        | Tiene todos los permisos del sistema.
        | Esta configuración es provisional y podrá ajustarse posteriormente.
        */

        $adminRole = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions($permisos);


        //QUÍMICO FARMACÉUTICO

        $quimicoRole = Role::firstOrCreate([
            'name' => 'Químico Farmacéutico',
            'guard_name' => 'web',
        ]);

        $quimicoRole->syncPermissions([
            'gestionar_productos',
            'gestionar_precios',
            'gestionar_recetas',
            'validar_recetas',
            'autorizar_libro_isp',
            'anular_ventas',
            'ver_reportes_globales',
            'ver_stock',
        ]);


        //TÉCNICO FARMACÉUTICO (VENDEDOR)

        $tecnicoRole = Role::firstOrCreate([
            'name' => 'Técnico Farmacéutico',
            'guard_name' => 'web',
        ]);

        $tecnicoRole->syncPermissions([
            'gestionar_recetas',
            'validar_recetas',
            'anular_ventas',
            'cerrar_caja',
            'gestionar_productos',
            'gestionar_precios',
            'gestionar_stock',
            'ver_stock',
            'gestionar_convenios',
            
        ]);


        //BODEGUERO

        $bodegueroRole = Role::firstOrCreate([
            'name' => 'Bodeguero',
            'guard_name' => 'web',
        ]);

        $bodegueroRole->syncPermissions([
            'gestionar_stock',
        ]);

        /*
        | SEEDERS DE DATOS
        |--------------------------------------------------------------------------
        | Los roles y permisos anteriores forman parte de la configuración
        | del sistema.
        */

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
