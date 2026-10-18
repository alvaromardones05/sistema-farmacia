<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Convenio;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Paciente::with('convenio')->activos();

        $query->when($request->filled('buscar'), function ($q) use ($request) {
            $termino = $request->buscar;
            $q->where(function ($sub) use ($termino) {
                $sub->where('rut', 'like', "%{$termino}%")
                    ->orWhere('nombres', 'like', "%{$termino}%")
                    ->orWhere('apellidos', 'like', "%{$termino}%");
            });
        });

        $pacientes = $query->orderBy('apellidos')->paginate(15)->withQueryString();

        return view('pacientes.index', compact('pacientes'));
    }

    public function create()
    {
        $convenios = Convenio::activos()->get();

        return view('pacientes.create', compact('convenios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rut' => 'required|string|max:12|unique:pacientes,rut',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'convenio_id' => 'nullable|exists:convenios,id',
            'numero_poliza' => 'nullable|string|max:100',
        ]);

        $validated['activo'] = true;

        $paciente = Paciente::create($validated);

        if ($request->input('origen') === 'venta') {
            return redirect()->route('ventas.create', ['paciente_id' => $paciente->id])
                ->with('success', 'Paciente registrado exitosamente.');
        }

        return redirect()->route('pacientes.show', $paciente)
            ->with('success', 'Paciente registrado exitosamente.');
    }

    public function show(Paciente $paciente)
    {
        $paciente->load('convenio', 'ventas', 'recetas.detalles.producto');

        return view('pacientes.show', compact('paciente'));
    }
}
