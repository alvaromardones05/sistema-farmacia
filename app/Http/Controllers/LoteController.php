<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function index()
    {
        $lotes = Lote::with('producto')->paginate(15);
        return view('lotes.index', compact('lotes'));
    }

    public function create()
    {
        $productos = Producto::where('activo', true)->get();
        return view('lotes.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id'       => 'required|exists:productos,id',
            'numero_lote'       => 'required|string|unique:lotes,numero_lote|max:100',
            'fecha_fabricacion' => 'nullable|date',
            'fecha_vencimiento' => 'required|date|after:today',
            'cantidad_inicial'  => 'required|integer|min:0',
            'estado'            => 'required|in:activo,agotado,vencido',
            'observaciones'     => 'nullable|string',
        ]);

        Lote::create($validated);

        return redirect()
            ->route('lotes.index')
            ->with('success', 'Lote creado exitosamente.');
    }

    public function edit(Lote $lote)
    {
        $productos = Producto::where('activo', true)->get();
        return view('lotes.edit', compact('lote', 'productos'));
    }

    public function update(Request $request, Lote $lote)
    {
        $validated = $request->validate([
            'producto_id'       => 'required|exists:productos,id',
            'numero_lote'       => 'required|string|unique:lotes,numero_lote,' . $lote->id . '|max:100',
            'fecha_fabricacion' => 'nullable|date',
            'fecha_vencimiento' => 'required|date',
            'cantidad_inicial'  => 'required|integer|min:0',
            'estado'            => 'required|in:activo,agotado,vencido',
            'observaciones'     => 'nullable|string',
        ]);

        $lote->update($validated);

        return redirect()
            ->route('lotes.index')
            ->with('success', 'Lote actualizado exitosamente.');
    }

    public function destroy(Lote $lote)
    {
        $lote->delete();

        return redirect()
            ->route('lotes.index')
            ->with('success', 'Lote eliminado exitosamente.');
    }
}