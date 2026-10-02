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
                'codigo_interno' => 'AMLO-5',
                'codigo_barra' => '7798156202175',
                'nombre' => 'Amlodipino 5mg',
                'principio_activo' => 'Amlodipino',
                'forma_farmaceutica' => 'Comprimido',
                'concentracion' => '5mg',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'comprimido',
                'tipo_receta' => 'simple',
                'es_controlado' => false,
                'stock_minimo' => 20,
                'stock_critico' => 8,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'TRAMA-50',
                'codigo_barra' => '7798156202181',
                'nombre' => 'Tramadol 50mg',
                'principio_activo' => 'Tramadol',
                'forma_farmaceutica' => 'Cápsula',
                'concentracion' => '50mg',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'cápsula',
                'tipo_receta' => 'retenida',
                'es_controlado' => true,
                'registro_isp' => 'ISP-2024-003',
                'stock_minimo' => 5,
                'stock_critico' => 2,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'ACET-600',
                'codigo_barra' => '7798156202182',
                'nombre' => 'Acetilcisteína 600mg',
                'principio_activo' => 'Acetilcisteína',
                'forma_farmaceutica' => 'Granulado',
                'concentracion' => '600mg',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'sobre',
                'tipo_receta' => 'ninguna',
                'es_controlado' => false,
                'stock_minimo' => 10,
                'stock_critico' => 4,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'AMBRO-30',
                'codigo_barra' => '7798156202183',
                'nombre' => 'Ambroxol 30mg',
                'principio_activo' => 'Ambroxol',
                'forma_farmaceutica' => 'Jarabe',
                'concentracion' => '30mg/5ml',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'frasco',
                'tipo_receta' => 'ninguna',
                'es_controlado' => false,
                'stock_minimo' => 10,
                'stock_critico' => 4,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'AMOX-SUS',
                'codigo_barra' => '7798156202184',
                'nombre' => 'Amoxicilina Suspensión',
                'principio_activo' => 'Amoxicilina',
                'forma_farmaceutica' => 'Suspensión oral',
                'concentracion' => '250mg/5ml',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'frasco',
                'tipo_receta' => 'simple',
                'es_controlado' => false,
                'stock_minimo' => 10,
                'stock_critico' => 4,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'DICL-GEL',
                'codigo_barra' => '7798156202185',
                'nombre' => 'Diclofenaco Gel 1%',
                'principio_activo' => 'Diclofenaco',
                'forma_farmaceutica' => 'Gel',
                'concentracion' => '1%',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'tubo',
                'tipo_receta' => 'ninguna',
                'es_controlado' => false,
                'stock_minimo' => 10,
                'stock_critico' => 4,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'CLOTR-CRE',
                'codigo_barra' => '7798156202186',
                'nombre' => 'Clotrimazol Crema 1%',
                'principio_activo' => 'Clotrimazol',
                'forma_farmaceutica' => 'Crema',
                'concentracion' => '1%',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'tubo',
                'tipo_receta' => 'ninguna',
                'es_controlado' => false,
                'stock_minimo' => 10,
                'stock_critico' => 4,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'MUPI-CRE',
                'codigo_barra' => '7798156202187',
                'nombre' => 'Mupirocina 2%',
                'principio_activo' => 'Mupirocina',
                'forma_farmaceutica' => 'Ungüento',
                'concentracion' => '2%',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'tubo',
                'tipo_receta' => 'simple',
                'es_controlado' => false,
                'stock_minimo' => 8,
                'stock_critico' => 3,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'INSU-RAP',
                'codigo_barra' => '7798156202188',
                'nombre' => 'Insulina rápida',
                'principio_activo' => 'Insulina humana',
                'forma_farmaceutica' => 'Solución inyectable',
                'concentracion' => '100UI/ml',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'frasco',
                'tipo_receta' => 'simple',
                'es_controlado' => false,
                'stock_minimo' => 5,
                'stock_critico' => 2,
                'activo' => true,
            ],
            [
                'codigo_interno' => 'SALBU-JAR',
                'codigo_barra' => '7798156202189',
                'nombre' => 'Salbutamol Jarabe',
                'principio_activo' => 'Salbutamol',
                'forma_farmaceutica' => 'Jarabe',
                'concentracion' => '2mg/5ml',
                'laboratorio_id' => $laboratorios->random()->id,
                'unidad_medida' => 'frasco',
                'tipo_receta' => 'simple',
                'es_controlado' => false,
                'stock_minimo' => 10,
                'stock_critico' => 4,
                'activo' => true,
            ],
        ];

        foreach ($productos as $producto) {
            $producto['categoria_id'] = $categorias->random()->id;

            Producto::create($producto);
        }
    }
}