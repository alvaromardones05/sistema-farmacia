<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraDetalle;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class OrdenCompraController extends Controller
{
    public function index()
    {
        $ordenes = OrdenCompra::with(['proveedor', 'usuario'])
            ->latest('fecha_orden')
            ->paginate(15);

        $pendientes = OrdenCompra::whereIn('estado', ['pendiente', 'confirmada'])->count();
        $parciales = OrdenCompra::where('estado', 'parcial')->count();
        $recibidas = OrdenCompra::where('estado', 'recibida')->count();
        $proveedoresActivos = Proveedor::activos()->count();

        return view('compras.index', compact(
            'ordenes',
            'pendientes',
            'parciales',
            'recibidas',
            'proveedoresActivos'
        ));
    }

    public function create()
    {
        $proveedores = Proveedor::activos()
            ->orderBy('nombre_razon_social')
            ->get();

        $productos = Producto::activos()
            ->orderBy('nombre')
            ->get();

        $stockPorProducto = DB::table('stock')
            ->join('lotes', 'lotes.id', '=', 'stock.lote_id')
            ->select('lotes.producto_id', DB::raw('SUM(stock.cantidad) as total'))
            ->groupBy('lotes.producto_id')
            ->pluck('total', 'producto_id');

        $productos->each(function ($p) use ($stockPorProducto) {
            $p->stock_actual = (int) ($stockPorProducto[$p->id] ?? 0);
        });

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha_entrega_esperada' => 'nullable|date',
            'observaciones' => 'nullable|string|max:2000',

            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id|distinct',
            'detalles.*.cantidad_solicitada' => 'required|integer|min:1|max:100000',
            'detalles.*.precio_unitario' => 'required|integer|min:0|max:99999999',
        ], [
            'detalles.required' => 'Debes agregar al menos un producto.',
            'detalles.min' => 'Debes agregar al menos un producto.',
            'detalles.*.producto_id.distinct' => 'No puedes agregar el mismo producto dos veces.',
            'detalles.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'detalles.*.cantidad_solicitada.min' => 'La cantidad debe ser mayor a cero.',
            'detalles.*.cantidad_solicitada.max' => 'La cantidad es demasiado grande.',
            'detalles.*.precio_unitario.min' => 'El precio no puede ser negativo.',
            'detalles.*.precio_unitario.max' => 'El precio es demasiado grande.',
        ]);

        $proveedor = Proveedor::findOrFail($validated['proveedor_id']);

        if (!$proveedor->activo) {
            return back()
                ->withInput()
                ->withErrors(['proveedor_id' => 'El proveedor seleccionado está inactivo.']);
        }

        try {
            $orden = DB::transaction(function () use ($validated) {

                $orden = OrdenCompra::create([
                    'numero_orden' => $this->generarNumeroOrden(),
                    'proveedor_id' => $validated['proveedor_id'],
                    'fecha_orden' => now(),
                    'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'] ?? null,
                    'estado' => 'pendiente',
                    'usuario_id' => auth()->id(),
                    'observaciones' => $validated['observaciones'] ?? null,
                ]);

                foreach ($validated['detalles'] as $detalle) {
                    OrdenCompraDetalle::create([
                        'orden_compra_id' => $orden->id,
                        'producto_id' => $detalle['producto_id'],
                        'cantidad_solicitada' => $detalle['cantidad_solicitada'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'cantidad_recibida' => 0,
                        'estado_linea' => 'pendiente',
                    ]);
                }

                return $orden;
            });

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['orden' => $e->getMessage()]);
        }

        return redirect()
            ->route('compras.show', $orden)
            ->with('success', 'Orden de compra creada correctamente.');
    }

    public function show(OrdenCompra $orden)
    {
        $orden->load([
            'proveedor',
            'usuario',
            'detalles.producto',
            'recepciones.usuario',
        ]);

        $puedeRecibir = $orden->puedeRecibirMercaderia();

        return view('compras.show', compact('orden', 'puedeRecibir'));
    }

    private function generarNumeroOrden(): string
    {
        $ultima = OrdenCompra::latest('id')->first();
        $numero = $ultima ? $ultima->id + 1 : 1;

        return 'OC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

           public function pdf(OrdenCompra $orden)
    {
        $orden->load(['proveedor', 'usuario', 'detalles.producto']);
        $pdf = Pdf::loadView('pdf.orden_compra', compact('orden'));
        return $pdf->download("Orden_Compra_{$orden->numero_orden}.pdf");
    }

    public function guia(OrdenCompra $orden)
    {
        $orden->load(['proveedor', 'detalles.producto']);
        $pdf = Pdf::loadView('pdf.guia_despacho', compact('orden'));
        return $pdf->download("Guia_Despacho_{$orden->numero_orden}.pdf");
    }

    public function acta(OrdenCompra $orden)
    {
        $orden->load(['proveedor', 'detalles.producto']);
        $pdf = Pdf::loadView('pdf.acta_recepcion', compact('orden'))->setPaper('a4', 'landscape');
        return $pdf->download("Acta_Recepcion_{$orden->numero_orden}.pdf");
    }
}
