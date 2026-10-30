<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\RecepcionMercaderia;
use App\Models\RecepcionMercaderiaDetalle;
use App\Models\Lote;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\OrdenCompraDetalle;

class RecepcionMercaderiaController extends Controller
{
    public function index()
    {
        $recepciones = RecepcionMercaderia::with([
                'ordenCompra.proveedor',
                'usuario'
            ])
            ->withCount('detalles')
            ->latest('fecha_recepcion')
            ->paginate(15);

        return view('compras.recepciones.index', compact('recepciones'));
    }

    public function create(OrdenCompra $orden)
    {
        if (!$orden->puedeRecibirMercaderia()) {
            return redirect()
                ->route('compras.show', $orden)
                ->withErrors(['recepcion' => 'Esta orden ya fue recibida, no tiene cantidades pendientes o tiene una recepción pendiente de revisión.']);
        }

        $orden->load(['proveedor', 'detalles.producto']);

        $ubicacionesAlmacenamiento = UbicacionAlmacen::where('activo', true)
            ->whereIn('tipo', ['bodega', 'frigorifica'])
            ->orderBy('id')
            ->get(['tipo', 'nombre'])
            ->unique('tipo')
            ->pluck('nombre', 'tipo');

        return view('compras.recepciones.create', compact('orden', 'ubicacionesAlmacenamiento'));
    }

