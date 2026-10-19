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
                'recepcion' => 'Esta orden ya fue recibida, no tiene cantidades pendientes o tiene una recepción pendiente de revisión.'
            ]);
    }

    $validated = $request->validate([
        'numero_comprobante' => 'nullable|string|max:100',
        'observaciones' => 'nullable|string',

        'detalles' => 'required|array',
        'detalles.*.cantidad_recibida' => 'nullable|integer|min:0',
        'detalles.*.numero_lote' => 'nullable|string|max:100',
        'detalles.*.fecha_fabricacion' => 'nullable|date|before_or_equal:today',
        'detalles.*.fecha_vencimiento' => 'nullable|date|after_or_equal:today',
    ]);

    try {
        $recepcion = DB::transaction(function () use ($validated, $orden) {
            $orden = OrdenCompra::whereKey($orden->id)->lockForUpdate()->firstOrFail();

            if (!$orden->puedeRecibirMercaderia()) {
                throw new \Exception('Esta orden ya fue recibida o tiene una recepción pendiente de revisión.');
            }

            $orden->load('detalles.producto');

            $recepcion = RecepcionMercaderia::create([
                'orden_compra_id' => $orden->id,
                'usuario_id' => auth()->id(),
                'fecha_recepcion' => now(),
                'numero_comprobante' => $validated['numero_comprobante'] ?? null,
                'estado' => 'pendiente',
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $totalRecibido = 0;

            foreach ($validated['detalles'] as $detalleId => $datos) {

                $detalleOrden = $orden->detalles
                    ->firstWhere('id', (int) $detalleId);

                if (!$detalleOrden) {
                    throw new \Exception(
                        'Uno de los productos no pertenece a esta orden.'
                    );
                }

                $cantidad = (int) ($datos['cantidad_recibida'] ?? 0);

                if ($cantidad <= 0) {
                    continue;
                }

                $pendiente = $detalleOrden->cantidad_solicitada
                    - $detalleOrden->cantidad_recibida;

                if ($cantidad > $pendiente) {
                    throw new \Exception(
                        "La cantidad recibida de {$detalleOrden->producto->nombre} supera las {$pendiente} unidades pendientes."
                    );
                }

                if (empty($datos['numero_lote'])) {
                    throw new \Exception(
                        "Debes ingresar el número de lote de {$detalleOrden->producto->nombre}."
                    );
                }

                if (empty($datos['fecha_vencimiento'])) {
                    throw new \Exception(
                        "Debes ingresar la fecha de vencimiento de {$detalleOrden->producto->nombre}."
                    );
                }

                if (
                    !empty($datos['fecha_fabricacion']) &&
                    Carbon::parse($datos['fecha_fabricacion'])
                        ->gt(Carbon::parse($datos['fecha_vencimiento']))
                ) {
                    throw new \Exception(
                        "La fecha de fabricación de {$detalleOrden->producto->nombre} no puede ser posterior al vencimiento."
                    );
                }

                RecepcionMercaderiaDetalle::create([
                    'recepcion_mercaderia_id' => $recepcion->id,
                    'orden_compra_detalle_id' => $detalleOrden->id,
                    'producto_id' => $detalleOrden->producto_id,

                    // Todavía no existe un lote aprobado
                    'lote_id' => null,

                    'cantidad_recibida' => $cantidad,
                    'precio_unitario' => $detalleOrden->precio_unitario,

                    'numero_lote' => $datos['numero_lote'],
                    'fecha_fabricacion' => $datos['fecha_fabricacion'] ?? null,
                    'fecha_vencimiento' => $datos['fecha_vencimiento'],
                ]);

                $totalRecibido += $cantidad;
            }

            if ($totalRecibido <= 0) {
                throw new \Exception(
                    'Debes registrar al menos un producto recibido.'
                );
            }

            return $recepcion;
        });

    } catch (\Throwable $e) {

        return back()
            ->withInput()
            ->withErrors(['recepcion' => $e->getMessage()]);
    }

    return redirect()
        ->route('compras.recepciones.show', $recepcion)
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
                'recepcion' => 'Esta recepción ya fue revisada.'
            ]);
        }

        if ($recepcion->usuario_id === $usuario->id) {
            return back()->withErrors([
                'recepcion' => 'No puedes aprobar una recepción registrada por ti mismo.'
            ]);
        }

        $tieneControlados = $recepcion->detalles
            ->contains(fn($detalle) => $detalle->producto->es_controlado);

        if ($tieneControlados) {
            if (!$usuario->hasRole('Químico Farmacéutico')) {
                abort(403, 'Los medicamentos controlados requieren aprobación de un Químico Farmacéutico.');
            }
        } elseif (!$usuario->hasAnyRole([
            'Técnico Farmacéutico',
            'Químico Farmacéutico'
        ])) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($recepcion, $usuario) {

                $recepcionActual = RecepcionMercaderia::whereKey($recepcion->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($recepcionActual->estado !== 'pendiente') {
                    throw new \Exception('Esta recepción ya fue revisada.');
                }

                $recepcionActual->load([
                    'detalles.producto',
                    'detalles.ordenCompraDetalle',
                    'ordenCompra.detalles',
                ]);

                $orden = $recepcionActual->ordenCompra;

                $ubicaciones = UbicacionAlmacen::where('activo', true)
                    ->whereIn('tipo', ['bodega', 'frigorifica'])
                    ->orderBy('id')
                    ->get()
                    ->unique('tipo')
                    ->keyBy('tipo');

                foreach ($recepcionActual->detalles as $detalle) {

                    $tipoUbicacion = $detalle->producto->condicion_almacenamiento === 'refrigerado'
                        ? 'frigorifica'
                        : 'bodega';
                    $ubicacion = $ubicaciones->get($tipoUbicacion);

                    if (!$ubicacion) {
                        throw new \Exception(
                            $tipoUbicacion === 'frigorifica'
                                ? 'No existe una ubicación frigorífica activa configurada para almacenar este producto.'
                                : 'No existe una bodega activa configurada para almacenar este producto.'
                        );
                    }

                    $detalleOrden = $detalle->ordenCompraDetalle;

                    if (!$detalleOrden) {
                        throw new \Exception(
                            "No se encontró el detalle de la orden para {$detalle->producto->nombre}."
                        );
                    }

                    $pendiente = $detalleOrden->cantidad_solicitada
                        - $detalleOrden->cantidad_recibida;

                    if ($detalle->cantidad_recibida > $pendiente) {
                        throw new \Exception(
                            "La cantidad recibida de {$detalle->producto->nombre} supera las unidades pendientes."
                        );
                    }

                    $lote = Lote::where(
                        'numero_lote',
                        $detalle->numero_lote
                    )->lockForUpdate()->first();

                    if ($lote) {

                        if ($lote->producto_id !== $detalle->producto_id) {
                            throw new \Exception(
                                "El lote {$detalle->numero_lote} ya pertenece a otro producto."
                            );
                        }

                        if (
                            $lote->fecha_vencimiento->format('Y-m-d')
                            !== $detalle->fecha_vencimiento->format('Y-m-d')
                        ) {
                            throw new \Exception(
                                "El lote {$detalle->numero_lote} ya existe con otra fecha de vencimiento."
                            );
                        }

                        $lote->increment(
                            'cantidad_inicial',
                            $detalle->cantidad_recibida
                        );

                        $lote->update([
                            'estado' => 'activo'
                        ]);

                    } else {

                        $lote = Lote::create([
                            'producto_id' => $detalle->producto_id,
                            'numero_lote' => $detalle->numero_lote,
                            'fecha_fabricacion' => $detalle->fecha_fabricacion,
                            'fecha_vencimiento' => $detalle->fecha_vencimiento,
                            'cantidad_inicial' => $detalle->cantidad_recibida,
                            'estado' => 'activo',
                            'observaciones' => 'Creado desde recepción de mercadería aprobada.',
                        ]);
                    }

                    Stock::registrarEntrada(
                        $detalle->producto,
                        $lote,
                        $ubicacion,
                        $detalle->cantidad_recibida,
                        $usuario->id,
                        "Recepción #{$recepcionActual->id} - {$orden->numero_orden}",
                        'Recepción de mercadería aprobada'
                    );

                    $detalle->update([
                        'lote_id' => $lote->id
                    ]);

                    $nuevaCantidad =
                        $detalleOrden->cantidad_recibida
                        + $detalle->cantidad_recibida;

                    $detalleOrden->update([
                        'cantidad_recibida' => $nuevaCantidad,
                        'estado_linea' =>
                            $nuevaCantidad >= $detalleOrden->cantidad_solicitada
                                ? 'recibida'
                                : 'parcial',
                    ]);
                }

                $orden->refresh();
                $orden->load('detalles');

                $ordenCompleta = $orden->detalles->every(function ($detalle) {
                    return in_array(
                        $detalle->estado_linea,
                        ['recibida', 'cancelada']
                    );
                });

                $orden->update([
                    'estado' => $ordenCompleta
                        ? 'recibida'
                        : 'parcial',
                ]);

                $recepcionActual->update([
                    'estado' => 'aprobada',
                    'tipo_recepcion' => $ordenCompleta
                        ? 'completa'
                        : 'parcial',
                    'revisado_por' => $usuario->id,
                    'fecha_revision' => now(),
                    'motivo_rechazo' => null,
                ]);
            });

        } catch (\Throwable $e) {

            return back()->withErrors([
                'recepcion' => $e->getMessage()
            ]);
        }

        return redirect()
            ->route('compras.recepciones.show', $recepcion)
            ->with(
                'success',
                'Recepción aprobada. El stock fue ingresado en las ubicaciones correspondientes a cada producto.'
            );
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

        return redirect()
            ->route('compras.recepciones.show', $recepcion)
            ->with('success', 'Recepción rechazada correctamente.');
    }
    
}
