<?php

namespace Database\Seeders;

use App\Models\Convenio;
use Illuminate\Database\Seeder;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        $convenios = [
            [
                'nombre' => 'Isapre Banmédica',
                'codigo_convenio' => 'ISP-BANMED',
                'tipo' => 'isapre',
                'descuento_pct' => 15.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Isapre Cruz Blanca',
                'codigo_convenio' => 'ISP-CRUZBL',
                'tipo' => 'isapre',
                'descuento_pct' => 10.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Fonasa',
                'codigo_convenio' => 'FONASA',
                'tipo' => 'fonasa',
                'descuento_pct' => 25.00,
                'activo' => true,
            ],
            [
                'nombre' => 'Particular',
                'codigo_convenio' => 'PARTICULAR',
                'tipo' => 'particular',
                'descuento_pct' => 0.00,
                'activo' => true,
            ],
        ];

        foreach ($convenios as $convenio) {
            Convenio::create($convenio);
        }
    }
}