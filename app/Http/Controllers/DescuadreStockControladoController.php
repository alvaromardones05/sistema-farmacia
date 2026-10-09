<?php

namespace App\Http\Controllers;

use App\Models\DescuadreStockControlado;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescuadreStockControladoController extends Controller
{
    public function index(Request $request)
    {
        $query = DescuadreStockControlado::with([
                'producto',
                'lote',
                'ubicacion',
                'usuario',
                'revisor',
            ])
            ->latest('fecha_inventario');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $descuadres = $query
            ->paginate(15)
            ->withQueryString();

        $pendientes = DescuadreStockControlado::pendientes()->count();

        return view(
            'descuadres-controlados.index',
            compact('descuadres', 'pendientes')
        );
    }

    public function aprobar(
        Request $request,
        DescuadreStockControlado $descuadre
    ) {
        $datos = $request->validate([
            'observacion_revision' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($descuadre, $datos) {

                $descuadre = DescuadreStockControlado::query()
                    ->whereKey($descuadre->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($descuadre->estado, ['registrado', 'investigado'])) {
                    throw new \Exception(
                        'Este descuadre ya fue resuelto.'
                    );
                }

                if (!$descuadre->ubicacion_id) {
                    throw new \Exception(
                        'El descuadre no tiene una ubicación asociada.'
                    );
                }

                $descuadre->load([
                    'producto',
                    'lote',
                    'ubicacion',
                ]);

                if ($descuadre->diferencia > 0) {

                    Stock::registrarEntrada(
                        $descuadre->producto,
                        $descuadre->lote,
                        $descuadre->ubicacion,
                        $descuadre->diferencia,
                        auth()->id(),
                        'ajuste',
                        'Descuadre controlado aprobado #' . $descuadre->id
                    );

                } elseif ($descuadre->diferencia < 0) {

                    $stockActual = Stock::where('producto_id', $descuadre->producto_id)
                        ->where('lote_id', $descuadre->lote_id)
                        ->where('ubicacion_id', $descuadre->ubicacion_id)
                        ->sum('cantidad');

                    if ($stockActual < abs($descuadre->diferencia)) {
                        throw new \Exception(
                            'No existe stock suficiente para aplicar este ajuste.'
                        );
                    }

                    Stock::registrarSalida(
                        $descuadre->producto,
                        $descuadre->lote,
                        $descuadre->ubicacion,
                        abs($descuadre->diferencia),
                        auth()->id(),
                        'ajuste',
                        null,
                        'Descuadre controlado aprobado #' . $descuadre->id
                    );
                }

                $descuadre->update([
                    'estado' => 'resuelto',
                    'revisado_por' => auth()->id(),
                    'fecha_resolucion' => now(),
                    'observacion_revision' =>
                        $datos['observacion_revision'] ?? null,
                ]);
            });

            return redirect()
                ->route('descuadres-controlados.index')
                ->with(
                    'success',
                    'Descuadre aprobado y stock actualizado correctamente.'
                );

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rechazar(
        Request $request,
        DescuadreStockControlado $descuadre
    ) {
        $datos = $request->validate([
            'observacion_revision' => 'required|string|max:1000',
        ]);

        if (!in_array($descuadre->estado, ['registrado', 'investigado'])) {
            return back()->with(
                'error',
                'Este descuadre ya fue resuelto.'
            );
        }

        $descuadre->update([
            'estado' => 'rechazado',
            'revisado_por' => auth()->id(),
            'fecha_resolucion' => now(),
            'observacion_revision' => $datos['observacion_revision'],
        ]);

        return redirect()
            ->route('descuadres-controlados.index')
            ->with(
                'success',
                'Descuadre rechazado. El stock no fue modificado.'
            );
    }
}
