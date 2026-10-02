<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraDetalle;
use App\Models\Proveedor;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha_entrega_esperada' => 'nullable|date',
            'observaciones' => 'nullable|string',

            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad_solicitada' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
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

        return view('compras.show', compact('orden'));
    }

    private function generarNumeroOrden(): string
    {
        $ultima = OrdenCompra::latest('id')->first();
        $numero = $ultima ? $ultima->id + 1 : 1;

        return 'OC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}