<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStockCalculationTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoria = Categoria::create(['nombre' => 'Prueba', 'activo' => true]);
    }

    public function test_stock_categories_and_critical_thresholds_are_distinct(): void
    {
        $this->assertStockCase([
            'bodega' => 100,
        ], [100, 100, 0, 0, 100], false);

        $this->assertStockCase([
            'cuarentena' => 100,
        ], [100, 0, 0, 100, 0], true);

        $this->assertStockCase([
            'canje' => 20,
        ], [20, 0, 0, 20, 0], true);

        $this->assertStockCase([
            'bodega' => 30,
            'vitrina' => 10,
            'cuarentena' => 5,
            'canje' => 5,
        ], [50, 30, 10, 10, 40], false);
    }

    public function test_sellable_stock_obeys_storage_condition_and_lot_eligibility(): void
    {
        $producto = $this->createProduct('refrigerado');
        $this->addStock($producto, 'frigorifica', 4);
        $this->addStock($producto, 'vitrina_refrigerada', 7);
        $this->addStock($producto, 'bodega', 20);
        $this->addStock($producto, 'vitrina', 30);
        $this->addStock($producto, 'vitrina_refrigerada', 8, 'activo', today()->subDay());
        $this->addStock($producto, 'vitrina_refrigerada', 9, 'agotado');

        $this->assertSame(7, $producto->getStockVendible());
        $this->assertSame(28, $producto->getStockUtilizable());
    }

    private function assertStockCase(array $cantidades, array $esperado, bool $critico): void
    {
        $producto = $this->createProduct();

        foreach ($cantidades as $tipo => $cantidad) {
            $this->addStock($producto, $tipo, $cantidad);
        }

        [$fisico, $reserva, $vendible, $noVendible, $utilizable] = $esperado;

        $this->assertSame($fisico, $producto->getStockTotal());
        $this->assertSame($fisico, $producto->obtenerStock());
        $this->assertSame($reserva, $producto->getStockReserva());
        $this->assertSame($vendible, $producto->getStockVendible());
        $this->assertSame($noVendible, $producto->getStockNoVendible());
        $this->assertSame($utilizable, $producto->getStockUtilizable());
        $this->assertSame($critico, $producto->estaEnStockCriticoUtilizable());
    }

    private function createProduct(string $condicion = 'ambiente'): Producto
    {
        static $codigo = 0;

        return Producto::create([
            'nombre' => 'Producto de prueba',
            'codigo_interno' => 'TEST-' . ++$codigo,
            'activo' => true,
            'stock_minimo' => 20,
            'stock_critico' => 10,
            'condicion_almacenamiento' => $condicion,
            'categoria_id' => $this->categoria->id,
            'unidad_medida' => 'unidad',
            'precio_base' => 1000,
        ]);
    }

    private function addStock(
        Producto $producto,
        string $tipo,
        int $cantidad,
        string $estadoLote = 'activo',
        $fechaVencimiento = null
    ): void {
        $ubicacion = UbicacionAlmacen::firstOrCreate(
            ['tipo' => $tipo],
            ['nombre' => $tipo, 'activo' => true]
        );
        $lote = Lote::create([
            'producto_id' => $producto->id,
            'numero_lote' => 'LOTE-' . uniqid('', true),
            'fecha_vencimiento' => $fechaVencimiento ?? today()->addYear(),
            'estado' => $estadoLote,
            'cantidad_inicial' => $cantidad,
        ]);

        Stock::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'cantidad' => $cantidad,
        ]);
    }
}
