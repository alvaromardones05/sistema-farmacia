<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Stock;
use App\Models\AperturaCaja;
use App\Models\UbicacionAlmacen;
use App\Models\Convenio;
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

    // ===================== CREAR (FORM) =====================
    public function create()
    {
        $aperturaCaja = AperturaCaja::abiertas()->first();

        $pacientes = Paciente::activos()->get();
        $convenios = Convenio::where('activo', true)->get();

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        // Dataset para el formulario: producto + precio vigente + stock
        // disponible en vitrina, ya calculado en el servidor para que
        // el JS del formulario no tenga que pedir nada nuevo al backend.
        $productos = Producto::activos()->get()->map(function (Producto $producto) use ($ubicacionVenta) {
            $precio = $producto->getPrecioVigente();
            $disponible = $ubicacionVenta
                ? Stock::where('producto_id', $producto->id)
                    ->where('ubicacion_id', $ubicacionVenta->id)
                    ->sum('cantidad')
                : 0;

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo_interno,
                'precio_neto' => $precio?->precio_neto ?? 0,
                'tiene_precio' => $precio !== null,
                'disponible' => (int) $disponible,
            ];
        });

        return view('ventas.create', compact('pacientes', 'convenios', 'productos', 'aperturaCaja', 'ubicacionVenta'));
    }

    // ===================== GUARDAR =====================
    public function store(Request $request)
    {
        $aperturaCaja = AperturaCaja::abiertas()->first();

        if (!$aperturaCaja) {
            return back()->withInput()->withErrors(['caja' => 'No hay caja abierta. Ábrela antes de vender.']);
        }

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        if (!$ubicacionVenta) {
            return back()->withInput()->withErrors(['ubicacion' => 'No existe una ubicación de tipo vitrina configurada.']);
        }

        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'convenio_id' => 'nullable|exists:convenios,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.descuento_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $venta = DB::transaction(function () use ($validated, $aperturaCaja, $ubicacionVenta) {

                $venta = Venta::create([
                    'numero_venta' => Venta::generarNumeroVenta(),
                    'paciente_id' => $validated['paciente_id'],
                    'usuario_id' => auth()->id(),
                    'apertura_caja_id' => $aperturaCaja->id,
                    'convenio_id' => $validated['convenio_id'] ?? null,
                    'fecha_venta' => now(),
                    'estado' => 'pendiente',
                ]);

                foreach ($validated['detalles'] as $detalle) {
                    $producto = Producto::findOrFail($detalle['producto_id']);
                    $cantidad = (int) $detalle['cantidad'];
                    $descuentoPct = (float) ($detalle['descuento_pct'] ?? 0);

                    $precioPrecio = $producto->getPrecioVigente();
                    if (!$precioPrecio) {
                        throw new \Exception("No hay precio vigente para {$producto->nombre}.");
                    }

                    // FEFO: el sistema elige el lote, no el vendedor.
                    $lote = $this->seleccionarLoteFEFO($producto, $ubicacionVenta, $cantidad);
                    if (!$lote) {
                        throw new \Exception("Stock insuficiente de {$producto->nombre} en vitrina.");
                    }

                    $subtotal = $precioPrecio->precio_neto * $cantidad * (1 - $descuentoPct / 100);
                    $impuesto = $subtotal * 0.19;
                    $total = $subtotal + $impuesto;

                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $producto->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioPrecio->precio_neto,
                        'descuento_pct' => $descuentoPct,
                        'subtotal' => $subtotal,
                        'impuesto' => $impuesto,
                        'total' => $total,
                    ]);

                    Stock::registrarSalida(
                        $producto,
                        $lote,
                        $ubicacionVenta,
                        $cantidad,
                        auth()->id(),
                        'salida',
                        "Venta #{$venta->numero_venta}"
                    );
                }

                $venta->calcularTotales();
                $venta->update(['estado' => 'completada']);

                return $venta;
            });
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['venta' => $e->getMessage()]);
        }

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta procesada exitosamente.');
    }

    // ===================== VER =====================
    public function show(Venta $venta)
    {
        $venta->load('paciente', 'usuario', 'detalles.producto', 'detalles.lote', 'pagos', 'convenio');

        return view('ventas.show', compact('venta'));
    }

    // ===================== ANULAR =====================
    public function anular(Venta $venta, Request $request)
    {
        $request->validate(['motivo' => 'required|string']);

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        DB::transaction(function () use ($venta, $ubicacionVenta, $request) {
            foreach ($venta->detalles as $detalle) {
                Stock::registrarEntrada(
                    $detalle->producto,
                    $detalle->lote,
                    $ubicacionVenta,
                    $detalle->cantidad,
                    auth()->id(),
                    "Anulación: {$venta->numero_venta}",
                    $request->input('motivo')
                );
            }

            $venta->update(['estado' => 'anulada']);
        });

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta anulada.');
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

    // ===================== FEFO: elegir lote con stock suficiente =====================

    private function seleccionarLoteFEFO(Producto $producto, UbicacionAlmacen $ubicacion, int $cantidadRequerida): ?Lote
    {
        $stockDisponible = Stock::where('producto_id', $producto->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->where('cantidad', '>=', $cantidadRequerida)
            ->whereHas('lote', fn($q) => $q->where('estado', 'activo'))
            ->with('lote')
            ->get()
            ->sortBy(fn($s) => $s->lote->fecha_vencimiento)
            ->first();

        return $stockDisponible?->lote;
    }
}