<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Lote;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\RecepcionMercaderia;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pruebas del flujo de compras: orden de compra, recepción de mercadería
 * (Bodeguero), aprobación (Químico Farmacéutico) y segregación de funciones.
 * Informe N3.
 */
class RecepcionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $quimico;
    private User $bodeguero;
    private User $otroBodeguero;
    private User $tecnico;
    private User $mixto;
    private Producto $producto;
    private OrdenCompra $orden;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolQuimico = Role::findOrCreate('Químico Farmacéutico', 'web');
        $rolBodeguero = Role::findOrCreate('Bodeguero', 'web');
        $rolTecnico = Role::findOrCreate('Técnico Farmacéutico', 'web');

        $this->quimico = User::factory()->create();
        $this->quimico->assignRole($rolQuimico);
        $this->bodeguero = User::factory()->create();
        $this->bodeguero->assignRole($rolBodeguero);
        $this->otroBodeguero = User::factory()->create();
        $this->otroBodeguero->assignRole($rolBodeguero);
        $this->tecnico = User::factory()->create();
        $this->tecnico->assignRole($rolTecnico);
        $this->mixto = User::factory()->create();
        $this->mixto->assignRole([$rolQuimico, $rolBodeguero]);

        UbicacionAlmacen::create(['nombre' => 'Bodega Central', 'tipo' => 'bodega', 'activo' => true]);

        $proveedor = Proveedor::create([
            'rut' => '76123456-7',
            'nombre_razon_social' => 'Distribuidor de prueba',
            'activo' => true,
        ]);

        $categoria = Categoria::create(['nombre' => 'Prueba', 'activo' => true]);
        $this->producto = Producto::create([
            'codigo_interno' => 'COMPRA-001',
            'nombre' => 'Producto de compra',
            'precio_base' => 1000,
            'categoria_id' => $categoria->id,
            'unidad_medida' => 'unidad',
            'condicion_almacenamiento' => 'ambiente',
            'es_controlado' => false,
            'stock_minimo' => 1,
            'stock_critico' => 0,
            'activo' => true,
        ]);

        $this->actingAs($this->quimico)
            ->post(route('compras.store'), [
                'proveedor_id' => $proveedor->id,
                'detalles' => [[
                    'producto_id' => $this->producto->id,
                    'cantidad_solicitada' => 10,
                    'precio_unitario' => 500,
                ]],
            ])
            ->assertSessionHasNoErrors();

        $this->orden = OrdenCompra::firstOrFail();
    }

    private function datosRecepcion(array $lote = []): array
    {
        $detalleId = $this->orden->detalles()->firstOrFail()->id;

        return [
            'numero_comprobante' => 'GD-001',
            'detalles' => [
                $detalleId => [
                    'lotes' => [
                        array_merge([
                            'numero_lote' => 'LOTE-REC-001',
                            'cantidad_recibida' => 10,
                            'cantidad_rechazada' => 0,
                            'fecha_fabricacion' => today()->subMonth()->toDateString(),
                            'fecha_vencimiento' => today()->addYear()->toDateString(),
                        ], $lote),
                    ],
                ],
            ],
        ];
    }

    private function registrar(User $usuario, ?array $datos = null)
    {
        return $this->actingAs($usuario)
            ->post(route('compras.recepciones.store', $this->orden), $datos ?? $this->datosRecepcion());
    }

    private function stockProducto(): int
    {
        return (int) Stock::where('producto_id', $this->producto->id)->sum('cantidad');
    }

    public function test_bodeguero_registra_y_quimico_aprueba_la_recepcion_y_entra_el_stock(): void
    {
        $this->registrar($this->bodeguero)->assertSessionHasNoErrors();

        $recepcion = RecepcionMercaderia::firstOrFail();
        $this->assertSame('pendiente', $recepcion->estado);
        $this->assertSame(0, $this->stockProducto(), 'El stock no debe entrar antes de la aprobación.');

        $this->actingAs($this->quimico)
            ->post(route('compras.recepciones.aprobar', $recepcion))
            ->assertSessionHasNoErrors();

        $this->assertSame('aprobada', $recepcion->fresh()->estado);
        $this->assertSame(10, $this->stockProducto());
        $this->assertTrue(Lote::where('numero_lote', 'LOTE-REC-001')->exists());
        $this->assertSame(10, (int) $this->orden->detalles()->first()->fresh()->cantidad_recibida);
    }

    public function test_aprobar_dos_veces_no_duplica_el_stock(): void
    {
        $this->registrar($this->bodeguero);
        $recepcion = RecepcionMercaderia::firstOrFail();

        $this->actingAs($this->quimico)->post(route('compras.recepciones.aprobar', $recepcion));
        $this->actingAs($this->quimico)
            ->post(route('compras.recepciones.aprobar', $recepcion))
            ->assertSessionHasErrors('recepcion');

        $this->assertSame(10, $this->stockProducto());
    }

    public function test_no_se_puede_aprobar_la_recepcion_registrada_por_uno_mismo(): void
    {
        $this->registrar($this->mixto)->assertSessionHasNoErrors();
        $recepcion = RecepcionMercaderia::firstOrFail();

        $this->actingAs($this->mixto)
            ->post(route('compras.recepciones.aprobar', $recepcion))
            ->assertSessionHasErrors('recepcion');

        $this->assertSame('pendiente', $recepcion->fresh()->estado);
        $this->assertSame(0, $this->stockProducto());
    }

    public function test_bodeguero_no_puede_aprobar_aunque_la_ruta_lo_deja_pasar(): void
    {
        // Documenta H29: la ruta admite Químico|Bodeguero, pero el controlador
        // solo autoriza Químico o Técnico; el Bodeguero termina en 403.
        $this->registrar($this->bodeguero);
        $recepcion = RecepcionMercaderia::firstOrFail();

        $this->actingAs($this->otroBodeguero)
            ->post(route('compras.recepciones.aprobar', $recepcion))
            ->assertForbidden();

        $this->assertSame('pendiente', $recepcion->fresh()->estado);
    }

    public function test_tecnico_no_llega_a_la_aprobacion_por_la_regla_de_la_ruta(): void
    {
        // Documenta H29: el controlador permitiría al Técnico, pero la ruta lo excluye.
        $this->registrar($this->bodeguero);
        $recepcion = RecepcionMercaderia::firstOrFail();

        $this->actingAs($this->tecnico)
            ->post(route('compras.recepciones.aprobar', $recepcion))
            ->assertForbidden();
    }

    public function test_quimico_no_puede_registrar_recepciones(): void
    {
        $this->registrar($this->quimico)->assertForbidden();

        $this->assertSame(0, RecepcionMercaderia::count());
    }

    public function test_no_se_acepta_una_segunda_recepcion_mientras_hay_una_pendiente(): void
    {
        $this->registrar($this->bodeguero)->assertSessionHasNoErrors();

        $this->registrar($this->bodeguero, $this->datosRecepcion(['numero_lote' => 'LOTE-REC-002']))
            ->assertSessionHasErrors('recepcion');

        $this->assertSame(1, RecepcionMercaderia::count());
    }

    public function test_unidades_rechazadas_exigen_el_motivo(): void
    {
        $this->registrar($this->bodeguero, $this->datosRecepcion([
            'cantidad_rechazada' => 2,
            'observacion' => '',
        ]))->assertSessionHasErrors('recepcion');

        $this->assertSame(0, RecepcionMercaderia::count());
    }

    public function test_rechazadas_no_pueden_superar_las_recibidas(): void
    {
        $this->registrar($this->bodeguero, $this->datosRecepcion([
            'cantidad_recibida' => 5,
            'cantidad_rechazada' => 6,
            'observacion' => 'Envases dañados',
        ]))->assertSessionHasErrors('recepcion');

        $this->assertSame(0, RecepcionMercaderia::count());
    }

    public function test_lote_sin_numero_es_rechazado(): void
    {
        $this->registrar($this->bodeguero, $this->datosRecepcion(['numero_lote' => '']))
            ->assertSessionHasErrors('recepcion');

        $this->assertSame(0, RecepcionMercaderia::count());
    }

    public function test_fecha_de_vencimiento_vencida_es_rechazada(): void
    {
        $this->registrar($this->bodeguero, $this->datosRecepcion([
            'fecha_vencimiento' => today()->subDay()->toDateString(),
        ]))->assertSessionHasErrors();

        $this->assertSame(0, RecepcionMercaderia::count());
    }
}
