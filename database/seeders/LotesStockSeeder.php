<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LotesStockSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $productos = Producto::where('activo', true)->get();

            if ($productos->isEmpty()) {
                throw new \LogicException(
                    'No existen productos activos. Ejecuta ProductosSeeder primero.'
                );
            }

            // Obtener ubicaciones de almacenamiento
            $ubicaciones = UbicacionAlmacen::where('activo', true)
                ->get()
                ->groupBy('tipo');

            foreach ($productos as $producto) {

                // Determinar ubicación según almacenamiento
                $tipoUbicacion = match ($producto->condicion_almacenamiento) {
                    'ambiente' => 'bodega',
                    'refrigerado' => 'frigorifica',
                    default => throw new \LogicException(
                        "Condición de almacenamiento inválida: {$producto->nombre}"
                    ),
                };

                $ubicacion = $ubicaciones->get($tipoUbicacion)?->first();

                if (!$ubicacion) {
                    throw new \LogicException(
                        "No existe una ubicación activa de tipo {$tipoUbicacion}."
                    );
                }

                // Evitar duplicar lotes si se ejecuta nuevamente
                if ($producto->lotes()->exists()) {
                    continue;
                }

                // Entre 1 y 2 lotes por producto
                $cantidadLotes = rand(1, 2);

                for ($i = 1; $i <= $cantidadLotes; $i++) {

                    // Cantidad aleatoria entre 1 y 20 unidades
                    $cantidad = rand(1, 20);

                    $fechaFabricacion = now()
                        ->subMonths(rand(1, 8))
                        ->startOfDay();

                    $fechaVencimiento = now()
                        ->addMonths(rand(6, 36))
                        ->startOfDay();

                    // Crear lote
                    $lote = Lote::create([
                        'producto_id' => $producto->id,
                        'numero_lote' => 'LOT-'
                            . $producto->codigo_interno
                            . '-'
                            . strtoupper(Str::random(6)),
                        'fecha_fabricacion' => $fechaFabricacion,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'cantidad_inicial' => $cantidad,
                        'estado' => 'activo',
                        'observaciones' => 'Lote generado para datos de prueba.',
                    ]);

                    // Crear stock asociado al lote
                    Stock::create([
                        'producto_id' => $producto->id,
                        'lote_id' => $lote->id,
                        'ubicacion_id' => $ubicacion->id,
                        'cantidad' => $cantidad,
                    ]);
                }
            }
        });
    }
}
