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

        /*
        |--------------------------------------------------------------------------
        | Precios de productos
        |--------------------------------------------------------------------------
        | precio_venta = precio final con IVA incluido.
        */
        $precios = [
            'AMLO-5'    => 3490,
            'TRAMA-50'  => 5990,
            'ACET-600'  => 6490,
            'AMBRO-30'  => 4990,
            'AMOX-SUS'  => 6990,
            'DICL-GEL'  => 5490,
            'CLOTR-CRE' => 4490,
            'MUPI-CRE'  => 7990,
            'INSU-RAP'  => 15990,
            'SALBU-JAR' => 4990,
        ];

        $impuestoPct = 19;

        /*
        |--------------------------------------------------------------------------
        | Asignar precio a TODOS los productos activos
        |--------------------------------------------------------------------------
        */
        Producto::activos()->each(function (Producto $producto) use (
            $lista,
            $precios,
            $impuestoPct
        ) {
            /*
             * Si definimos manualmente un precio para el producto, lo usamos.
             *
             * Si existe otro producto activo que no está en el arreglo,
             * generamos un precio automáticamente para que ninguno quede
             * sin precio.
             */
            $precioVenta = $precios[$producto->codigo_interno]
                ?? random_int(1500, 15000);

            /*
             * El precio de venta ya incluye IVA.
             * Calculamos el precio neto.
             */
            $precioNeto = round(
                $precioVenta / (1 + $impuestoPct / 100),
                2
            );

            ProductoPrecio::updateOrCreate(
                [
                    'producto_id' => $producto->id,
                    'lista_precio_id' => $lista->id,
                    'vigente_hasta' => null,
                ],
                [
                    'precio_neto' => $precioNeto,
                    'impuesto_pct' => $impuestoPct,
                    'precio_venta' => $precioVenta,
                    'vigente_desde' => now()->startOfDay(),
                ]
            );
        });
    }
}