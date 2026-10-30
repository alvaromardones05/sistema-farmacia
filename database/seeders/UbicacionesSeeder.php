<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UbicacionAlmacen;


class UbicacionesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'bodega' => 'Bodega Central',
            'frigorifica' => 'Cámara Frigorífica',
            'vitrina' => 'Vitrina',
            'vitrina_refrigerada' => 'Vitrina Refrigerada',
            'cuarentena' => 'Productos no aptos para venta',
            'canje' => 'Zona de Canje',
        ] as $tipo => $nombre) {
            UbicacionAlmacen::updateOrCreate(
                ['tipo' => $tipo],
                ['nombre' => $nombre, 'activo' => true]
            );
        }
    }
}