    public function store(Request $request, OrdenCompra $orden)
{
    if (!$orden->puedeRecibirMercaderia()) {
        return redirect()
            ->route('compras.show', $orden)
            ->withErrors([
                'recepcion' =>
                    'Esta orden ya fue recibida, no tiene cantidades pendientes o tiene una recepción pendiente de revisión.'
            ]);
    }

    $validated = $request->validate([
        'numero_comprobante' => 'nullable|string|max:100',
        'observaciones' => 'nullable|string|max:2000',

        'detalles' => 'required|array',

        'detalles.*.lotes' => 'required|array',

        'detalles.*.lotes.*.numero_lote' =>
            'nullable|string|max:100',

        'detalles.*.lotes.*.cantidad_recibida' =>
            'nullable|integer|min:0',

        'detalles.*.lotes.*.cantidad_rechazada' =>
            'nullable|integer|min:0',

        'detalles.*.lotes.*.fecha_fabricacion' =>
            'nullable|date|before_or_equal:today',

        'detalles.*.lotes.*.fecha_vencimiento' =>
            'nullable|date|after_or_equal:today',

        'detalles.*.lotes.*.observacion' =>
            'nullable|string|max:1000',
    ]);

    try {

        $recepcion = DB::transaction(function () use (
            $validated,
            $orden
        ) {

            $orden = OrdenCompra::whereKey($orden->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$orden->puedeRecibirMercaderia()) {
                throw new \Exception(
                    'Esta orden ya fue recibida o tiene una recepción pendiente de revisión.'
                );
            }

            $orden->load('detalles.producto');

            $recepcion = RecepcionMercaderia::create([
                'orden_compra_id' => $orden->id,
                'usuario_id' => auth()->id(),
                'fecha_recepcion' => now(),
                'numero_comprobante' =>
                    $validated['numero_comprobante'] ?? null,
                'estado' => 'pendiente',
                'observaciones' =>
                    $validated['observaciones'] ?? null,
            ]);

            $totalFisicamenteRecibido = 0;

            foreach (
                $validated['detalles']
                as $detalleId => $datosProducto
            ) {

                $detalleOrden = $orden->detalles
                    ->firstWhere('id', (int) $detalleId);

                if (!$detalleOrden) {
                    throw new \Exception(
                        'Uno de los productos no pertenece a esta orden.'
                    );
                }

                $pendiente =
                    (int) $detalleOrden->cantidad_solicitada
                    - (int) $detalleOrden->cantidad_recibida;

                if ($pendiente <= 0) {
                    continue;
                }

                $totalRecibidoProducto = 0;
                $lotesIngresados = [];

                foreach (
                    $datosProducto['lotes'] ?? []
                    as $datosLote
                ) {

                    $cantidadRecibida =
                        (int) (
                            $datosLote['cantidad_recibida']
                            ?? 0
                        );

                    if ($cantidadRecibida <= 0) {
                        continue;
                    }

                    $cantidadRechazada =
                        (int) (
                            $datosLote['cantidad_rechazada']
                            ?? 0
                        );

                    if (
                        $cantidadRechazada >
                        $cantidadRecibida
                    ) {
                        throw new \Exception(
                            "La cantidad rechazada de {$detalleOrden->producto->nombre} no puede superar la cantidad recibida."
                        );
                    }

                    $cantidadAceptada =
                        $cantidadRecibida
                        - $cantidadRechazada;

                    $numeroLote = trim(
                        (string) (
                            $datosLote['numero_lote']
                            ?? ''
                        )
                    );

                    if ($numeroLote === '') {
                        throw new \Exception(
                            "Debes ingresar el lote o serie de {$detalleOrden->producto->nombre}."
                        );
                    }

                    $claveLote =
                        mb_strtoupper($numeroLote);

                    if (
                        isset(
                            $lotesIngresados[$claveLote]
                        )
                    ) {
                        throw new \Exception(
                            "El lote {$numeroLote} está repetido para {$detalleOrden->producto->nombre}. Agrupa sus unidades en una sola fila."
                        );
                    }

                    $lotesIngresados[$claveLote] = true;

                    $fechaFabricacion =
                        $datosLote['fecha_fabricacion']
                        ?? null;

                    $fechaVencimiento =
                        $datosLote['fecha_vencimiento']
                        ?? null;

                    if (empty($fechaFabricacion)) {
                        throw new \Exception(
                            "Debes ingresar la fecha de fabricación del lote {$numeroLote}."
                        );
                    }

                    if (empty($fechaVencimiento)) {
                        throw new \Exception(
                            "Debes ingresar la fecha de vencimiento del lote {$numeroLote}."
                        );
                    }

                    $fabricacion =
                        Carbon::parse(
                            $fechaFabricacion
                        );

                    $vencimiento =
                        Carbon::parse(
                            $fechaVencimiento
                        );

                    if (
                        $fabricacion->gte(
                            $vencimiento
                        )
                    ) {
                        throw new \Exception(
                            "La fecha de vencimiento del lote {$numeroLote} debe ser posterior a la fecha de fabricación."
                        );
                    }

                    $observacion = trim(
                        (string) (
                            $datosLote['observacion']
                            ?? ''
                        )
                    );

                    if (
                        $cantidadRechazada > 0
                        && $observacion === ''
                    ) {
                        throw new \Exception(
                            "Debes indicar el motivo del rechazo de las unidades del lote {$numeroLote}."
                        );
                    }

                    $totalRecibidoProducto +=
                        $cantidadRecibida;

                    if (
                        $totalRecibidoProducto >
                        $pendiente
                    ) {
                        throw new \Exception(
                            "La cantidad recibida de {$detalleOrden->producto->nombre} supera las {$pendiente} unidades pendientes de la orden."
                        );
                    }

                    RecepcionMercaderiaDetalle::create([
                        'recepcion_mercaderia_id' =>
                            $recepcion->id,

                        'orden_compra_detalle_id' =>
                            $detalleOrden->id,

                        'producto_id' =>
                            $detalleOrden->producto_id,

                        // El lote real se crea/reutiliza
                        // únicamente cuando se aprueba.
                        'lote_id' => null,

                        'cantidad_recibida' =>
                            $cantidadRecibida,

                        'cantidad_aceptada' =>
                            $cantidadAceptada,

                        'cantidad_rechazada' =>
                            $cantidadRechazada,

                        'precio_unitario' =>
                            $detalleOrden->precio_unitario,

                        'numero_lote' =>
                            $numeroLote,

                        'fecha_fabricacion' =>
                            $fechaFabricacion,

                        'fecha_vencimiento' =>
                            $fechaVencimiento,

                        'observacion' =>
                            $observacion !== ''
                                ? $observacion
                                : null,
                    ]);

                    $totalFisicamenteRecibido +=
                        $cantidadRecibida;
                }
            }

            if ($totalFisicamenteRecibido <= 0) {
                throw new \Exception(
                    'Debes registrar al menos un producto recibido.'
                );
            }

            return $recepcion;
        });

    } catch (\Throwable $e) {

        return back()
            ->withInput()
            ->withErrors([
                'recepcion' => $e->getMessage()
            ]);
    }

    return redirect()
        ->route(
            'compras.recepciones.show',
            $recepcion
        )
        ->with(
            'success',
            'Recepción registrada. Está pendiente de revisión y todavía no ha ingresado al inventario.'
        );
}

