<?php

namespace App\Http\Controllers;

use App\Models\LibroControlEstupefaciente;
use App\Services\ControlEstupefacientesService;
use Illuminate\Http\Request;

class LibroControlEstupefacientesController extends Controller
{
    protected ControlEstupefacientesService $controlService;

    public function __construct(ControlEstupefacientesService $controlService)
    {
        $this->controlService = $controlService;
    }

    public function index()
    {
        $libros = LibroControlEstupefaciente::with('producto', 'lote', 'usuarioISP')
            ->orderBy('numero_folio')
            ->paginate(20);

        return view('libro-control.index', [
            'libros' => $libros,
        ]);
    }

    public function show(LibroControlEstupefaciente $libro)
    {
        return view('libro-control.show', [
            'libro' => $libro->load('producto', 'lote', 'usuarioISP', 'movimientoStock'),
        ]);
    }

    public function reportePorProducto($productoId)
    {
        $producto = \App\Models\Producto::findOrFail($productoId);

        if (!$producto->es_controlado) {
            return back()->withError('Producto no es controlado');
        }

        $historial = $this->controlService->obtenerHistorial($producto);
        $saldo = $this->controlService->obtenerSaldo($producto);

        return view('libro-control.por-producto', [
            'producto' => $producto,
            'historial' => $historial,
            'saldo' => $saldo,
        ]);
    }

    public function verificarIntegridad(Request $request)
    {
        $tipoLibro = $request->input('tipo', 'salida');
        $esIntegro = $this->controlService->verificarIntegridad($tipoLibro);

        return response()->json([
            'integro' => $esIntegro,
            'tipo' => $tipoLibro,
            'mensaje' => $esIntegro ? 'Numeración correcta' : 'Hay saltos en la numeración',
        ]);
    }

    public function exportarISP(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $reporte = $this->controlService->generarReporteISP(
            $validated['fecha_inicio'],
            $validated['fecha_fin']
        );

        return view('libro-control.reporte-isp', [
            'reporte' => $reporte,
            'fechaInicio' => $validated['fecha_inicio'],
            'fechaFin' => $validated['fecha_fin'],
        ]);
    }
}