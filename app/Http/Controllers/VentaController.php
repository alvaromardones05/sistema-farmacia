<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Venta;
use App\Models\Paciente;
use App\Services\VentaService;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use App\Models\VentaDetalle;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\AperturaCaja;
use App\Models\Stock;

class VentaController extends Controller
{
    // ===================== LISTAR =====================
    public function index()
    {
        $ventas = Venta::with('paciente', 'usuario', 'detalles')
            ->latest()
            ->paginate(15);

        return view('ventas.index', [
            'ventas' => $ventas,
        ]);
    }

    // ===================== CREAR (FORM) =====================
    public function create()
    {
        $pacientes = Paciente::activos()->get();
        $aperturaCaja = AperturaCaja::where('estado', 'abierta')->first();

        if (!$aperturaCaja) {
            return back()->withError('No hay caja abierta');
        }

        return view('ventas.create', [
            'pacientes' => $pacientes,
            'aperturaCaja' => $aperturaCaja,
        ]);
    }

    // ===================== GUARDAR =====================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'apertura_caja_id' => 'required|exists:aperturas_caja,id',
            'convenio_id' => 'nullable|exists:convenios,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.descuento_pct' => 'numeric|min:0|max:100',
        ]);

        $paciente = Paciente::find($validated['paciente_id']);
        $aperturaCaja = AperturaCaja::find($validated['apertura_caja_id']);

        // Crear venta
        $venta = Venta::create([
            'numero_venta' => Venta::generarNumeroVenta(),
            'paciente_id' => $paciente->id,
            'usuario_id' => auth()->id(),
            'apertura_caja_id' => $aperturaCaja->id,
            'convenio_id' => $validated['convenio_id'] ?? null,
            'fecha_venta' => now(),
            'estado' => 'pendiente',
        ]);

        // Agregar detalles
        foreach ($validated['detalles'] as $detalle) {
            $producto = Producto::find($detalle['producto_id']);
            $lote = Lote::find($detalle['lote_id']);

            $precioPrecio = $producto->getPrecioVigente($validated['convenio_id'] ?? null);
            if (!$precioPrecio) {
                $venta->delete();
                return back()->withError("No hay precio vigente para {$producto->nombre}");
            }

            $subtotal = $precioPrecio->precio_neto * $detalle['cantidad'] * (1 - ($detalle['descuento_pct'] ?? 0) / 100);
            $impuesto = $subtotal * 0.19;
            $total = $subtotal + $impuesto;

            $ventaDetalle = VentaDetalle::create([
                'venta_id' => $venta->id,
                'producto_id' => $producto->id,
                'lote_id' => $lote->id,
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $precioPrecio->precio_neto,
                'descuento_pct' => $detalle['descuento_pct'] ?? 0,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
            ]);

            // Salir del inventario
            Stock::registrarSalida(
                $producto,
                $lote,
                $aperturaCaja->caja->ubicaciones()->first(),
                $detalle['cantidad'],
                auth()->id(),
                'salida',
                "Venta #{$venta->numero_venta}"
            );
        }

        // Calcular totales
        $venta->calcularTotales();
        $venta->update(['estado' => 'completada']);

        return redirect()->route('ventas.show', $venta)
            ->with('success', 'Venta procesada');
    }

    // ===================== VER =====================
    public function show(Venta $venta)
    {
        $venta->load('paciente', 'usuario', 'detalles.producto', 'pagos', 'convenio');

        return view('ventas.show', [
            'venta' => $venta,
        ]);
    }

    // ===================== ANULAR =====================
    public function anular(Venta $venta, Request $request)
    {
        $request->validate(['motivo' => 'required|string']);

        // Devolver stock
        foreach ($venta->detalles as $detalle) {
            Stock::registrarEntrada(
                $detalle->producto,
                $detalle->lote,
                $venta->aperturaCaja->caja->ubicaciones()->first(),
                $detalle->cantidad,
                auth()->id(),
                "Anulación: {$venta->numero_venta}",
                $request->input('motivo')
            );
        }

        $venta->update(['estado' => 'anulada']);

        return redirect()->route('ventas.show', $venta)
            ->with('success', 'Venta anulada');
    }

    // ===================== REPORTE DIARIO =====================
    public function reporteDiario()
    {
        $ventas = Venta::whereDate('fecha_venta', today())
            ->completadas()
            ->with('paciente', 'usuario', 'detalles.producto')
            ->get();

        $totalVentas = $ventas->sum('total');
        $cantidadVentas = $ventas->count();

        return view('ventas.reporte-diario', [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'cantidadVentas' => $cantidadVentas,
        ]);
    }
}