    public function show(RecepcionMercaderia $recepcion)
    {
        $recepcion->load([
            'ordenCompra.proveedor',
            'usuario',
            'revisadoPor',
            'detalles.producto',
            'detalles.lote',
        ]);

        $usuario = auth()->user();

        $tieneControlados = $recepcion->detalles
            ->contains(fn($detalle) => $detalle->producto->es_controlado);

        $puedeRevisar =
            $recepcion->estado === 'pendiente'
            && $recepcion->usuario_id !== $usuario->id
            && (
                $tieneControlados
                    ? $usuario->hasRole('Químico Farmacéutico')
                    : $usuario->hasAnyRole([
                        'Técnico Farmacéutico',
                        'Químico Farmacéutico'
                    ])
            );

        return view('compras.recepciones.show', compact('recepcion','tieneControlados','puedeRevisar'));}


    public function aprobar(RecepcionMercaderia $recepcion)
{
    $usuario = auth()->user();

    $recepcion->load([
        'detalles.producto',
        'detalles.ordenCompraDetalle',
        'ordenCompra.detalles',
    ]);

    if ($recepcion->estado !== 'pendiente') {
        return back()->withErrors([
            'recepcion' =>
                'Esta recepción ya fue revisada.'
        ]);
    }

    if (
        $recepcion->usuario_id ===
        $usuario->id
    ) {
        return back()->withErrors([
            'recepcion' =>
                'No puedes aprobar una recepción registrada por ti mismo.'
        ]);
    }

    /*
     * Mantengo tu autorización actual.
     * No estamos modificando roles en este cambio.
     */
    $tieneControlados =
        $recepcion->detalles->contains(
            fn ($detalle) =>
                $detalle->producto->es_controlado
        );

    if ($tieneControlados) {

        if (
            !$usuario->hasRole(
                'Químico Farmacéutico'
            )
        ) {
            abort(
                403,
                'Los medicamentos controlados requieren aprobación de un Químico Farmacéutico.'
            );
        }

    } elseif (
        !$usuario->hasAnyRole([
            'Técnico Farmacéutico',
            'Químico Farmacéutico',
        ])
    ) {
        abort(403);
    }

    try {

        DB::transaction(function () use (
            $recepcion,
            $usuario
        ) {

            $recepcionActual =
                RecepcionMercaderia::whereKey(
                    $recepcion->id
                )
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $recepcionActual->estado
                !== 'pendiente'
            ) {
                throw new \Exception(
                    'Esta recepción ya fue revisada.'
                );
            }

            $recepcionActual->load([
                'detalles.producto',
                'detalles.ordenCompraDetalle',
            ]);

            $orden = OrdenCompra::whereKey(
                    $recepcionActual->orden_compra_id
                )
                ->lockForUpdate()
                ->firstOrFail();

            $ubicaciones =
                UbicacionAlmacen::where(
                    'activo',
                    true
                )
                ->whereIn(
                    'tipo',
                    ['bodega', 'frigorifica']
                )
                ->orderBy('id')
                ->get()
                ->unique('tipo')
                ->keyBy('tipo');

            /*
             * Acumulamos por línea de orden.
             *
             * Ej:
             * Paracetamol:
             * lote A = 100
             * lote B = 50
             *
             * OrdenCompraDetalle se actualiza una vez:
             * +150
             */
            $aceptadasPorDetalleOrden = [];

            foreach (
                $recepcionActual->detalles
                as $detalle
            ) {

                $cantidadAceptada =
                    (int) $detalle->cantidad_aceptada;

                /*
                 * Si todo este lote fue rechazado,
                 * queda como trazabilidad, pero
                 * no entra al inventario.
                 */
                if ($cantidadAceptada <= 0) {
                    continue;
                }

                $tipoUbicacion =
                    $detalle->producto
                        ->condicion_almacenamiento
                        === 'refrigerado'
                            ? 'frigorifica'
                            : 'bodega';

                $ubicacion =
                    $ubicaciones->get(
                        $tipoUbicacion
                    );

                if (!$ubicacion) {

                    throw new \Exception(
                        $tipoUbicacion ===
                        'frigorifica'
                            ? 'No existe una ubicación frigorífica activa configurada para almacenar este producto.'
                            : 'No existe una bodega activa configurada para almacenar este producto.'
                    );
                }

                /*
                 * Un lote pertenece a un producto.
                 * No buscamos el número de lote
                 * globalmente.
                 */
                $lote = Lote::where(
                        'producto_id',
                        $detalle->producto_id
                    )
                    ->where(
                        'numero_lote',
                        $detalle->numero_lote
                    )
                    ->lockForUpdate()
                    ->first();

                if ($lote) {

                    if (
                        $lote->fecha_vencimiento
                            ->format('Y-m-d')
                        !==
                        $detalle->fecha_vencimiento
                            ->format('Y-m-d')
                    ) {
                        throw new \Exception(
                            "El lote {$detalle->numero_lote} ya existe para {$detalle->producto->nombre} con otra fecha de vencimiento."
                        );
                    }

                    if (
                        $lote->fecha_fabricacion
                        && $detalle->fecha_fabricacion
                        && $lote->fecha_fabricacion
                            ->format('Y-m-d')
                        !==
                        $detalle->fecha_fabricacion
                            ->format('Y-m-d')
                    ) {
                        throw new \Exception(
                            "El lote {$detalle->numero_lote} ya existe para {$detalle->producto->nombre} con otra fecha de fabricación."
                        );
                    }

                    $lote->increment(
                        'cantidad_inicial',
                        $cantidadAceptada
                    );

                    $lote->update([
                        'estado' => 'activo',
                    ]);

                } else {

                    $lote = Lote::create([
                        'producto_id' =>
                            $detalle->producto_id,

                        'numero_lote' =>
                            $detalle->numero_lote,

                        'fecha_fabricacion' =>
                            $detalle->fecha_fabricacion,

                        'fecha_vencimiento' =>
                            $detalle->fecha_vencimiento,

                        'cantidad_inicial' =>
                            $cantidadAceptada,

                        'estado' => 'activo',

                        'observaciones' =>
                            $detalle->observacion
                                ?: 'Creado desde recepción de mercadería aprobada.',
                    ]);
                }

                /*
                 * registrarEntrada ya es el flujo
                 * existente de ingreso a Stock /
                 * MovimientoStock.
                 */
                Stock::registrarEntrada(
                    $detalle->producto,
                    $lote,
                    $ubicacion,
                    $cantidadAceptada,
                    $usuario->id,
                    "Recepción #{$recepcionActual->id} - {$orden->numero_orden}",
                    'Recepción de mercadería aprobada'
                );

                $detalle->update([
                    'lote_id' => $lote->id,
                ]);

                $detalleOrdenId =
                    $detalle
                        ->orden_compra_detalle_id;

                $aceptadasPorDetalleOrden[
                    $detalleOrdenId
                ] =
                    (
                        $aceptadasPorDetalleOrden[
                            $detalleOrdenId
                        ] ?? 0
                    )
                    + $cantidadAceptada;
            }

            /*
             * Actualizamos cada línea de orden
             * una sola vez.
             */
            foreach (
                $aceptadasPorDetalleOrden
                as $detalleOrdenId =>
                    $cantidadAceptadaTotal
            ) {

                $detalleOrden =
                    OrdenCompraDetalle::whereKey(
                        $detalleOrdenId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $pendiente =
                    (int)
                        $detalleOrden
                            ->cantidad_solicitada
                    -
                    (int)
                        $detalleOrden
                            ->cantidad_recibida;

                if (
                    $cantidadAceptadaTotal >
                    $pendiente
                ) {
                    throw new \Exception(
                        "Las cantidades aceptadas superan las unidades pendientes de la orden."
                    );
                }

                $nuevaCantidad =
                    (int)
                        $detalleOrden
                            ->cantidad_recibida
                    +
                    $cantidadAceptadaTotal;

                $detalleOrden->update([
                    'cantidad_recibida' =>
                        $nuevaCantidad,

                    'estado_linea' =>
                        $nuevaCantidad >=
                        $detalleOrden
                            ->cantidad_solicitada
                            ? 'recibida'
                            : 'parcial',
                ]);
            }

            /*
             * Recalculamos estado de la orden.
             */
            $orden->refresh();
            $orden->load('detalles');

            $ordenCompleta =
                $orden->detalles->every(
                    function ($detalle) {
                        return in_array(
                            $detalle->estado_linea,
                            [
                                'recibida',
                                'cancelada'
                            ],
                            true
                        );
                    }
                );

            $totalAceptadoRecepcion =
                array_sum(
                    $aceptadasPorDetalleOrden
                );

            if ($ordenCompleta) {

                $nuevoEstadoOrden =
                    'recibida';

            } elseif (
                $totalAceptadoRecepcion > 0
            ) {

                $nuevoEstadoOrden =
                    'parcial';

            } else {

                /*
                 * Si se aprobaron 0 unidades
                 * porque todo llegó rechazado,
                 * no avanzamos artificialmente
                 * el estado de la orden.
                 */
                $nuevoEstadoOrden =
                    $orden->estado;
            }

            $orden->update([
                'estado' =>
                    $nuevoEstadoOrden,
            ]);

            $recepcionActual->update([
                'estado' => 'aprobada',

                'tipo_recepcion' =>
                    $ordenCompleta
                        ? 'completa'
                        : 'parcial',

                'revisado_por' =>
                    $usuario->id,

                'fecha_revision' =>
                    now(),

                'motivo_rechazo' =>
                    null,
            ]);
        });

    } catch (\Throwable $e) { return back()->withErrors([ 'recepcion' => $e->getMessage() ]);}

    return redirect() ->route( 'compras.recepciones.show', $recepcion )
        ->with( 'success', 'Recepción aprobada. Solo las cantidades aceptadas fueron ingresadas al inventario.');
}


