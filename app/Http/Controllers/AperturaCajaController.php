<?php

namespace App\Http\Controllers;

use App\Models\AperturaCaja;
use App\Models\Caja;
use Illuminate\Http\Request;

class AperturaCajaController extends Controller
{
    public function index()
    {
        $apertura = AperturaCaja::abiertas()->with('caja', 'usuario')->first();
        $cajas = Caja::activas()->get();

        return view('caja.index', compact('apertura', 'cajas'));
    }

    public function abrir(Request $request)
{
    $request->validate([
        'caja_id' => 'required|exists:cajas,id',
        'saldo_inicial' => 'required|numeric|min:0',
        'redirect_to' => 'nullable|string',
    ]);

    if (AperturaCaja::abiertas()->exists()) {
        return back()->withErrors([
            'caja' => 'Ya existe una caja abierta.'
        ]);
    }

    AperturaCaja::create([
        'caja_id' => $request->caja_id,
        'usuario_id' => auth()->id(),
        'fecha_apertura' => now(),
        'saldo_inicial' => $request->saldo_inicial,
        'estado' => 'abierta',
    ]);

    if ($request->filled('redirect_to')) {
        return redirect($request->redirect_to)
            ->with('success', 'Caja abierta correctamente.');
    }

    return redirect()->route('caja.index')
        ->with('success', 'Caja abierta correctamente.');
}
    public function cerrar(AperturaCaja $apertura)
    {
        if ($apertura->estado !== 'abierta') {
            return back()->withError('Esta caja ya está cerrada.');
        }

        // Nota: esto NO genera un CierreCaja con arqueo/conciliación
        // todavía — eso es un módulo aparte, pendiente a futuro.
        $apertura->update(['estado' => 'cerrada']);

        return redirect()->route('caja.index')->with('success', 'Caja cerrada correctamente.');
    }
}