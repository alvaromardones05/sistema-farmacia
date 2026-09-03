<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Stock;
use App\Services\InventarioService;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    public function index()
    {
        $productos = Producto::with('stock.ubicacion', 'stock.lote')
            ->activos()
            ->paginate(15);

        return view('inventario.index', [
            'productos' => $productos,
        ]);
    }

    public function transferir(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'required|exists:lotes,id',
            'ubicacion_origen_id' => 'required|exists:ubicaciones_almacen,id',
            'ubicacion_destino_id' => 'required|exists:ubicaciones_almacen,id|different:ubicacion_origen_id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string',
        ]);

        $producto = Producto::find($validated['producto_id']);
        $lote = Lote::find($validated['lote_id']);
        $origenUbicacion = \App\Models\UbicacionAlmacen::find($validated['ubicacion_origen_id']);
        $destinoUbicacion = \App\Models\UbicacionAlmacen::find($validated['ubicacion_destino_id']);

        try {
            $this->inventarioService->transferir(
                $producto,
                $lote,
                $origenUbicacion,
                $destinoUbicacion,
                $validated['cantidad'],
                auth()->id(),
                $validated['motivo']
            );

            return back()->with('success', 'Transferencia realizada exitosamente');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function ajuste(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'required|exists:lotes,id',
            'ubicacion_id' => 'required|exists:ubicaciones_almacen,id',
            'diferencia' => 'required|integer',
            'motivo' => 'required|string|max:255',
        ]);

        $producto = Producto::find($validated['producto_id']);
        $lote = Lote::find($validated['lote_id']);
        $ubicacion = \App\Models\UbicacionAlmacen::find($validated['ubicacion_id']);

        $this->inventarioService->registrarAjuste(
            $producto,
            $lote,
            $ubicacion,
            $validated['diferencia'],
            auth()->id(),
            $validated['motivo']
        );

        return back()->with('success', 'Ajuste registrado');
    }

    public function stockPorUbicacion(Producto $producto)
    {
        $detalles = $this->inventarioService->obtenerStockPorUbicacion($producto);

        return response()->json(['data' => $detalles]);
    }
}