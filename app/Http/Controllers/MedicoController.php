<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use Illuminate\Http\Request;

class MedicoController extends Controller
{
    public function create()
    {
        return view('medicos.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'rut' => 'required|string|max:12|unique:medicos,rut',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'registro_profesional' => 'nullable|string|max:100',
            'especialidad' => 'nullable|string|max:100',
        ]);
        $medico = Medico::create([...$datos, 'activo' => true]);
        return redirect()->route('recetas.create', ['medico_id' => $medico->id])
            ->with('success', 'Médico registrado. Ya puedes seleccionarlo en la receta.');
    }
}
