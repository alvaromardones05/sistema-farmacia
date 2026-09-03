<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Laboratorio;
use Illuminate\Database\Seeder;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = Categoria::all();
        $laboratorios = Laboratorio::all();

        $productos = [
            [
                'codigo_interno' => 'IBUP-500',
                'codigo_barra' => '7798156202157',
                'nombre' => 'Ibupirac 500mg',
                'principio_activo' => 'Ibuprofen',
                'forma_farmaceutica' => 'Comprimido',
                'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'comprimido',
                'tipo_receta' => 'ninguna',
                'es_controlado' => false,
                'stock_minimo' => 20,
                'stock_critico' => 10,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'AMOX-500',
                'codigo_barra' => '7798156202158',
                'nombre' => 'Amoxicilina 500mg',
                'principio_activo' => 'Amoxicillinum',
                'forma_farmaceutica' => 'Comprimido',
                'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'comprimido',
                'tipo_receta' => 'simple',
                'es_controlado' => false,
                'stock_minimo' => 15,
                'stock_critico' => 5,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'DIAZEPAM-5',
                'codigo_barra' => '7798156202159',
                'nombre' => 'Diazepam 5mg',
                'principio_activo' => 'Diazepam',
                'forma_farmaceutica' => 'Comprimido',
                'concentracion' => '5mg',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'comprimido',
                'tipo_receta' => 'retenida',
                'es_controlado' => true,
                'registro_isp' => 'ISP-2024-001',
                'stock_minimo' => 5,
                'stock_critico' => 2,
                'activo' => true,
            ],
        ];

        foreach ($productos as $producto) {
            $producto['categoria_id'] = $categorias->random()->id;

            Producto::create($producto);
        }
    }
}