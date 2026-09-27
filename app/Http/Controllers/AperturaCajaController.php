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
        // Solo una caja abierta a la vez en todo el sistema.
        if (AperturaCaja::abiertas()->exists()) {
            return back()->withError('Ya hay una caja abierta. Ciérrala antes de abrir otra.');
        }

        $validated = $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'saldo_inicial' => 'required|numeric|min:0',
        ]);

        AperturaCaja::create([
            'caja_id' => $validated['caja_id'],
            'usuario_id' => auth()->id(),
            'fecha_apertura' => now(),
            'saldo_inicial' => $validated['saldo_inicial'],
            'estado' => 'abierta',
        ]);

        return redirect()->route('caja.index')->with('success', 'Caja abierta correctamente.');
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