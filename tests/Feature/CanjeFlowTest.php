<?php

namespace Tests\Feature;

use App\Models\Canje;
use App\Models\CanjeEntrega;
use App\Models\Categoria;
use App\Models\CorrellativoLibro;
use App\Models\LibroControlEstupefaciente;
use App\Models\Lote;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CanjeFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $operador;
    private User $quimico;
    private Producto $producto;
    private Lote $lote;
    private UbicacionAlmacen $bodega;
    private UbicacionAlmacen $canje;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $gestionarStock = Permission::findOrCreate('gestionar_stock', 'web');
        $autorizarLibro = Permission::findOrCreate('autorizar_libro_isp', 'web');
        $rolQuimico = Role::findOrCreate('Químico Farmacéutico', 'web');
        $rolQuimico->givePermissionTo([$gestionarStock, $autorizarLibro]);

        $this->operador = User::factory()->create();
        $this->operador->givePermissionTo($gestionarStock);
        $this->quimico = User::factory()->create();
        $this->quimico->assignRole($rolQuimico);

        $this->bodega = UbicacionAlmacen::create([
            'nombre' => 'Bodega Central',
            'tipo' => 'bodega',
            'activo' => true,
        ]);
        $this->canje = UbicacionAlmacen::create([
            'nombre' => 'Zona de Canje',
            'tipo' => 'canje',
            'activo' => true,
        ]);

        $categoria = Categoria::create(['nombre' => 'Prueba', 'activo' => true]);
        $this->producto = Producto::create([
            'codigo_interno' => 'CANJE-001',
            'nombre' => 'Producto de canje',
            'precio_base' => 1000,
            'categoria_id' => $categoria->id,
            'unidad_medida' => 'unidad',
            'condicion_almacenamiento' => 'ambiente',
            'es_controlado' => true,
            'stock_minimo' => 1,
            'stock_critico' => 0,
            'activo' => true,
        ]);
        $this->lote = Lote::create([
            'producto_id' => $this->producto->id,
            'numero_lote' => 'ORIGINAL-001',
            'fecha_fabricacion' => today()->subYear(),
            'fecha_vencimiento' => today()->addDays(15),
            'cantidad_inicial' => 10,
            'estado' => 'activo',
        ]);

        $entradaInicial = Stock::registrarEntrada(
            $this->producto,
            $this->lote,
            $this->bodega,
            10,
            $this->operador->id,
            'Ingreso de prueba',
            'Stock inicial'
        );
        LibroControlEstupefaciente::registrarEntrada(
            $this->producto,
            $this->lote,
            $entradaInicial,
            $this->quimico->id,
            'Ingreso inicial de prueba'
        );
    }

    public function test_request_rejection_and_over_request_do_not_change_stock(): void
    {
        $this->actingAs($this->operador)
            ->from(route('inventario.proximos-vencer'))
            ->post(route('inventario.canjes.store', $this->lote), $this->requestData(11))
            ->assertSessionHasErrors('cantidad_solicitada');

        $this->assertSame(10, (int) Stock::where('ubicacion_id', $this->bodega->id)->value('cantidad'));
        $this->assertDatabaseCount('canjes', 0);

        $this->actingAs($this->operador)
            ->post(route('inventario.canjes.store', $this->lote), $this->requestData(5))
            ->assertSessionHasNoErrors();

        $canje = Canje::firstOrFail();
        $this->assertSame(10, (int) Stock::where('ubicacion_id', $this->bodega->id)->value('cantidad'));

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.rechazar', $canje), ['motivo_decision' => 'No autorizado para esta campaña.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('rechazado', $canje->fresh()->estado);
        $this->assertSame(10, (int) Stock::where('ubicacion_id', $this->bodega->id)->value('cantidad'));
        $this->assertDatabaseCount('movimientos_stock', 1);
    }

    public function test_approval_partial_deliveries_replacement_and_close_are_idempotent(): void
    {
        $this->actingAs($this->operador)
            ->post(route('inventario.canjes.store', $this->lote), $this->requestData(11))
            ->assertSessionHasErrors('cantidad_solicitada');
        $this->assertDatabaseCount('canjes', 0);

        $tokenSolicitud = (string) Str::uuid();
        $this->actingAs($this->operador)
            ->post(route('inventario.canjes.store', $this->lote), $this->requestData(5, $tokenSolicitud))
            ->assertSessionHasNoErrors();

        $canje = Canje::firstOrFail();
        $this->assertSame(10, (int) Stock::where('ubicacion_id', $this->bodega->id)->value('cantidad'));

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.aprobar', $canje), ['motivo_decision' => 'Aprobado por vencimiento próximo.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('aprobado', $canje->fresh()->estado);
        $this->assertSame(5, (int) Stock::where('ubicacion_id', $this->bodega->id)->value('cantidad'));
        $this->assertSame(5, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.aprobar', $canje), ['motivo_decision' => 'Segundo intento.'])
            ->assertSessionHasErrors('canje');
        $this->assertSame(2, MovimientoStock::where('referencia_documento', "Canje #{$canje->id}")->count());

        $proveedor = Proveedor::create([
            'rut' => '76123456-7',
            'nombre_razon_social' => 'Distribuidor de prueba',
            'activo' => true,
        ]);
        $tokenEntrega = (string) Str::uuid();
        $datosEntrega = [
            'cantidad_enviada' => 3,
            'proveedor_id' => $proveedor->id,
            'numero_documento' => 'GUIA-001',
            'idempotency_token' => $tokenEntrega,
        ];
        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.entregas.store', $canje), $datosEntrega)
            ->assertSessionHasNoErrors();
        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.entregas.store', $canje), $datosEntrega)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('canje_entregas', 1);
        $this->assertSame(2, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));
        $this->assertSame(3, (int) CanjeEntrega::firstOrFail()->cantidad_enviada);
        $this->assertSame(0, (int) CanjeEntrega::firstOrFail()->cantidad_aceptada);
        $this->assertSame(3, (int) $canje->fresh()->entregas()->sum('cantidad_enviada'));

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.cerrar', $canje))
            ->assertSessionHasErrors('canje');

        $entregaSinStock = [
            'cantidad_enviada' => 3,
            'proveedor_id' => $proveedor->id,
            'numero_documento' => 'GUIA-SIN-STOCK',
            'idempotency_token' => (string) Str::uuid(),
        ];
        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.entregas.store', $canje), $entregaSinStock)
            ->assertSessionHasErrors('cantidad_enviada');

        $this->assertDatabaseCount('canje_entregas', 1);
        $this->assertSame(4, MovimientoStock::count());
        $this->assertSame(2, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));

        $reintentoMismoFolio = $datosEntrega;
        $reintentoMismoFolio['idempotency_token'] = (string) Str::uuid();
        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.entregas.store', $canje), $reintentoMismoFolio)
            ->assertSessionHasErrors('numero_documento');

        $entregaOriginal = CanjeEntrega::firstOrFail();
        $this->respond($canje, $entregaOriginal, $proveedor, 1, 0, 'RESP-001', true);
        $this->assertSame(2, (int) $entregaOriginal->fresh()->cantidad_enviada
            - (int) $entregaOriginal->fresh()->cantidad_aceptada
            - (int) $entregaOriginal->fresh()->cantidad_rechazada);

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.cerrar', $canje))
            ->assertSessionHasErrors('canje');

        $this->respond($canje, $entregaOriginal, $proveedor, 1, 1, 'RESP-002');
        $this->assertSame(2, (int) $entregaOriginal->fresh()->cantidad_aceptada);
        $this->assertSame(1, (int) $entregaOriginal->fresh()->cantidad_rechazada);
        $this->assertDatabaseCount('canje_respuestas_entrega', 2);
        $this->assertSame(2, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.respuestas.store', [$canje, $entregaOriginal]), [
                'cantidad_aceptada' => 2,
                'cantidad_rechazada' => 1,
                'proveedor_id' => $proveedor->id,
                'numero_documento' => 'RESP-001',
                'idempotency_token' => (string) Str::uuid(),
            ])
            ->assertSessionHasErrors('numero_documento');

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.cerrar', $canje))
            ->assertSessionHasErrors('canje');

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.devoluciones.store', [$canje, $entregaOriginal]), [
                'cantidad' => 2,
                'proveedor_id' => $proveedor->id,
                'numero_documento' => 'DEV-EXCESIVA',
                'idempotency_token' => (string) Str::uuid(),
            ])
            ->assertSessionHasErrors('cantidad');
        $this->assertSame(2, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));

        $this->returnRejected($canje, $entregaOriginal, $proveedor, 1, 'DEV-001');
        $this->assertSame(3, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));
        $this->assertSame(5, (int) Stock::where('ubicacion_id', $this->bodega->id)->value('cantidad'));

        $entregaReenvio = $this->deliver($canje, $proveedor, 3, 'GUIA-002');
        $this->respond($canje, $entregaReenvio, $proveedor, 3, 0, 'RESP-002');
        $this->assertSame(0, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.cerrar', $canje))
            ->assertSessionHasErrors('canje');

        $loteExistente = Lote::create([
            'producto_id' => $this->producto->id,
            'numero_lote' => 'REPOSICION-001',
            'fecha_fabricacion' => null,
            'fecha_vencimiento' => today()->addYear(),
            'cantidad_inicial' => 0,
            'estado' => 'agotado',
        ]);
        $this->receiveReplacement($canje, $proveedor, 2, 'REPOSICION-001', true, 'FACTURA-MULTILOTE');
        $this->assertDatabaseCount('canje_reposiciones', 1);
        $this->assertSame(today()->subMonth()->toDateString(), $loteExistente->fresh()->fecha_fabricacion->toDateString());
        $this->receiveReplacement($canje, $proveedor, 1, 'REPOSICION-002', false, 'FACTURA-MULTILOTE');
        $this->assertDatabaseCount('canje_reposiciones', 2);
        $this->receiveReplacement($canje, $proveedor, 2, 'REPOSICION-003');

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.cerrar', $canje))
            ->assertSessionHasNoErrors();

        $this->assertSame('completado', $canje->fresh()->estado);
        $this->assertSame(0, (int) Stock::where('ubicacion_id', $this->canje->id)->value('cantidad'));
        $this->assertSame(5, (int) Stock::where('producto_id', $this->producto->id)
            ->where('lote_id', '!=', $this->lote->id)
            ->sum('cantidad'));
        $this->assertSame(9, LibroControlEstupefaciente::count());
        $this->assertSame(9, MovimientoStock::count());
    }

    public function test_bodeguero_can_reach_expiry_screen_without_inventory_read_permission(): void
    {
        $rolBodeguero = Role::findOrCreate('Bodeguero', 'web');
        $rolBodeguero->givePermissionTo(Permission::findOrCreate('gestionar_stock', 'web'));
        $bodeguero = User::factory()->create();
        $bodeguero->assignRole($rolBodeguero);

        $this->actingAs($bodeguero)
            ->get(route('inventario.proximos-vencer', ['dias' => 30]))
            ->assertOk();

        $this->actingAs($bodeguero)
            ->get(route('inventario.index'))
            ->assertForbidden();
    }

    private function requestData(int $cantidad, ?string $token = null): array
    {
        return [
            'ubicacion_origen_id' => $this->bodega->id,
            'cantidad_solicitada' => $cantidad,
            'motivo_solicitud' => 'Próximo vencimiento.',
            'dias' => 30,
            'idempotency_token' => $token ?? (string) Str::uuid(),
        ];
    }

    private function deliver(Canje $canje, Proveedor $proveedor, int $cantidadEnviada, string $documento): CanjeEntrega
    {
        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.entregas.store', $canje), [
                'cantidad_enviada' => $cantidadEnviada,
                'proveedor_id' => $proveedor->id,
                'numero_documento' => $documento,
                'idempotency_token' => (string) Str::uuid(),
            ])
            ->assertSessionHasNoErrors();

        return CanjeEntrega::latest('id')->firstOrFail();
    }

    private function respond(
        Canje $canje,
        CanjeEntrega $entrega,
        Proveedor $proveedor,
        int $aceptada,
        int $rechazada,
        string $documento,
        bool $retry = false
    ): void {
        $datos = [
            'cantidad_aceptada' => $aceptada,
            'cantidad_rechazada' => $rechazada,
            'proveedor_id' => $proveedor->id,
            'numero_documento' => $documento,
            'idempotency_token' => (string) Str::uuid(),
        ];

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.respuestas.store', [$canje, $entrega]), $datos)
            ->assertSessionHasNoErrors();

        if ($retry) {
            $datos['idempotency_token'] = (string) Str::uuid();
            $this->actingAs($this->quimico)
                ->post(route('inventario.canjes.respuestas.store', [$canje, $entrega]), $datos)
                ->assertSessionHasErrors('numero_documento');
        }
    }

    private function returnRejected(
        Canje $canje,
        CanjeEntrega $entrega,
        Proveedor $proveedor,
        int $cantidad,
        string $documento
    ): void {
        $datos = [
            'cantidad' => $cantidad,
            'proveedor_id' => $proveedor->id,
            'numero_documento' => $documento,
            'idempotency_token' => (string) Str::uuid(),
        ];

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.devoluciones.store', [$canje, $entrega]), $datos)
            ->assertSessionHasNoErrors();

        $datos['idempotency_token'] = (string) Str::uuid();
        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.devoluciones.store', [$canje, $entrega]), $datos)
            ->assertSessionHasErrors('numero_documento');
    }

    private function receiveReplacement(
        Canje $canje,
        Proveedor $proveedor,
        int $cantidad,
        string $numeroLote,
        bool $retry = false,
        ?string $documento = null
    ): void
    {
        $datos = [
            'cantidad' => $cantidad,
            'numero_lote' => $numeroLote,
            'fecha_fabricacion' => today()->subMonth()->toDateString(),
            'fecha_vencimiento' => today()->addYear()->toDateString(),
            'proveedor_id' => $proveedor->id,
            'numero_documento' => $documento ?? 'RECEPCION-' . $numeroLote,
            'idempotency_token' => (string) Str::uuid(),
        ];

        $this->actingAs($this->quimico)
            ->post(route('inventario.canjes.reposiciones.store', $canje), $datos)
            ->assertSessionHasNoErrors();

        if ($retry) {
            $datos['idempotency_token'] = (string) Str::uuid();
            $this->actingAs($this->quimico)
                ->post(route('inventario.canjes.reposiciones.store', $canje), $datos)
                ->assertSessionHasErrors('numero_documento');
        }
    }
}