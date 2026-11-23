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

        if ($categorias->isEmpty() || $laboratorios->isEmpty()) {
            throw new \LogicException('Debes tener categorías y laboratorios creados antes de ejecutar ProductosSeeder.');
        }

        $productos = [
            // --- ANALGÉSICOS Y ANTIINFLAMATORIOS ---
            [
                'codigo_interno' => 'PARA-500', 'codigo_barra' => '7801234500011', 'nombre' => 'Paracetamol 500mg',
                'principio_activo' => 'Paracetamol', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 50, 'stock_critico' => 20, 'activo' => true, 'precio_base' => 1500,
            ],
            [
                'codigo_interno' => 'IBUP-400', 'codigo_barra' => '7801234500028', 'nombre' => 'Ibuprofeno 400mg',
                'principio_activo' => 'Ibuprofeno', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '400mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 40, 'stock_critico' => 15, 'activo' => true, 'precio_base' => 2000,
            ],
            [
                'codigo_interno' => 'IBUP-600', 'codigo_barra' => '7801234500035', 'nombre' => 'Ibuprofeno 600mg',
                'principio_activo' => 'Ibuprofeno', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '600mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 2500,
            ],
            [
                'codigo_interno' => 'DICL-500', 'codigo_barra' => '7801234500042', 'nombre' => 'Diclofenaco 500mg',
                'principio_activo' => 'Diclofenaco', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 2200,
            ],
            [
                'codigo_interno' => 'KETO-30', 'codigo_barra' => '7801234500059', 'nombre' => 'Ketorolaco 30mg',
                'principio_activo' => 'Ketorolaco', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '30mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 3500,
            ],
            [
                'codigo_interno' => 'DICL-GEL', 'codigo_barra' => '7801234500066', 'nombre' => 'Diclofenaco Gel 1%',
                'principio_activo' => 'Diclofenaco', 'forma_farmaceutica' => 'Gel', 'concentracion' => '1%',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'tubo', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 10, 'stock_critico' => 4, 'activo' => true, 'precio_base' => 4500,
            ],

            // --- ANTIBIÓTICOS ---
            [
                'codigo_interno' => 'AMOX-500', 'codigo_barra' => '7801234500073', 'nombre' => 'Amoxicilina 500mg',
                'principio_activo' => 'Amoxicilina', 'forma_farmaceutica' => 'Cápsula', 'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'cápsula', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 4000,
            ],
            [
                'codigo_interno' => 'AMOX-SUS', 'codigo_barra' => '7801234500080', 'nombre' => 'Amoxicilina Suspensión',
                'principio_activo' => 'Amoxicilina', 'forma_farmaceutica' => 'Suspensión oral', 'concentracion' => '250mg/5ml',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'frasco', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 4500,
            ],
            [
                'codigo_interno' => 'AZIT-500', 'codigo_barra' => '7801234500097', 'nombre' => 'Azitromicina 500mg',
                'principio_activo' => 'Azitromicina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 6000,
            ],
            [
                'codigo_interno' => 'CEFA-500', 'codigo_barra' => '7801234500103', 'nombre' => 'Cefadroxilo 500mg',
                'principio_activo' => 'Cefadroxilo', 'forma_farmaceutica' => 'Cápsula', 'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'cápsula', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 5500,
            ],
            [
                'codigo_interno' => 'CLAR-500', 'codigo_barra' => '7801234500110', 'nombre' => 'Claritromicina 500mg',
                'principio_activo' => 'Claritromicina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '500mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 7500,
            ],

            // --- ANTIHIPERTENSIVOS Y CARDIOVASCULARES ---
            [
                'codigo_interno' => 'LOSA-50', 'codigo_barra' => '7801234500127', 'nombre' => 'Losartán 50mg',
                'principio_activo' => 'Losartán', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '50mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 3500,
            ],
            [
                'codigo_interno' => 'ENAL-10', 'codigo_barra' => '7801234500134', 'nombre' => 'Enalapril 10mg',
                'principio_activo' => 'Enalapril', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '10mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 3000,
            ],
            [
                'codigo_interno' => 'AMLO-5', 'codigo_barra' => '7801234500141', 'nombre' => 'Amlodipino 5mg',
                'principio_activo' => 'Amlodipino', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '5mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 40, 'stock_critico' => 15, 'activo' => true, 'precio_base' => 2800,
            ],
            [
                'codigo_interno' => 'ATEN-50', 'codigo_barra' => '7801234500158', 'nombre' => 'Atenolol 50mg',
                'principio_activo' => 'Atenolol', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '50mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 25, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 2500,
            ],
            [
                'codigo_interno' => 'ATOR-20', 'codigo_barra' => '7801234500165', 'nombre' => 'Atorvastatina 20mg',
                'principio_activo' => 'Atorvastatina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '20mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 5000,
            ],
            [
                'codigo_interno' => 'ASPI-100', 'codigo_barra' => '7801234500172', 'nombre' => 'Aspirina 100mg',
                'principio_activo' => 'Ácido Acetilsalicílico', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '100mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 50, 'stock_critico' => 20, 'activo' => true, 'precio_base' => 1800,
            ],
            [
                'codigo_interno' => 'CLOP-75', 'codigo_barra' => '7801234500189', 'nombre' => 'Clopidogrel 75mg',
                'principio_activo' => 'Clopidogrel', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '75mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 6500,
            ],

            // --- GASTROINTESTINALES ---
            [
                'codigo_interno' => 'OMEP-20', 'codigo_barra' => '7801234500196', 'nombre' => 'Omeprazol 20mg',
                'principio_activo' => 'Omeprazol', 'forma_farmaceutica' => 'Cápsula', 'concentracion' => '20mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'cápsula', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 50, 'stock_critico' => 20, 'activo' => true, 'precio_base' => 2000,
            ],
            [
                'codigo_interno' => 'RANI-300', 'codigo_barra' => '7801234500202', 'nombre' => 'Ranitidina 300mg',
                'principio_activo' => 'Ranitidina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '300mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 2500,
            ],
            [
                'codigo_interno' => 'DOMP-10', 'codigo_barra' => '7801234500219', 'nombre' => 'Domperidona 10mg',
                'principio_activo' => 'Domperidona', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '10mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 3200,
            ],
            [
                'codigo_interno' => 'METO-10', 'codigo_barra' => '7801234500226', 'nombre' => 'Metoclopramida 10mg',
                'principio_activo' => 'Metoclopramida', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '10mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 25, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 2100,
            ],

            // --- ENDOCRINOS Y DIABETES ---
            [
                'codigo_interno' => 'METF-850', 'codigo_barra' => '7801234500233', 'nombre' => 'Metformina 850mg',
                'principio_activo' => 'Metformina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '850mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 40, 'stock_critico' => 15, 'activo' => true, 'precio_base' => 3000,
            ],
            [
                'codigo_interno' => 'GLIM-2', 'codigo_barra' => '7801234500240', 'nombre' => 'Glibenclamida 2.5mg',
                'principio_activo' => 'Glibenclamida', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '2.5mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 2200,
            ],
            [
                'codigo_interno' => 'LEVO-50', 'codigo_barra' => '7801234500257', 'nombre' => 'Levotiroxina 50mcg',
                'principio_activo' => 'Levotiroxina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '50mcg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 4000,
            ],
            [
                'codigo_interno' => 'INSU-RAP', 'codigo_barra' => '7801234500264', 'nombre' => 'Insulina rápida',
                'principio_activo' => 'Insulina humana', 'forma_farmaceutica' => 'Solución inyectable', 'concentracion' => '100UI/ml',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'frasco', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'refrigerado',
                'stock_minimo' => 10, 'stock_critico' => 4, 'activo' => true, 'precio_base' => 12000,
            ],
            [
                'codigo_interno' => 'INSU-GLA', 'codigo_barra' => '7801234500271', 'nombre' => 'Insulina Glargina',
                'principio_activo' => 'Insulina Glargina', 'forma_farmaceutica' => 'Solución inyectable', 'concentracion' => '100UI/ml',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'frasco', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'refrigerado',
                'stock_minimo' => 8, 'stock_critico' => 3, 'activo' => true, 'precio_base' => 25000,
            ],

            // --- RESPIRATORIOS Y ALERGIAS ---
            [
                'codigo_interno' => 'SALBU-INH', 'codigo_barra' => '7801234500288', 'nombre' => 'Salbutamol Inhalador',
                'principio_activo' => 'Salbutamol', 'forma_farmaceutica' => 'Aerosol', 'concentracion' => '100mcg/dosis',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'inhalador', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 8500,
            ],
            [
                'codigo_interno' => 'SALBU-JAR', 'codigo_barra' => '7801234500295', 'nombre' => 'Salbutamol Jarabe',
                'principio_activo' => 'Salbutamol', 'forma_farmaceutica' => 'Jarabe', 'concentracion' => '2mg/5ml',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'frasco', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 3800,
            ],
            [
                'codigo_interno' => 'LORA-10', 'codigo_barra' => '7801234500301', 'nombre' => 'Loratadina 10mg',
                'principio_activo' => 'Loratadina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '10mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 40, 'stock_critico' => 15, 'activo' => true, 'precio_base' => 1500,
            ],
            [
                'codigo_interno' => 'CETI-10', 'codigo_barra' => '7801234500318', 'nombre' => 'Cetirizina 10mg',
                'principio_activo' => 'Cetirizina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '10mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 1800,
            ],

            // --- MUCOSÍTICOS Y OTROS ---
            [
                'codigo_interno' => 'ACET-600', 'codigo_barra' => '7801234500325', 'nombre' => 'Acetilcisteína 600mg',
                'principio_activo' => 'Acetilcisteína', 'forma_farmaceutica' => 'Granulado', 'concentracion' => '600mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'sobre', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 2500,
            ],
            [
                'codigo_interno' => 'AMBR-30', 'codigo_barra' => '7801234500332', 'nombre' => 'Ambroxol Jarabe',
                'principio_activo' => 'Ambroxol', 'forma_farmaceutica' => 'Jarabe', 'concentracion' => '30mg/5ml',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'frasco', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 3000,
            ],
            [
                'codigo_interno' => 'CLOTR-CRE', 'codigo_barra' => '7801234500349', 'nombre' => 'Clotrimazol Crema 1%',
                'principio_activo' => 'Clotrimazol', 'forma_farmaceutica' => 'Crema', 'concentracion' => '1%',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'tubo', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 4000,
            ],
            [
                'codigo_interno' => 'MUPI-UNG', 'codigo_barra' => '7801234500356', 'nombre' => 'Mupirocina 2%',
                'principio_activo' => 'Mupirocina', 'forma_farmaceutica' => 'Ungüento', 'concentracion' => '2%',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'tubo', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 10, 'stock_critico' => 4, 'activo' => true, 'precio_base' => 5500,
            ],
            [
                'codigo_interno' => 'HIDRO-CRE', 'codigo_barra' => '7801234500363', 'nombre' => 'Hidrocortisona Crema 1%',
                'principio_activo' => 'Hidrocortisona', 'forma_farmaceutica' => 'Crema', 'concentracion' => '1%',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'tubo', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 3200,
            ],
            [
                'codigo_interno' => 'VITA-C', 'codigo_barra' => '7801234500370', 'nombre' => 'Vitamina C 1g',
                'principio_activo' => 'Ácido Ascórbico', 'forma_farmaceutica' => 'Comprimido efervescente', 'concentracion' => '1g',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'ninguna',
                'requiere_receta' => false, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 30, 'stock_critico' => 10, 'activo' => true, 'precio_base' => 2500,
            ],

            // --- MEDICAMENTOS CONTROLADOS (RETENIDAS) ---
            [
                'codigo_interno' => 'TRAM-50', 'codigo_barra' => '7801234500387', 'nombre' => 'Tramadol 50mg',
                'principio_activo' => 'Tramadol', 'forma_farmaceutica' => 'Cápsula', 'concentracion' => '50mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'cápsula', 'tipo_receta' => 'retenida',
                'requiere_receta' => true, 'es_controlado' => true, 'registro_isp' => 'ISP-2024-001', 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 10, 'stock_critico' => 4, 'activo' => true, 'precio_base' => 4500,
            ],
            [
                'codigo_interno' => 'CLONA-2', 'codigo_barra' => '7801234500394', 'nombre' => 'Clonazepam 2mg',
                'principio_activo' => 'Clonazepam', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '2mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'retenida',
                'requiere_receta' => true, 'es_controlado' => true, 'registro_isp' => 'ISP-2024-002', 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 3800,
            ],
            [
                'codigo_interno' => 'DIAZ-10', 'codigo_barra' => '7801234500400', 'nombre' => 'Diazepam 10mg',
                'principio_activo' => 'Diazepam', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '10mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'retenida',
                'requiere_receta' => true, 'es_controlado' => true, 'registro_isp' => 'ISP-2024-003', 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 3500,
            ],
            [
                'codigo_interno' => 'SERT-50', 'codigo_barra' => '7801234500417', 'nombre' => 'Sertralina 50mg',
                'principio_activo' => 'Sertralina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '50mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'retenida',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 6500,
            ],
            [
                'codigo_interno' => 'FLUO-20', 'codigo_barra' => '7801234500424', 'nombre' => 'Fluoxetina 20mg',
                'principio_activo' => 'Fluoxetina', 'forma_farmaceutica' => 'Cápsula', 'concentracion' => '20mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'cápsula', 'tipo_receta' => 'retenida',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 20, 'stock_critico' => 8, 'activo' => true, 'precio_base' => 5000,
            ],
            [
                'codigo_interno' => 'WARF-5', 'codigo_barra' => '7801234500431', 'nombre' => 'Warfarina 5mg',
                'principio_activo' => 'Warfarina', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '5mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'retenida',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 4000,
            ],

            // --- OTROS (UROLOGÍA, VACUNAS) ---
            [
                'codigo_interno' => 'SILD-50', 'codigo_barra' => '7801234500448', 'nombre' => 'Sildenafil 50mg',
                'principio_activo' => 'Sildenafil', 'forma_farmaceutica' => 'Comprimido', 'concentracion' => '50mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'comprimido', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 10, 'stock_critico' => 4, 'activo' => true, 'precio_base' => 8000,
            ],
            [
                'codigo_interno' => 'TAMS-04', 'codigo_barra' => '7801234500455', 'nombre' => 'Tamsulosina 0.4mg',
                'principio_activo' => 'Tamsulosina', 'forma_farmaceutica' => 'Cápsula', 'concentracion' => '0.4mg',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'cápsula', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'ambiente',
                'stock_minimo' => 15, 'stock_critico' => 5, 'activo' => true, 'precio_base' => 7500,
            ],
            [
                'codigo_interno' => 'VACU-FLU', 'codigo_barra' => '7801234500462', 'nombre' => 'Vacuna Influenza',
                'principio_activo' => 'Virus Influenza', 'forma_farmaceutica' => 'Suspensión inyectable', 'concentracion' => '0.5ml',
                'laboratorio_id' => $laboratorios->random()->id, 'unidad_medida' => 'dosis', 'tipo_receta' => 'simple',
                'requiere_receta' => true, 'es_controlado' => false, 'condicion_almacenamiento' => 'refrigerado',
                'stock_minimo' => 10, 'stock_critico' => 3, 'activo' => true, 'precio_base' => 15000,
            ],
        ];

        $productosSinCondicion = collect($productos)
            ->filter(fn ($producto) => !in_array(
                $producto['condicion_almacenamiento'] ?? null,
                ['ambiente', 'refrigerado'],
                true
            ))
            ->pluck('codigo_interno')
            ->all();

        if ($productosSinCondicion) {
            throw new \LogicException(
                'Confirma la condición de almacenamiento (ambiente o refrigerado) para estos productos antes de ejecutar ProductosSeeder: '
                . implode(', ', $productosSinCondicion)
            );
        }

        // 1. Aseguramos que exista una lista de precios base
        $listaPrecio = \App\Models\ListaPrecio::firstOrCreate(
            ['nombre' => 'Particular'],
            [
                'vigente_desde' => now()->subDay(),
            ]
        );

        foreach ($productos as $productoData) {
            $productoData['categoria_id'] = $categorias->random()->id;

            // 2. Crear el producto
            $producto = Producto::create($productoData);

            // 3. Calcular el precio con IVA (19%)
            $precioNeto = (float) $productoData['precio_base'];
            $ivaPct = 19.00;
            $precioVenta = round($precioNeto * (1 + $ivaPct / 100), 2);

            // 4. Crear el precio con todos los campos obligatorios
            $producto->precios()->create([
                'lista_precio_id' => $listaPrecio->id,
                'precio_neto' => $precioNeto,
                'impuesto_pct' => $ivaPct,
                'precio_venta' => $precioVenta,
                'vigente_desde' => now()->subDay(),
                'vigente_hasta' => null,
            ]);
        }
    }
}
