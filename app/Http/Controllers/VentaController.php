<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Paciente;
use App\Services\VentaService;
use App\Services\InventarioService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    protected VentaService $ventaService;
    protected InventarioService $inventarioService;

    public function __construct(
        VentaService $ventaService,
        InventarioService $inventarioService
    ) {
        $this->ventaService = $ventaService;
        $this->inventarioService = $inventarioService;
    }

    public function index()
    {
        return view('ventas.index', [
            'ventas' => Venta::with('paciente', 'usuario', 'detalles')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('ventas.create', [
            'pacientes' => Paciente::activos()->get(),
        ]);
    }

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
        $aperturaCaja = \App\Models\AperturaCaja::find($validated['apertura_caja_id']);

        $venta = $this->ventaService->crearVenta(
            $paciente,
            $aperturaCaja,
            auth()->id(),
            $validated['convenio_id'] ?? null
        );

        foreach ($validated['detalles'] as $detalle) {
            $producto = \App\Models\Producto::find($detalle['producto_id']);
            $lote = \App\Models\Lote::find($detalle['lote_id']);

            $this->ventaService->agregarDetalle(
                $venta,
                $producto,
                $lote,
                $detalle['cantidad'],
                $detalle['descuento_pct'] ?? 0
            );
        }

        $this->ventaService->procesarVenta($venta);

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta procesada');
    }

    public function show(Venta $venta)
    {
        return view('ventas.show', [
            'venta' => $venta->load('paciente', 'usuario', 'detalles.producto', 'pagos'),
        ]);
    }

    public function anular(Venta $venta, Request $request)
    {
        $request->validate(['motivo' => 'required|string']);

        $this->ventaService->anularVenta($venta, $request->input('motivo'));

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta anulada');
    }

    public function reporteDiario()
    {
        $ventas = $this->ventaService->obtenerVentasDelDia();
        $totalVentas = $ventas->sum('total');
        $cantidadVentas = $ventas->count();

        return view('ventas.reporte-diario', [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'cantidadVentas' => $cantidadVentas,
        ]);
    }
}