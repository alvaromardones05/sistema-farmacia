<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DescuadreStockControlado;
use App\Models\MovimientoStock;


class InventarioController extends Controller
{
// ===================== LISTAR (tabla plana + filtros + resumen) =====================

    public function index(Request $request)
    {
        $query = Stock::with(['producto', 'lote', 'ubicacion'])
            ->whereHas('producto', fn($q) => $q->where('activo', true));

        $query->when($request->filled('producto_id'), function ($q) use ($request) {
            $q->where('producto_id', $request->producto_id);
        });

        $query->when($request->filled('ubicacion_id'), function ($q) use ($request) {
            $q->where('ubicacion_id', $request->ubicacion_id);
        });

        $query->when($request->filled('buscar'), function ($q) use ($request) {
            $termino = $request->buscar;
            $q->where(function ($sub) use ($termino) {
                $sub->whereHas('producto', function ($p) use ($termino) {
                        $p->where('nombre', 'like', "%{$termino}%")
                          ->orWhere('codigo_interno', 'like', "%{$termino}%");
                    })
                    ->orWhereHas('lote', function ($l) use ($termino) {
                        $l->where('numero_lote', 'like', "%{$termino}%");
                    });
            });
        });

        $stock = $query->orderBy('producto_id')->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        // Dataset COMPLETO (sin paginar, sin filtros) para poblar los
        // selects en cascada de los modales de Traslado/Ajuste. Es una
        // consulta aparte de la tabla paginada a propósito: los modales
        // deben poder operar sobre CUALQUIER stock, no solo el filtrado.
        $stockCompleto = Stock::with(['producto:id,nombre,es_controlado', 'lote:id,numero_lote', 'ubicacion:id,nombre'])
            ->whereHas('producto', fn($q) => $q->where('activo', true))
            ->get()
            ->map(fn($s) => [
                'producto_id' => $s->producto_id,
                'producto_nombre' => $s->producto?->nombre,
                'es_controlado' => (bool) $s->producto?->es_controlado,
                'lote_id' => $s->lote_id,
                'lote_numero' => $s->lote?->numero_lote,
                'ubicacion_id' => $s->ubicacion_id,
                'ubicacion_nombre' => $s->ubicacion?->nombre,
                'cantidad' => $s->cantidad,
            ]);

        return view('inventario.index', [
            'stock' => $stock,
            'stockCompleto' => $stockCompleto,
            'productos' => Producto::activos()->orderBy('nombre')->get(),
            'ubicaciones' => UbicacionAlmacen::where('activo', true)->get(),
            'stockTotalUnidades' => Stock::whereHas('producto', fn($q) => $q->where('activo', true))->sum('cantidad'),
            'stockCriticoCount' => $this->contarProductosStockCritico(),
            'porVencerCount' => $this->contarLotesPorVencer(),
            'vencidosCount' => $this->contarLotesVencidos(),
        ]);
    }

    // ===================== HELPERS PRIVADOS PARA LAS TARJETAS =====================

    private function contarProductosStockCritico(): int
    {
        return Producto::activos()
            ->withSum('stock as stock_total', 'cantidad')
            ->get()
            ->filter(fn($p) => (int) $p->stock_total <= $p->stock_critico)
            ->count();
    }

    private function contarLotesPorVencer(int $dias = 30): int
    {
        return Lote::where('estado', 'activo')
            ->withSum('stock as stock_total', 'cantidad')
            ->get()
            ->filter(fn($l) => (int) $l->stock_total > 0
                && $l->fecha_vencimiento->between(now(), now()->addDays($dias)))
            ->count();
    }

    private function contarLotesVencidos(): int
    {
        return Lote::withSum('stock as stock_total', 'cantidad')
            ->get()
            ->filter(fn($l) => (int) $l->stock_total > 0 && $l->fecha_vencimiento->isPast())
            ->count();
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
                ->with('error', 'No fue posible encontrar los datos de la transferencia.');
        }

        // Verificamos que el lote corresponda al producto seleccionado.
        if ((int) $lote->producto_id !== (int) $producto->id) {
            return back()
                ->withInput()
                ->with('error', 'El lote seleccionado no corresponde al producto.');
        }

