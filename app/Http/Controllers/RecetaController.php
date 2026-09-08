<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Producto;
use Illuminate\Http\Request;

class RecetaController extends Controller
{
    // ===================== LISTAR =====================
    public function index()
    {
        $recetas = Receta::with('paciente', 'medico', 'detalles')
            ->latest()
            ->paginate(15);

        return view('recetas.index', [
            'recetas' => $recetas,
        ]);
    }

    // ===================== CREAR (FORM) =====================
    public function create()
    {
        $pacientes = Paciente::activos()->get();
        $medicos = Medico::activos()->get();

        return view('recetas.create', [
            'pacientes' => $pacientes,
            'medicos' => $medicos,
        ]);
    }

    // ===================== GUARDAR =====================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_id' => 'required|exists:medicos,id',
            'tipo' => 'required|in:simple,retenida,cheque',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad_prescrita' => 'required|integer|min:1',
            'detalles.*.unidad_medida' => 'required|string',
            'detalles.*.instrucciones' => 'nullable|string',
        ]);

        $paciente = Paciente::find($validated['paciente_id']);
        $medico = Medico::find($validated['medico_id']);

        // Crear receta
        $receta = Receta::create([
            'numero_receta' => Receta::generarNumeroReceta(),
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'tipo' => $validated['tipo'],
            'estado' => 'vigente',
        ]);

        // Agregar detalles
        foreach ($validated['detalles'] as $detalle) {
            RecetaDetalle::create([
                'receta_id' => $receta->id,
                'producto_id' => $detalle['producto_id'],
                'cantidad_prescrita' => $detalle['cantidad_prescrita'],
                'unidad_medida' => $detalle['unidad_medida'],
                'instrucciones' => $detalle['instrucciones'] ?? null,
                'estado' => 'pendiente',
            ]);
        }

        return redirect()->route('recetas.show', $receta)
            ->with('success', 'Receta creada');
    }

    // ===================== VER =====================
    public function show(Receta $receta)
    {
        $receta->load('paciente', 'medico', 'detalles.producto');

        return view('recetas.show', [
            'receta' => $receta,
        ]);
    }

    // ===================== DISPENSAR MEDICAMENTO =====================
    public function dispensar(RecetaDetalle $detalle, Request $request)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $detalle->dispensar($request->input('cantidad'));

        return back()->with('success', 'Medicamento dispensado');
    }
}