<?php

namespace Database\Seeders;

use App\Models\Caja;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        Caja::create([
            'numero_caja' => '001',
            'descripcion' => 'Caja Principal - Vitrina',
            'activa' => true,
        ]);

        Caja::create([
            'numero_caja' => '002',
            'descripcion' => 'Caja Secundaria',
            'activa' => true,
        ]);
    }
}