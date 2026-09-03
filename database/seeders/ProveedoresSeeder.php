<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'rut' => '76543210-1',
                'nombre_razon_social' => 'Distribuidora Médica Chile',
                'contacto_nombre' => 'Roberto González',
                'telefono' => '227654321',
                'email' => 'contacto@distribmedica.cl',
                'ciudad' => 'Santiago',
                'region' => 'Metropolitana',
                'activo' => true,
            ],
            [
                'rut' => '87654321-2',
                'nombre_razon_social' => 'Farmalogística S.A.',
                'contacto_nombre' => 'Patricia López',
                'telefono' => '227654322',
                'email' => 'ventas@farmalogistica.cl',
                'ciudad' => 'Valparaíso',
                'region' => 'Valparaíso',
                'activo' => true,
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}