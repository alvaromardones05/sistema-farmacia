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
    public function index()
    {
        $productos = Producto::with('categoria', 'laboratorio')
            ->activos()
            ->paginate(15);

        return view('productos.index', [
            'productos' => $productos,
        ]);
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
            'tipo_receta' => 'required|in:ninguna,simple,retenida,cheque',
            'es_controlado' => 'boolean',
            'registro_isp' => 'nullable|string',
            'stock_minimo' => 'required|integer|min:0',
            'stock_critico' => 'required|integer|min:0',
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
        $estaBajo = $producto->estaBajoStock();
        $estaCritico = $producto->estaEnStockCritico();
        $alertas = AlertaStock::where('producto_id', $producto->id)
            ->where('estado', 'activa')
            ->get();

        return view('productos.show', [
            'producto' => $producto,
            'stock' => $stock,
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
            'tipo_receta' => 'required|in:ninguna,simple,retenida,cheque',
            'es_controlado' => 'boolean',
            'stock_minimo' => 'required|integer|min:0',
            'stock_critico' => 'required|integer|min:0',
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