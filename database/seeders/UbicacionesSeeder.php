<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UbicacionAlmacen;


class UbicacionesSeeder extends Seeder
{
    public function run(): void
    {
        UbicacionAlmacen::create([
            'nombre' => 'Vitrina Principal',
            'tipo' => 'vitrina',
            'activo' => true,
        ]);

        UbicacionAlmacen::create([
            'nombre' => 'Bodega Central',
            'tipo' => 'bodega',
            'activo' => true,
        ]);

        UbicacionAlmacen::create([
            'nombre' => 'Bodega Frigorífica',
            'tipo' => 'bodega',
            'activo' => true,
        ]);
    }
}