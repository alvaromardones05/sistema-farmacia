<?php

namespace Database\Seeders;

use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use Illuminate\Database\Seeder;

class PrecioSeeder extends Seeder
{
    public function run(): void
    {
        $lista = ListaPrecio::firstOrCreate(
            ['nombre' => 'Lista Normal'],
            [
                'descripcion' => 'Lista de precios general',
                'tipo' => 'normal',
                'vigente_desde' => now()->startOfDay(),
                'vigente_hasta' => null,
                'activa' => true,
            ]
        );

        Producto::activos()->each(function (Producto $producto) use ($lista) {
            // Evita duplicar si el producto ya tiene un precio vigente en esta lista
            $yaTienePrecio = ProductoPrecio::where('producto_id', $producto->id)
                ->where('lista_precio_id', $lista->id)
                ->whereNull('vigente_hasta')
                ->exists();

            if ($yaTienePrecio) {
                return;
            }

            $precioNeto = random_int(500, 15000);
            $impuestoPct = 19;

            ProductoPrecio::create([
                'producto_id' => $producto->id,
                'lista_precio_id' => $lista->id,
                'precio_neto' => $precioNeto,
                'impuesto_pct' => $impuestoPct,
                'precio_venta' => round($precioNeto * (1 + $impuestoPct / 100)),
                'vigente_desde' => now()->startOfDay(),
                'vigente_hasta' => null,
            ]);
        });
    }
}