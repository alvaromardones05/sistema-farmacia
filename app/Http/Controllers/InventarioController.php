<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Stock;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use App\Models\UbicacionAlmacen;


class InventarioController extends Controller
{
    // ===================== LISTAR =====================
    public function index()
    {
        $productos = Producto::with('stock.ubicacion', 'stock.lote')
            ->activos()
            ->paginate(15);

        return view('inventario.index', [
            'productos' => $productos,
        ]);
    }

    // ===================== TRANSFERIR STOCK =====================
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
        $origenUbicacion = UbicacionAlmacen::find($validated['ubicacion_origen_id']);
        $destinoUbicacion = UbicacionAlmacen::find($validated['ubicacion_destino_id']);

        try {
            // Salida del origen
            Stock::registrarSalida(
                $producto,
                $lote,
                $origenUbicacion,
                $validated['cantidad'],
                auth()->id(),
                'transferencia',
                null,
                $validated['motivo']
            );

            // Entrada al destino
            Stock::registrarEntrada(
                $producto,
                $lote,
                $destinoUbicacion,
                $validated['cantidad'],
                auth()->id(),
                null,
                $validated['motivo']
            );

            return back()->with('success', 'Transferencia realizada exitosamente');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    // ===================== AJUSTE DE INVENTARIO =====================
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
        $ubicacion = UbicacionAlmacen::find($validated['ubicacion_id']);

        try {
            if ($validated['diferencia'] > 0) {
                Stock::registrarEntrada(
                    $producto,
                    $lote,
                    $ubicacion,
                    $validated['diferencia'],
                    auth()->id(),
                    null,
                    "Ajuste: {$validated['motivo']}"
                );
            } else {
                Stock::registrarSalida(
                    $producto,
                    $lote,
                    $ubicacion,
                    abs($validated['diferencia']),
                    auth()->id(),
                    'ajuste',
                    null,
                    "Ajuste: {$validated['motivo']}"
                );
            }

            return back()->with('success', 'Ajuste registrado');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    // ===================== STOCK POR UBICACIÓN =====================
    public function stockPorUbicacion(Producto $producto)
    {
        $detalles = Stock::where('producto_id', $producto->id)
            ->with('ubicacion', 'lote')
            ->get();

        return response()->json(['data' => $detalles]);
    }
}