<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    public function index()
    {
        $lotes = Lote::with([
            'producto',
            'stock.ubicacion',
        ])->paginate(15);

        return view('lotes.index', compact('lotes'));
    }

    public function create()
    {
        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $ubicaciones = UbicacionAlmacen::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('lotes.create', compact('productos', 'ubicaciones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id'       => 'required|exists:productos,id',
            'numero_lote'       => 'required|string|unique:lotes,numero_lote|max:100',
            'fecha_fabricacion' => 'nullable|date',
            'fecha_vencimiento' => 'required|date|after:today',
            'cantidad_inicial'  => 'required|integer|min:0',
            'ubicacion_id'      => 'required|exists:ubicaciones_almacen,id',
            'estado'            => 'required|in:activo,agotado,vencido',
            'observaciones'     => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {

            $lote = Lote::create([
                'producto_id'       => $validated['producto_id'],
                'numero_lote'       => $validated['numero_lote'],
                'fecha_fabricacion' => $validated['fecha_fabricacion'] ?? null,
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'cantidad_inicial'  => $validated['cantidad_inicial'],
                'estado'            => $validated['estado'],
                'observaciones'     => $validated['observaciones'] ?? null,
            ]);

            if ($validated['cantidad_inicial'] > 0) {

                $producto = Producto::findOrFail($validated['producto_id']);

                $ubicacion = UbicacionAlmacen::findOrFail(
                    $validated['ubicacion_id']
                );

                Stock::registrarEntrada(
                    producto: $producto,
                    lote: $lote,
                    ubicacion: $ubicacion,
                    cantidad: $validated['cantidad_inicial'],
                    usuarioId: auth()->id(),
                    referencia: 'Lote ' . $lote->numero_lote,
                    motivo: 'Ingreso inicial de lote'
                );
            }
        });

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