    public function rechazar(Request $request, RecepcionMercaderia $recepcion)
    {
        $validated = $request->validate([
            'motivo_rechazo' => 'required|string|max:1000',
        ]);

        $usuario = auth()->user();

        $recepcion->load('detalles.producto');

        if ($recepcion->estado !== 'pendiente') {
            return back()->withErrors([
                'recepcion' => 'Esta recepción ya fue revisada.'
            ]);
        }

        if ($recepcion->usuario_id === $usuario->id) {
            return back()->withErrors([
                'recepcion' => 'No puedes rechazar una recepción registrada por ti mismo.'
            ]);
        }

        $tieneControlados = $recepcion->detalles
            ->contains(fn($detalle) => $detalle->producto->es_controlado);

        if ($tieneControlados) {

            if (!$usuario->hasRole('Químico Farmacéutico')) {
                abort(403);
            }

        } elseif (!$usuario->hasAnyRole([
            'Técnico Farmacéutico',
            'Químico Farmacéutico'
        ])) {
            abort(403);
        }

        $recepcion->update([
            'estado' => 'rechazada',
            'revisado_por' => $usuario->id,
            'fecha_revision' => now(),
            'motivo_rechazo' => $validated['motivo_rechazo'],
        ]);

        return redirect() ->route('compras.recepciones.show', $recepcion) ->with('success', 'Recepción rechazada correctamente.');}
}
