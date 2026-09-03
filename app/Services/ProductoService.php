<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Stock;
use App\Models\Lote;
use App\Models\AlertaStock;
use Illuminate\Pagination\Paginator;

class ProductoService
{
    /**
     * Crear un nuevo producto
     */
    public function crear(array $datos): Producto
    {
        return Producto::create($datos);
    }

    /**
     * Actualizar producto
     */
    public function actualizar(Producto $producto, array $datos): Producto
    {
        $producto->update($datos);
        return $producto;
    }

    /**
     * Obtener producto con relaciones
     */
    public function obtenerConRelaciones(int $id)
    {
        return Producto::with([
            'categoria',
            'laboratorio',
            'stock.ubicacion',
            'lotes',
            'precios.listaPrecio'
        ])->findOrFail($id);
    }

    /**
     * Listar productos paginados
     */
    public function listar(int $perPage = 15)
    {
        return Producto::with('categoria', 'laboratorio')
            ->activos()
            ->paginate($perPage);
    }

    /**
     * Buscar productos
     */
    public function buscar(string $termino)
    {
        return Producto::where('nombre', 'like', "%{$termino}%")
            ->orWhere('codigo_interno', 'like', "%{$termino}%")
            ->orWhere('codigo_barra', 'like', "%{$termino}%")
            ->activos()
            ->get();
    }

    /**
     * Obtener stock actual del producto
     */
    public function obtenerStock(Producto $producto): int
    {
        return Stock::where('producto_id', $producto->id)->sum('cantidad');
    }

    /**
     * Verificar si está bajo de stock
     */
    public function estaBajoStock(Producto $producto): bool
    {
        $stockActual = $this->obtenerStock($producto);
        return $stockActual <= $producto->stock_minimo;
    }

    /**
     * Verificar si está en stock crítico
     */
    public function estaEnStockCritico(Producto $producto): bool
    {
        $stockActual = $this->obtenerStock($producto);
        return $stockActual <= $producto->stock_critico;
    }

    /**
     * Generar alertas de stock
     */
    public function generarAlertasStock(Producto $producto): void
    {
        $stockActual = $this->obtenerStock($producto);

        // Eliminar alertas previas
        AlertaStock::where('producto_id', $producto->id)
            ->where('estado', 'activa')
            ->delete();

        if ($stockActual <= $producto->stock_critico) {
            AlertaStock::create([
                'producto_id' => $producto->id,
                'tipo_alerta' => 'stock_critico',
                'cantidad_actual' => $stockActual,
                'estado' => 'activa',
            ]);
            return;
        }

        if ($stockActual <= $producto->stock_minimo) {
            AlertaStock::create([
                'producto_id' => $producto->id,
                'tipo_alerta' => 'stock_bajo',
                'cantidad_actual' => $stockActual,
                'estado' => 'activa',
            ]);
        }
    }

    /**
     * Obtener próximo lote (FEFO)
     */
    public function obtenerProximoLoteFEFO(Producto $producto)
    {
        return Lote::where('producto_id', $producto->id)
            ->where('estado', 'activo')
            ->orderBy('fecha_vencimiento')
            ->first();
    }

    /**
     * Obtener alertas activas
     */
    public function obtenerAlertas(Producto $producto)
    {
        return AlertaStock::where('producto_id', $producto->id)
            ->where('estado', 'activa')
            ->get();
    }

    /**
     * Desactivar producto
     */
    public function desactivar(Producto $producto): Producto
    {
        return $this->actualizar($producto, ['activo' => false]);
    }
}