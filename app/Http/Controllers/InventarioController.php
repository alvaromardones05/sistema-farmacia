<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // ===================== LISTAR =====================

    public function index()
    {
        // Obtiene los productos activos junto con su stock,
        // ubicación y lote asociado.
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
        // Validamos los datos recibidos antes de realizar
        // cualquier modificación en el inventario.
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'required|exists:lotes,id',
            'ubicacion_origen_id' => 'required|exists:ubicaciones_almacen,id',
            'ubicacion_destino_id' => 'required|exists:ubicaciones_almacen,id|different:ubicacion_origen_id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        // Buscamos las entidades necesarias para realizar
        // la transferencia.
        $producto = Producto::find($validated['producto_id']);
        $lote = Lote::find($validated['lote_id']);
        $origenUbicacion = UbicacionAlmacen::find($validated['ubicacion_origen_id']);
        $destinoUbicacion = UbicacionAlmacen::find($validated['ubicacion_destino_id']);

        // Verificamos que todos los registros existan.
        if (!$producto || !$lote || !$origenUbicacion || !$destinoUbicacion) {
            return back()
                ->withInput()
                ->withError('No fue posible encontrar los datos de la transferencia.');
        }

        // Verificamos que el lote corresponda al producto seleccionado.
        if ((int) $lote->producto_id !== (int) $producto->id) {
            return back()
                ->withInput()
                ->withError('El lote seleccionado no corresponde al producto.');
        }

        // Verificamos que producto, lote y ubicaciones estén activos.
        if (isset($producto->activo) && !$producto->activo) {
            return back()
                ->withInput()
                ->withError('El producto seleccionado está inactivo.');
        }

        if (isset($lote->activo) && !$lote->activo) {
            return back()
                ->withInput()
                ->withError('El lote seleccionado está inactivo.');
        }

        if (isset($origenUbicacion->activo) && !$origenUbicacion->activo) {
            return back()
                ->withInput()
                ->withError('La ubicación de origen está inactiva.');
        }

        if (isset($destinoUbicacion->activo) && !$destinoUbicacion->activo) {
            return back()
                ->withInput()
                ->withError('La ubicación de destino está inactiva.');
        }

        try {
            /*
             * La transferencia completa se ejecuta dentro de una
             * transacción.
             *
             * Si la salida o la entrada falla, Laravel realiza
             * rollback y el inventario vuelve a su estado anterior.
             */
            DB::transaction(function () use (
                $producto,
                $lote,
                $origenUbicacion,
                $destinoUbicacion,
                $validated
            ) {
                // Descontamos el stock de la ubicación de origen.
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

                // Agregamos el mismo stock a la ubicación de destino.
                Stock::registrarEntrada(
                    $producto,
                    $lote,
                    $destinoUbicacion,
                    $validated['cantidad'],
                    auth()->id(),
                    'transferencia',
                    $validated['motivo']
                );
            });

            return back()->with(
                'success',
                'Transferencia realizada exitosamente.'
            );
        } catch (\Throwable $e) {
            // Si ocurre cualquier error, no se confirma la operación.
            return back()
                ->withInput()
                ->withError($e->getMessage());
        }
    }

    // ===================== AJUSTE DE INVENTARIO =====================

    public function ajuste(Request $request)
    {
        // Validamos los datos del ajuste.
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'required|exists:lotes,id',
            'ubicacion_id' => 'required|exists:ubicaciones_almacen,id',
            'diferencia' => 'required|integer|not_in:0',
            'motivo' => 'required|string|max:255',
        ]);

        // Buscamos las entidades relacionadas.
        $producto = Producto::find($validated['producto_id']);
        $lote = Lote::find($validated['lote_id']);
        $ubicacion = UbicacionAlmacen::find($validated['ubicacion_id']);

        // Verificamos que los registros existan.
        if (!$producto || !$lote || !$ubicacion) {
            return back()
                ->withInput()
                ->withError('No fue posible encontrar los datos del ajuste.');
        }

        // Verificamos que el lote corresponda al producto.
        if ((int) $lote->producto_id !== (int) $producto->id) {
            return back()
                ->withInput()
                ->withError('El lote seleccionado no corresponde al producto.');
        }

        // Verificamos que producto, lote y ubicación estén activos.
        if (isset($producto->activo) && !$producto->activo) {
            return back()
                ->withInput()
                ->withError('El producto seleccionado está inactivo.');
        }

        if (isset($lote->activo) && !$lote->activo) {
            return back()
                ->withInput()
                ->withError('El lote seleccionado está inactivo.');
        }

        if (isset($ubicacion->activo) && !$ubicacion->activo) {
            return back()
                ->withInput()
                ->withError('La ubicación seleccionada está inactiva.');
        }

        try {
            /*
             * El ajuste también se realiza dentro de una transacción
             * para mantener sincronizados el stock y su movimiento.
             */
            DB::transaction(function () use (
                $producto,
                $lote,
                $ubicacion,
                $validated
            ) {
                // Una diferencia positiva representa una entrada.
                if ($validated['diferencia'] > 0) {
                    Stock::registrarEntrada(
                        $producto,
                        $lote,
                        $ubicacion,
                        $validated['diferencia'],
                        auth()->id(),
                        'ajuste',
                        "Ajuste: {$validated['motivo']}"
                    );
                } else {
                    // Una diferencia negativa representa una salida.
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
            });

            return back()->with(
                'success',
                'Ajuste registrado correctamente.'
            );
        } catch (\Throwable $e) {
            // Si ocurre un error, la transacción se revierte.
            return back()
                ->withInput()
                ->withError($e->getMessage());
        }
    }

    // ===================== STOCK POR UBICACIÓN =====================

    public function stockPorUbicacion(Producto $producto)
    {
        // Obtiene el stock del producto separado por ubicación y lote.
        $detalles = Stock::where('producto_id', $producto->id)
            ->with('ubicacion', 'lote')
            ->get();

        return response()->json([
            'data' => $detalles,
        ]);
    }
}