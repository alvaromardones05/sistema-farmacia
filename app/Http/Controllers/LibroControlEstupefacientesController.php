<?php

namespace App\Http\Controllers;

use App\Models\LibroControlEstupefaciente;
use App\Services\ControlEstupefacientesService;
use Illuminate\Http\Request;
use App\Models\Producto;

class LibroControlEstupefacientesController extends Controller
{
    // ===================== LISTAR =====================
    public function index()
    {
        $libros = LibroControlEstupefaciente::with('producto', 'lote', 'usuarioISP')
            ->orderBy('numero_folio')
            ->paginate(20);

        return view('libro-control.index', [
            'libros' => $libros,
        ]);
    }

    // ===================== VER DETALLE =====================
    public function show(LibroControlEstupefaciente $libro)
    {
        $libro->load('producto', 'lote', 'usuarioISP', 'movimientoStock');

        return view('libro-control.show', [
            'libro' => $libro,
        ]);
    }

    // ===================== REPORTE POR PRODUCTO =====================
    public function reportePorProducto($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        if (!$producto->es_controlado) {
            return back()->withError('Producto no es controlado');
        }

        $historial = LibroControlEstupefaciente::where('producto_id', $producto->id)
            ->with('lote', 'usuarioISP')
            ->orderBy('numero_folio')
            ->get();

        $saldo = LibroControlEstupefaciente::obtenerSaldo($producto);

        return view('libro-control.por-producto', [
            'producto' => $producto,
            'historial' => $historial,
            'saldo' => $saldo,
        ]);
    }

    // ===================== VERIFICAR INTEGRIDAD =====================
    public function verificarIntegridad(Request $request)
    {
        $tipoLibro = $request->input('tipo', 'salida');
        $esIntegro = LibroControlEstupefaciente::verificarIntegridad($tipoLibro);

        return response()->json([
            'integro' => $esIntegro,
            'tipo' => $tipoLibro,
            'mensaje' => $esIntegro ? 'Numeración correcta' : 'Hay saltos en la numeración',
        ]);
    }

    // ===================== EXPORTAR ISP =====================
    public function exportarISP(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $reporte = LibroControlEstupefaciente::whereBetween('fecha_registro', [
            $validated['fecha_inicio'],
            $validated['fecha_fin']
        ])
        ->with('producto', 'lote', 'usuarioISP')
        ->orderBy('numero_folio')
        ->get()
        ->groupBy('tipo_libro');

        return view('libro-control.reporte-isp', [
            'reporte' => $reporte,
            'fechaInicio' => $validated['fecha_inicio'],
            'fechaFin' => $validated['fecha_fin'],
        ]);
    }
}