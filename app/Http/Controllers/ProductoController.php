<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    protected ProductoService $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    public function index()
    {
        return view('productos.index', [
            'productos' => $this->productoService->listar(15),
        ]);
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_interno' => 'required|string|unique:productos',
            'nombre' => 'required|string|max:150',
            'categoria_id' => 'required|exists:categorias_producto,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'principio_activo' => 'nullable|string',
            'forma_farmaceutica' => 'nullable|string',
            'concentracion' => 'nullable|string',
            'unidad_medida' => 'required|string',
            'tipo_receta' => 'required|in:ninguna,simple,retenida,cheque',
            'es_controlado' => 'boolean',
            'stock_minimo' => 'required|integer|min:0',
            'stock_critico' => 'required|integer|min:0',
        ]);

        $producto = $this->productoService->crear($validated);

        return redirect()->route('productos.show', $producto)->with('success', 'Producto creado exitosamente');
    }

    public function show(Producto $producto)
    {
        $producto = $this->productoService->obtenerConRelaciones($producto->id);
        $stock = $this->productoService->obtenerStock($producto);
        $alertas = $this->productoService->obtenerAlertas($producto);

        return view('productos.show', [
            'producto' => $producto,
            'stock' => $stock,
            'alertas' => $alertas,
        ]);
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', ['producto' => $producto]);
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'principio_activo' => 'nullable|string',
            'forma_farmaceutica' => 'nullable|string',
            'stock_minimo' => 'required|integer|min:0',
            'stock_critico' => 'required|integer|min:0',
        ]);

        $this->productoService->actualizar($producto, $validated);

        return redirect()->route('productos.show', $producto)->with('success', 'Producto actualizado');
    }

    public function search(Request $request)
    {
        $termino = $request->get('q', '');
        $productos = $termino ? $this->productoService->buscar($termino) : [];

        return response()->json(['data' => $productos]);
    }
}