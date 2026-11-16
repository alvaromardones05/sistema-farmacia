<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Models\Categoria;
use App\Models\Laboratorio;
use App\Models\AlertaStock;


class ProductoController extends Controller
{
    // ===================== LISTAR =====================
    public function index(Request $request)
{
        $stockUtilizable = Producto::queryStockUtilizablePorProducto();
        $query = Producto::query()
            ->leftJoinSub($stockUtilizable, 'existencias_utilizables', function ($join) {
                $join->on('productos.id', '=', 'existencias_utilizables.producto_id');
            })
            ->select('productos.*')
            ->selectRaw('COALESCE(existencias_utilizables.stock_utilizable, 0) as stock_utilizable')
            ->withSum('stock as stock_fisico', 'cantidad')
            ->with(['categoria', 'laboratorio']);

    // Búsqueda
    if ($request->filled('buscar')) {
        $buscar = $request->buscar;
        $query->where(function ($q) use ($buscar) {
            $q->where('nombre', 'like', "%{$buscar}%")
              ->orWhere('codigo_interno', 'like', "%{$buscar}%")
              ->orWhere('principio_activo', 'like', "%{$buscar}%");
        });
    }

    // Categoría
    if ($request->filled('categoria_id')) {
        $query->where('categoria_id', $request->categoria_id);
    }

    // Estado
    if ($request->estado === 'activos') {
        $query->where('activo', true);
    } elseif ($request->estado === 'inactivos') {
        $query->where('activo', false);
    } elseif ($request->estado === 'stock_critico') {
        $query->whereRaw('COALESCE(existencias_utilizables.stock_utilizable, 0) <= productos.stock_critico');
    }

    // Ordenamiento
    match ($request->orden) {
        'nombre_desc' => $query->orderBy('nombre', 'desc'),
        'reciente'    => $query->latest(),
        'stock_asc'   => $query->orderBy('stock_fisico'),
        'stock_desc'  => $query->orderByDesc('stock_fisico'),
        default       => $query->orderBy('nombre', 'asc'),
    };

    $productos = $query->paginate(20)->withQueryString();

    // Stats
    $stats = [
        'total'     => Producto::count(),
        'activos'   => Producto::where('activo', true)->count(),
        'inactivos' => Producto::where('activo', false)->count(),
        'critico'   => Producto::activos()
            ->leftJoinSub($stockUtilizable, 'existencias_utilizables', function ($join) {
                $join->on('productos.id', '=', 'existencias_utilizables.producto_id');
            })
            ->whereRaw('COALESCE(existencias_utilizables.stock_utilizable, 0) <= productos.stock_critico')
            ->count(),
    ];

    $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

    return view('productos.index', compact('productos', 'categorias', 'stats'));
}

    // ===================== CREAR (FORM) =====================
    public function create()
    {
        $categorias = Categoria::where('activo', true)->get();
        $laboratorios = Laboratorio::all();

        return view('productos.create', [
            'categorias' => $categorias,
            'laboratorios' => $laboratorios,
        ]);
    }

    // ===================== GUARDAR =====================
    public function store(Request $request)
    {
        // Validar
        $validated = $request->validate([
            'codigo_interno' => 'required|string|unique:productos',
            'codigo_barra' => 'nullable|string|unique:productos',
            'nombre' => 'required|string|max:150',
            'principio_activo' => 'nullable|string',
            'forma_farmaceutica' => 'nullable|string',
            'concentracion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'unidad_medida' => 'required|string',
            'condicion_almacenamiento' => 'required|in:ambiente,refrigerado',
            'tipo_receta' => 'required|in:ninguna,simple,retenida,cheque',
            'requiere_receta' => 'boolean',
            'es_controlado' => 'boolean',
            'registro_isp' => 'nullable|string',
            'stock_minimo' => 'required|integer|min:0',
            'stock_critico' => 'required|integer|min:0',
            'precio_base' => 'required|numeric|min:0',
        ]);

        // Crear
        $producto = Producto::create($validated);

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Producto creado exitosamente');
    }

    // ===================== VER =====================
    public function show(Producto $producto)
    {
        $producto->load('categoria', 'laboratorio', 'stock.ubicacion', 'stock.lote', 'precios.listaPrecio');

        $stock = $producto->obtenerStock();
        $stockUtilizable = $producto->getStockUtilizable();
        $estaBajo = $stockUtilizable <= $producto->stock_minimo;
        $estaCritico = $stockUtilizable <= $producto->stock_critico;
        $alertas = AlertaStock::where('producto_id', $producto->id)
            ->where('estado', 'activa')
            ->get();

        return view('productos.show', [
            'producto' => $producto,
            'stock' => $stock,
            'stockUtilizable' => $stockUtilizable,
            'estaBajo' => $estaBajo,
            'estaCritico' => $estaCritico,
            'alertas' => $alertas,
        ]);
    }

    // ===================== EDITAR (FORM) =====================
    public function edit(Producto $producto)
    {
        $categorias = Categoria::where('activo', true)->get();
        $laboratorios = Laboratorio::all();

        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => $categorias,
            'laboratorios' => $laboratorios,
        ]);
    }

    // ===================== ACTUALIZAR =====================
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'principio_activo' => 'nullable|string',
            'forma_farmaceutica' => 'nullable|string',
            'concentracion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'unidad_medida' => 'required|string',
            'condicion_almacenamiento' => 'required|in:ambiente,refrigerado',
            'tipo_receta' => 'required|in:ninguna,simple,retenida,cheque',
            'requiere_receta' => 'boolean',
            'es_controlado' => 'boolean',
            'stock_minimo' => 'required|integer|min:0',
            'stock_critico' => 'required|integer|min:0',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $producto->update($validated);

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Producto actualizado');
    }

    // ===================== ELIMINAR =====================
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado');
    }

    // ===================== BÚSQUEDA =====================
    public function search(Request $request)
    {
        $termino = $request->get('q', '');

        if (!$termino) {
            return response()->json(['data' => []]);
        }

        $productos = Producto::where('nombre', 'like', "%{$termino}%")
            ->orWhere('codigo_interno', 'like', "%{$termino}%")
            ->orWhere('codigo_barra', 'like', "%{$termino}%")
            ->activos()
            ->get();

        return response()->json(['data' => $productos]);
    }
}