        // Verificamos que producto, lote y ubicaciones estén activos.
        if (isset($producto->activo) && !$producto->activo) {
            return back()
                ->withInput()
                ->with('error', 'El producto seleccionado está inactivo.');
        }

        if ($lote->estado !== 'activo') {
            return back()
                ->withInput()
                ->with('error', 'El lote seleccionado no está activo.');
        }

        if (isset($origenUbicacion->activo) && !$origenUbicacion->activo) {
            return back()
                ->withInput()
                ->with('error', 'La ubicación de origen está inactiva.');
        }

        if (isset($destinoUbicacion->activo) && !$destinoUbicacion->activo) {
            return back()
                ->withInput()
                ->with('error', 'La ubicación de destino está inactiva.');
        }

        $stockOrigen = Stock::where('producto_id', $producto->id)
            ->where('lote_id', $lote->id)
            ->where('ubicacion_id', $origenUbicacion->id)
            ->first();

        if (!$stockOrigen || $stockOrigen->cantidad < $validated['cantidad']) {
            return back()
                ->withInput()
                ->with('error', 'No hay suficiente stock disponible en la ubicación de origen.');
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
                ->with('error', $e->getMessage());
        }
    }

        // ===================== AJUSTE DE INVENTARIO =====================

    public function ajuste(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'required|exists:lotes,id',
            'ubicacion_id' => 'required|exists:ubicaciones_almacen,id',
            'diferencia' => 'required|integer|not_in:0',
            'motivo' => 'required|string|max:255',
        ]);

        $producto = Producto::find($validated['producto_id']);
        $lote = Lote::find($validated['lote_id']);
        $ubicacion = UbicacionAlmacen::find($validated['ubicacion_id']);

        if (!$producto || !$lote || !$ubicacion) {
            return back()
                ->withInput()
                ->with('error', 'No fue posible encontrar los datos del ajuste.');
        }

        if ((int) $lote->producto_id !== (int) $producto->id) {
            return back()
                ->withInput()
                ->with('error', 'El lote seleccionado no corresponde al producto.');
        }

        if (isset($producto->activo) && !$producto->activo) {
            return back()
                ->withInput()
                ->with('error', 'El producto seleccionado está inactivo.');
        }

        if ($lote->estado !== 'activo') {
            return back()
                ->withInput()
            ->with('error', 'El lote seleccionado no está activo.');
        }

        if (isset($ubicacion->activo) && !$ubicacion->activo) {
            return back()
                ->withInput()
                ->with('error', 'La ubicación seleccionada está inactiva.');
        }

        // =========================================================
        // PRODUCTO CONTROLADO (ISP): no se toca stock directamente.
        // Se registra un descuadre para que el Químico Farmacéutico
        // investigue y apruebe antes de alterar el libro oficial.
        // =========================================================
        if ($producto->es_controlado) {
            try {
                DB::transaction(function () use ($producto, $lote, $ubicacion, $validated) {
                    $cantidadEsperada = Stock::where('producto_id', $producto->id)
                        ->where('lote_id', $lote->id)
                        ->where('ubicacion_id', $ubicacion->id)
                        ->sum('cantidad');

                    $cantidadContada = $cantidadEsperada + $validated['diferencia'];

                    DescuadreStockControlado::create([
                        'producto_id' => $producto->id,
                        'lote_id' => $lote->id,
                        'cantidad_esperada' => $cantidadEsperada,
                        'cantidad_contada' => $cantidadContada,
                        'diferencia' => $validated['diferencia'],
                        'fecha_inventario' => now(),
                        'usuario_id' => auth()->id(),
                        'estado' => 'registrado',
                        'explicacion' => $validated['motivo'],
                    ]);
                });

                return back()->with(
                    'success',
                    'Producto controlado: el descuadre fue registrado para revisión del Químico Farmacéutico. El stock no se modificó todavía.'
                );
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        // =========================================================
        // PRODUCTO NO CONTROLADO: ajuste directo (comportamiento
        // original, sin cambios).
        // =========================================================
        try {
            DB::transaction(function () use ($producto, $lote, $ubicacion, $validated) {
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
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
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

        // ===================== PRÓXIMOS A VENCER (FEFO) =====================

    public function proximosAVencer(Request $request)
    {
        // Ventana de días configurable desde el filtro del formulario,
        // 60 días por defecto si no se especifica nada.
        $dias = (int) $request->get('dias', 60);

        $lotes = Lote::with('producto')
            ->withSum('stock as stock_total', 'cantidad')
            // Orden FEFO real: el que vence primero aparece primero.
            ->orderBy('fecha_vencimiento')
            ->get()
            ->filter(function ($lote) use ($dias) {
                // Solo lotes con stock real, y dentro de la ventana elegida
                // (incluye vencidos: fecha_vencimiento <= hoy también cae aquí).
                return (int) $lote->stock_total > 0
                    && $lote->fecha_vencimiento->lte(now()->addDays($dias));
            });

        return view('inventario.proximos-vencer', compact('lotes', 'dias'));
    }

        // ===================== ALERTAS DE STOCK =====================

    public function alertas()
    {
        // Productos con stock bajo o crítico (calculado en vivo)
        $productos = Producto::activos()
            ->withSum('stock as stock_total', 'cantidad')
            ->get();

        $stockCritico = $productos->filter(
            fn($p) => (int) $p->stock_total <= $p->stock_critico
        );

        $stockBajo = $productos->filter(
            fn($p) => (int) $p->stock_total > $p->stock_critico
                && (int) $p->stock_total <= $p->stock_minimo
        );

        // Lotes activos con stock real > 0 (calculado en vivo)
        $lotesConStock = Lote::where('estado', 'activo')
            ->with('producto')
            ->withSum('stock as stock_total', 'cantidad')
            ->get()
            ->filter(fn($l) => (int) $l->stock_total > 0);

        $porVencer = $lotesConStock->filter(
            fn($l) => $l->fecha_vencimiento->between(now(), now()->addDays(30))
        );

        $vencidos = $lotesConStock->filter(
            fn($l) => $l->fecha_vencimiento->isPast()
        );

        return view('inventario.alertas', compact(
            'stockCritico',
            'stockBajo',
            'porVencer',
            'vencidos'
        ));
    }


        // ===================== HISTORIAL DE MOVIMIENTOS (KARDEX) =====================

    public function historial(Request $request)
    {
        $query = MovimientoStock::with(['producto', 'lote', 'ubicacion', 'usuario'])
            ->latest(); // más reciente primero

        // Cada filtro se aplica SOLO si el usuario lo llenó (when() evita
        // condiciones vacías del tipo where('producto_id', null)).
        $query->when($request->filled('producto_id'), function ($q) use ($request) {
            $q->where('producto_id', $request->producto_id);
        });

        $query->when($request->filled('ubicacion_id'), function ($q) use ($request) {
            $q->where('ubicacion_id', $request->ubicacion_id);
        });

        $query->when($request->filled('tipo_movimiento'), function ($q) use ($request) {
            $q->where('tipo_movimiento', $request->tipo_movimiento);
        });

        $query->when($request->filled('fecha_desde'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->fecha_desde);
        });

        $query->when($request->filled('fecha_hasta'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->fecha_hasta);
        });

        // withQueryString() mantiene los filtros activos al cambiar de página.
        $movimientos = $query->paginate(20)->withQueryString();

        $productos = Producto::activos()->orderBy('nombre')->get();
        $ubicaciones = UbicacionAlmacen::where('activo', true)->get();

        return view('inventario.historial', compact('movimientos', 'productos', 'ubicaciones'));
    }

    public function mostrarTransferir()
{
    $productos = Producto::activos()
        ->orderBy('nombre')
        ->get();

    $lotes = Lote::where('estado', 'activo')
        ->orderBy('numero_lote')
        ->get();

    $ubicaciones = UbicacionAlmacen::where('activo', true)
        ->orderBy('nombre')
        ->get();

    $stock = Stock::where('cantidad', '>', 0)->get();

    return view('inventario.transferir', compact(
        'productos',
        'lotes',
        'ubicaciones',
        'stock'
    ));
}

    public function mostrarAjustar()
    {
        $productos = Producto::activos()
            ->orderBy('nombre')
            ->get();

        $lotes = Lote::where('estado', 'activo')
            ->whereHas('producto', function ($query) {
                $query->where('activo', true);
            })
            ->orderBy('numero_lote')
            ->get();

        $ubicaciones = UbicacionAlmacen::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('inventario.ajustar', compact(
            'productos',
            'lotes',
            'ubicaciones'
        ));
    }
}