<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\AperturaCaja;
use App\Models\UbicacionAlmacen;
use App\Models\Convenio;
use App\Models\Pago;
use App\Models\DocumentoTributario;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    // ===================== LISTAR =====================
    public function index()
    {
        $ventas = Venta::with('paciente', 'usuario', 'detalles')
            ->latest()
            ->paginate(15);

        return view('ventas.index', compact('ventas'));
    }

    // ===================== CREAR =====================
    public function create()
    {
        $aperturaCaja = AperturaCaja::abiertas()->first();

        $pacientes = Paciente::activos()->get();
        $convenios = Convenio::where('activo', true)->get();

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        $productos = Producto::activos()->get()->map(function (Producto $producto) use ($ubicacionVenta) {
            $precio = $producto->getPrecioVigente();

            $disponible = $ubicacionVenta
                ? Stock::where('producto_id', $producto->id)
                    ->where('ubicacion_id', $ubicacionVenta->id)
                    ->where('cantidad', '>', 0)
                    ->whereHas('lote', function ($q) {
                        $q->where('estado', 'activo')
                            ->whereDate('fecha_vencimiento', '>=', today());
                    })
                    ->sum('cantidad')
                : 0;

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo_interno,
                'precio_neto' => $precio?->precio_neto ?? 0,
                'tiene_precio' => $precio !== null,
                'disponible' => (int) $disponible,
                'requiere_receta' => (bool) $producto->requiere_receta,
                'tipo_receta' => $producto->tipo_receta,
            ];
        });

        return view('ventas.create', compact(
            'pacientes',
            'convenios',
            'productos',
            'aperturaCaja',
            'ubicacionVenta'
        ));
    }

    // ===================== GUARDAR =====================
    public function store(Request $request)
    {
        $aperturaCaja = AperturaCaja::abiertas()->first();

        if (!$aperturaCaja) {
            return back()
                ->withInput()
                ->withErrors(['caja' => 'No hay caja abierta. Ábrela antes de vender.']);
        }

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        if (!$ubicacionVenta) {
            return back()
                ->withInput()
                ->withErrors(['ubicacion' => 'No existe una ubicación de tipo vitrina configurada.']);
        }

        $validated = $request->validate([
            'paciente_id' => 'nullable|exists:pacientes,id',
            'convenio_id' => 'nullable|exists:convenios,id',

            'metodo_pago' => 'required|in:efectivo,debito,credito,transferencia,cheque',
            'referencia_transaccion' => 'nullable|string|max:100',

            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.descuento_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $venta = DB::transaction(function () use (
                $validated,
                $aperturaCaja,
                $ubicacionVenta
            ) {
                $venta = Venta::create([
                    'numero_venta' => Venta::generarNumeroVenta(),
                    'paciente_id' => $validated['paciente_id'] ?? null,
                    'usuario_id' => auth()->id(),
                    'apertura_caja_id' => $aperturaCaja->id,
                    'convenio_id' => $validated['convenio_id'] ?? null,
                    'fecha_venta' => now(),
                    'subtotal' => 0,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'total' => 0,
                    'estado' => 'pendiente',
                ]);

                foreach ($validated['detalles'] as $detalle) {
                    $producto = Producto::activos()->findOrFail($detalle['producto_id']);

                    if ($producto->requiere_receta) {
                        throw new \Exception(
                            "El producto {$producto->nombre} requiere receta y no puede venderse como venta libre."
                        );
                    }

                    $cantidadSolicitada = (int) $detalle['cantidad'];
                    $descuentoPct = (float) ($detalle['descuento_pct'] ?? 0);

                    $precio = $producto->getPrecioVigente();

                    if (!$precio) {
                        throw new \Exception(
                            "No hay precio vigente para {$producto->nombre}."
                        );
                    }

                    $stocks = $this->obtenerStocksFEFO(
                        $producto,
                        $ubicacionVenta
                    );

                    $stockTotal = (int) $stocks->sum('cantidad');

                    if ($stockTotal < $cantidadSolicitada) {
                        throw new \Exception(
                            "Stock insuficiente de {$producto->nombre} en vitrina."
                        );
                    }

                    $cantidadRestante = $cantidadSolicitada;

                    foreach ($stocks as $stock) {
                        if ($cantidadRestante <= 0) {
                            break;
                        }

                        $cantidadLote = min(
                            $cantidadRestante,
                            (int) $stock->cantidad
                        );

                        $subtotalSinDescuento =
                            (float) $precio->precio_neto * $cantidadLote;

                        $descuentoMonto =
                            $subtotalSinDescuento * ($descuentoPct / 100);

                        $subtotal =
                            $subtotalSinDescuento - $descuentoMonto;

                        $impuesto = $subtotal * 0.19;
                        $total = $subtotal + $impuesto;

                        VentaDetalle::create([
                            'venta_id' => $venta->id,
                            'producto_id' => $producto->id,
                            'lote_id' => $stock->lote_id,
                            'cantidad' => $cantidadLote,
                            'precio_unitario' => $precio->precio_neto,
                            'descuento_pct' => $descuentoPct,
                            'subtotal' => round($subtotal, 2),
                            'impuesto' => round($impuesto, 2),
                            'total' => round($total, 2),
                        ]);

                        Stock::registrarSalida(
                            $producto,
                            $stock->lote,
                            $ubicacionVenta,
                            $cantidadLote,
                            auth()->id(),
                            'salida',
                            "Venta #{$venta->numero_venta}"
                        );

                        $cantidadRestante -= $cantidadLote;
                    }
                }

                // Calcular totales reales
                $venta->calcularTotales();

                // Registrar pago
                Pago::create([
                    'venta_id' => $venta->id,
                    'metodo_pago' => $validated['metodo_pago'],
                    'monto' => $venta->total,
                    'referencia_transaccion' =>
                        $validated['referencia_transaccion'] ?? null,
                ]);

                // Registrar movimiento de caja
                MovimientoCaja::create([
                    'apertura_caja_id' => $aperturaCaja->id,
                    'tipo' => 'venta',
                    'monto' => $venta->total,
                    'referencia_documento' => $venta->numero_venta,
                    'concepto' => "Venta #{$venta->numero_venta}",
                    'usuario_id' => auth()->id(),
                ]);

                // Generar boleta simulada
                DocumentoTributario::create([
                    'venta_id' => $venta->id,
                    'tipo' => 'boleta_electronica',
                    'folio' => $venta->id,
                    'fecha_emision' => now(),
                    'pdf_path' => null,
                    'estado_sii' => 'simulado',
                    'track_id' => 'SIM-' . $venta->numero_venta,
                ]);

                $venta->update([
                    'estado' => 'completada',
                ]);

                return $venta;
            });

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['venta' => $e->getMessage()]);
        }

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta procesada y boleta generada correctamente.');
    }

    // ===================== VER =====================
    public function show(Venta $venta)
    {
        $venta->load(
            'paciente',
            'usuario',
            'detalles.producto',
            'detalles.lote',
            'pagos',
            'convenio',
            'documentoTributario'
        );

        return view('ventas.show', compact('venta'));
    }

    // ===================== BOLETA =====================
    public function boleta(Venta $venta)
    {
        $venta->load(
            'paciente',
            'usuario',
            'detalles.producto',
            'detalles.lote',
            'pagos',
            'documentoTributario'
        );

        if (!$venta->documentoTributario) {
            abort(404, 'La venta no tiene una boleta asociada.');
        }

        return view('ventas.boleta', compact('venta'));
    }

    // ===================== ANULAR =====================
    public function anular(Venta $venta, Request $request)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        if ($venta->estado !== 'completada') {
            return back()->withErrors([
                'venta' => 'Esta venta no puede ser anulada.',
            ]);
        }

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        if (!$ubicacionVenta) {
            return back()->withErrors([
                'ubicacion' => 'No existe una ubicación de tipo vitrina.',
            ]);
        }

        DB::transaction(function () use (
            $venta,
            $ubicacionVenta,
            $request
        ) {
            $venta->load('detalles.producto', 'detalles.lote');

            foreach ($venta->detalles as $detalle) {
                Stock::registrarEntrada(
                    $detalle->producto,
                    $detalle->lote,
                    $ubicacionVenta,
                    $detalle->cantidad,
                    auth()->id(),
                    "Anulación venta #{$venta->numero_venta}",
                    $request->input('motivo')
                );
            }

            MovimientoCaja::create([
                'apertura_caja_id' => $venta->apertura_caja_id,
                'tipo' => 'reembolso',
                'monto' => $venta->total,
                'referencia_documento' => $venta->numero_venta,
                'concepto' => "Anulación venta #{$venta->numero_venta}",
                'usuario_id' => auth()->id(),
            ]);

            $venta->update([
                'estado' => 'anulada',
                'observaciones' => $request->input('motivo'),
            ]);
        });

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta anulada y stock devuelto correctamente.');
    }

    // ===================== REPORTE DIARIO =====================
    public function reporteDiario()
    {
        $ventas = Venta::whereDate('fecha_venta', today())
            ->completadas()
            ->with('paciente', 'usuario', 'detalles.producto')
            ->get();

        return view('ventas.reporte-diario', [
            'ventas' => $ventas,
            'totalVentas' => $ventas->sum('total'),
            'cantidadVentas' => $ventas->count(),
        ]);
    }

    // ===================== FEFO =====================
    private function obtenerStocksFEFO(
        Producto $producto,
        UbicacionAlmacen $ubicacion
    ) {
        return Stock::where('producto_id', $producto->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->where('cantidad', '>', 0)
            ->whereHas('lote', function ($q) {
                $q->where('estado', 'activo')
                    ->whereDate('fecha_vencimiento', '>=', today());
            })
            ->with('lote')
            ->get()
            ->sortBy(fn ($stock) => $stock->lote->fecha_vencimiento)
            ->values();
    }
}