<?php

namespace Tests\Feature;

use App\Models\AperturaCaja;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\CierreCaja;
use App\Models\ListaPrecio;
use App\Models\Lote;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pruebas de los flujos de caja (apertura y arqueo) y de venta (stock, pago,
 * movimiento de caja y validaciones). Informe N3.
 *
 * Datos de prueba: un producto de venta libre almacenado a temperatura
 * ambiente (se vende desde la ubicación "vitrina"), precio neto 1000 y
 * 5 unidades en un lote vigente.
 */
class VentaCajaFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $cajero;
    private Caja $caja;
    private Producto $producto;
    private UbicacionAlmacen $vitrina;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $cerrarCaja = Permission::findOrCreate('cerrar_caja', 'web');
        $rol = Role::findOrCreate('Técnico Farmacéutico', 'web');
        $rol->givePermissionTo($cerrarCaja);

        $this->cajero = User::factory()->create();
        $this->cajero->assignRole($rol);

        $this->caja = Caja::create([
            'numero_caja' => 'T-001',
            'descripcion' => 'Caja de prueba',
            'activa' => true,
        ]);

        $this->vitrina = UbicacionAlmacen::create([
            'nombre' => 'Vitrina',
            'tipo' => 'vitrina',
            'activo' => true,
        ]);

        $categoria = Categoria::create(['nombre' => 'Prueba', 'activo' => true]);
        $this->producto = Producto::create([
            'codigo_interno' => 'VENTA-001',
            'nombre' => 'Producto de venta libre',
            'precio_base' => 1000,
            'categoria_id' => $categoria->id,
            'unidad_medida' => 'unidad',
            'condicion_almacenamiento' => 'ambiente',
            'es_controlado' => false,
            'stock_minimo' => 1,
            'stock_critico' => 0,
            'activo' => true,
        ]);

        $lista = ListaPrecio::create([
            'nombre' => 'Lista Normal',
            'tipo' => 'normal',
            'vigente_desde' => today()->subDay(),
            'activa' => true,
        ]);
        ProductoPrecio::create([
            'producto_id' => $this->producto->id,
            'lista_precio_id' => $lista->id,
            'precio_neto' => 1000,
            'impuesto_pct' => 19,
            'precio_venta' => 1190,
            'vigente_desde' => today()->subDay(),
        ]);

        $lote = Lote::create([
            'producto_id' => $this->producto->id,
            'numero_lote' => 'VENTA-LOTE-001',
            'fecha_fabricacion' => today()->subMonths(2),
            'fecha_vencimiento' => today()->addYear(),
            'cantidad_inicial' => 5,
            'estado' => 'activo',
        ]);
        Stock::registrarEntrada(
            $this->producto,
            $lote,
            $this->vitrina,
            5,
            $this->cajero->id,
            'Ingreso de prueba',
            'Stock inicial'
        );
    }

    private function abrirCajaDirecto(int $saldoInicial = 10000): AperturaCaja
    {
        return AperturaCaja::create([
            'caja_id' => $this->caja->id,
            'usuario_id' => $this->cajero->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => $saldoInicial,
            'estado' => 'abierta',
        ]);
    }

    private function datosVenta(int $cantidad = 2, array $extra = []): array
    {
        return array_merge([
            'metodo_pago' => 'efectivo',
            'tipo_documento' => 'boleta',
            'detalles' => [
                ['producto_id' => $this->producto->id, 'cantidad' => $cantidad],
            ],
        ], $extra);
    }

    private function stockActual(): int
    {
        return (int) Stock::where('producto_id', $this->producto->id)->sum('cantidad');
    }

    // ------------------------------------------------------------ CAJA

    public function test_apertura_de_caja_registra_el_saldo_inicial(): void
    {
        $this->actingAs($this->cajero)
            ->post(route('caja.abrir'), [
                'caja_id' => $this->caja->id,
                'saldo_inicial' => 10000,
            ])
            ->assertRedirect(route('caja.index'));

        $apertura = AperturaCaja::firstOrFail();
        $this->assertSame('abierta', $apertura->estado);
        $this->assertSame($this->cajero->id, (int) $apertura->usuario_id);
        $this->assertSame(10000, (int) $apertura->saldo_inicial);
    }

    public function test_no_permite_abrir_una_segunda_caja_mientras_hay_una_abierta(): void
    {
        $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('caja.abrir'), [
                'caja_id' => $this->caja->id,
                'saldo_inicial' => 5000,
            ])
            ->assertSessionHasErrors('caja');

        $this->assertSame(1, AperturaCaja::count());
    }

    public function test_apertura_con_saldo_negativo_es_rechazada(): void
    {
        $this->actingAs($this->cajero)
            ->post(route('caja.abrir'), [
                'caja_id' => $this->caja->id,
                'saldo_inicial' => -1,
            ])
            ->assertSessionHasErrors('saldo_inicial');

        $this->assertSame(0, AperturaCaja::count());
    }

    public function test_cierre_con_arqueo_cuadrado_queda_aceptado(): void
    {
        $apertura = $this->abrirCajaDirecto(10000);

        $this->actingAs($this->cajero)
            ->post(route('caja.cerrar', $apertura), ['saldo_contado' => 10000])
            ->assertRedirect(route('caja.index'));

        $this->assertSame('cerrada', $apertura->fresh()->estado);
        $this->assertSame('aceptado', CierreCaja::firstOrFail()->estado);
    }

    public function test_cierre_con_diferencia_registra_la_diferencia(): void
    {
        $apertura = $this->abrirCajaDirecto(10000);

        $this->actingAs($this->cajero)
            ->post(route('caja.cerrar', $apertura), ['saldo_contado' => 9000]);

        $cierre = CierreCaja::firstOrFail();
        $this->assertSame('con_diferencia', $cierre->estado);
        $this->assertEqualsWithDelta(-1000.0, (float) $cierre->diferencia, 0.01);
    }

    public function test_cierre_repetido_no_duplica_el_cierre(): void
    {
        $apertura = $this->abrirCajaDirecto(10000);

        $this->actingAs($this->cajero)
            ->post(route('caja.cerrar', $apertura), ['saldo_contado' => 10000]);
        $this->actingAs($this->cajero)
            ->post(route('caja.cerrar', $apertura), ['saldo_contado' => 10000])
            ->assertSessionHas('error');

        $this->assertSame(1, CierreCaja::count());
    }

    // ------------------------------------------------------------ VENTA

    public function test_venta_descuenta_stock_y_registra_pago_y_movimiento_de_caja(): void
    {
        $apertura = $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(2))
            ->assertSessionHasNoErrors();

        $venta = Venta::firstOrFail();

        $this->assertSame(3, $this->stockActual());
        $this->assertSame($apertura->id, (int) $venta->apertura_caja_id);
        $this->assertEqualsWithDelta(2000.0, (float) $venta->subtotal, 0.01);
        $this->assertEqualsWithDelta(380.0, (float) $venta->impuesto, 0.01);
        $this->assertEqualsWithDelta((float) $venta->subtotal + (float) $venta->impuesto, (float) $venta->total, 0.01);
        $this->assertSame(1, Pago::where('venta_id', $venta->id)->count());
        $this->assertSame(1, MovimientoCaja::where('apertura_caja_id', $apertura->id)->where('tipo', 'venta')->count());
    }

    public function test_editar_precio_actualiza_las_ventas_nuevas_y_conserva_el_historial(): void
    {
        $this->abrirCajaDirecto();
        $precioAnterior = $this->producto->precios()->firstOrFail();
        $datos = $this->producto->only([
            'nombre', 'categoria_id', 'unidad_medida', 'condicion_almacenamiento',
            'stock_minimo', 'stock_critico',
        ]) + ['tipo_receta' => 'ninguna'];

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(1))
            ->assertSessionHasNoErrors();
        $ventaAnterior = Venta::firstOrFail();

        foreach ([2380, 3570, 3570] as $precio) {
            $this->put(route('productos.update', $this->producto), $datos + ['precio_base' => $precio])
                ->assertRedirect(route('productos.show', $this->producto))
                ->assertSessionHasNoErrors();
        }

        $this->post(route('ventas.store'), $this->datosVenta(1))
            ->assertSessionHasNoErrors();

        $this->assertSame(3570, (int) $this->producto->fresh()->precio_base);
        $this->assertSame(2, $this->producto->precios()->count());
        $this->assertSame('1190.00', $precioAnterior->fresh()->precio_venta);
        $this->assertTrue($precioAnterior->fresh()->vigente_hasta->isSameDay(today()->subDay()));
        $this->assertEquals(1190, $ventaAnterior->fresh()->total);
        $this->assertEquals(3570, Venta::latest('id')->firstOrFail()->total);
    }

    public function test_venta_sin_caja_abierta_es_rechazada_y_no_cambia_stock(): void
    {
        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(1))
            ->assertSessionHasErrors('caja');

        $this->assertSame(0, Venta::count());
        $this->assertSame(5, $this->stockActual());
    }

    public function test_venta_con_stock_insuficiente_no_cambia_stock(): void
    {
        $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(6))
            ->assertSessionHasErrors('detalles');

        $this->assertSame(0, Venta::count());
        $this->assertSame(5, $this->stockActual());
    }

    public function test_venta_de_la_ultima_unidad_deja_el_stock_en_cero(): void
    {
        $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(5))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $this->stockActual());
        $this->assertSame(1, Venta::count());
    }

    public function test_factura_exige_rut_y_razon_social(): void
    {
        $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(1, ['tipo_documento' => 'factura']))
            ->assertSessionHasErrors(['rut_empresa', 'razon_social']);

        $this->assertSame(0, Venta::count());
        $this->assertSame(5, $this->stockActual());
    }

    public function test_venta_sin_productos_es_rechazada(): void
    {
        $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), [
                'metodo_pago' => 'efectivo',
                'tipo_documento' => 'boleta',
                'detalles' => [],
            ])
            ->assertSessionHasErrors('detalles');

        $this->assertSame(0, Venta::count());
    }

    public function test_venta_con_cantidad_cero_es_rechazada(): void
    {
        $this->abrirCajaDirecto();

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(0))
            ->assertSessionHasErrors('detalles.0.cantidad');

        $this->assertSame(5, $this->stockActual());
    }

    public function test_lote_vencido_no_se_puede_vender(): void
    {
        $this->abrirCajaDirecto();
        Lote::query()->update(['fecha_vencimiento' => today()->subDay()]);

        $this->actingAs($this->cajero)
            ->post(route('ventas.store'), $this->datosVenta(1))
            ->assertSessionHasErrors('detalles');

        $this->assertSame(0, Venta::count());
        $this->assertSame(5, $this->stockActual());
    }
}
