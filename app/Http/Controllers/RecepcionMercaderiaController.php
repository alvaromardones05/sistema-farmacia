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
        if (in_array($orden->estado, ['recibida', 'anulada'])) {
            return redirect()
                ->route('compras.show', $orden)
                ->withErrors(['recepcion' => 'Esta orden ya no puede recibir mercadería.']);
        }

        $orden->load(['proveedor', 'detalles.producto']);

        $bodega = UbicacionAlmacen::where('tipo', 'bodega')
            ->where('activo', true)
            ->first();

        if (!$bodega) {
            return redirect()
                ->route('compras.show', $orden)
                ->withErrors(['recepcion' => 'No existe una ubicación activa de tipo Bodega.']);
        }

        return view('compras.recepciones.create', compact('orden', 'bodega'));
    }

    public function store(Request $request, OrdenCompra $orden)
    {
        if (in_array($orden->estado, ['recibida', 'anulada'])) {
            return redirect()
                ->route('compras.show', $orden)
                ->withErrors(['recepcion' => 'Esta orden ya no puede recibir mercadería.']);
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

        $bodega = UbicacionAlmacen::where('tipo', 'bodega')
            ->where('activo', true)
            ->first();

        if (!$bodega) {
            return back()
                ->withInput()
                ->withErrors(['recepcion' => 'No existe una ubicación activa de tipo Bodega.']);
        }

        $orden->load('detalles.producto');

        try {
            $recepcion = DB::transaction(function () use ($validated, $orden, $bodega) {

                $recepcion = RecepcionMercaderia::create([
                    'orden_compra_id' => $orden->id,
                    'usuario_id' => auth()->id(),
                    'fecha_recepcion' => now(),
                    'numero_comprobante' => $validated['numero_comprobante'] ?? null,
                    'estado' => 'parcial',
                    'observaciones' => $validated['observaciones'] ?? null,
                ]);

                $totalRecibido = 0;

                foreach ($validated['detalles'] as $detalleId => $datos) {

                    $detalleOrden = $orden->detalles
                        ->firstWhere('id', (int) $detalleId);

                    if (!$detalleOrden) {
                        throw new \Exception('Uno de los productos no pertenece a esta orden.');
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

                    $lote = Lote::where('numero_lote', $datos['numero_lote'])->first();

                    if ($lote) {

                        if ($lote->producto_id !== $detalleOrden->producto_id) {
                            throw new \Exception(
                                "El lote {$datos['numero_lote']} ya pertenece a otro producto."
                            );
                        }

                        if (
                            $lote->fecha_vencimiento->format('Y-m-d') !==
                            $datos['fecha_vencimiento']
                        ) {
                            throw new \Exception(
                                "El lote {$datos['numero_lote']} ya existe con otra fecha de vencimiento."
                            );
                        }

                        $lote->increment('cantidad_inicial', $cantidad);

                        $lote->update([
                            'estado' => 'activo',
                        ]);

                    } else {

                        $lote = Lote::create([
                            'producto_id' => $detalleOrden->producto_id,
                            'numero_lote' => $datos['numero_lote'],
                            'fecha_fabricacion' => $datos['fecha_fabricacion'] ?? null,
                            'fecha_vencimiento' => $datos['fecha_vencimiento'],
                            'cantidad_inicial' => $cantidad,
                            'estado' => 'activo',
                            'observaciones' => 'Creado desde recepción de mercadería.',
                        ]);
                    }

                    Stock::registrarEntrada(
                        $detalleOrden->producto,
                        $lote,
                        $bodega,
                        $cantidad,
                        auth()->id(),
                        "Recepción #{$recepcion->id} - {$orden->numero_orden}",
                        'Recepción de mercadería'
                    );

                    RecepcionMercaderiaDetalle::create([
                        'recepcion_mercaderia_id' => $recepcion->id,
                        'producto_id' => $detalleOrden->producto_id,
                        'lote_id' => $lote->id,
                        'cantidad_recibida' => $cantidad,
                        'precio_unitario' => $detalleOrden->precio_unitario,
                    ]);

                    $nuevaCantidad = $detalleOrden->cantidad_recibida + $cantidad;

                    $detalleOrden->update([
                        'cantidad_recibida' => $nuevaCantidad,
                        'estado_linea' => $nuevaCantidad >= $detalleOrden->cantidad_solicitada
                            ? 'recibida'
                            : 'parcial',
                    ]);

                    $totalRecibido += $cantidad;
                }

                if ($totalRecibido <= 0) {
                    throw new \Exception('Debes recibir al menos un producto.');
                }

                $orden->refresh();
                $orden->load('detalles');

                $ordenCompleta = $orden->detalles->every(function ($detalle) {
                    return $detalle->estado_linea === 'recibida'
                        || $detalle->estado_linea === 'cancelada';
                });

                $orden->update([
                    'estado' => $ordenCompleta ? 'recibida' : 'parcial',
                ]);

                $recepcion->update([
                    'estado' => $ordenCompleta ? 'completa' : 'parcial',
                ]);

                return $recepcion;
            });

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->withErrors(['recepcion' => $e->getMessage()]);
        }

        return redirect()
            ->route('compras.recepciones.show', $recepcion)
            ->with('success', 'Mercadería recibida correctamente.');
    }

    public function show(RecepcionMercaderia $recepcion)
    {
        $recepcion->load([
            'ordenCompra.proveedor',
            'usuario',
            'detalles.producto',
            'detalles.lote',
        ]);

        return view('compras.recepciones.show', compact('recepcion'));
    }
}