<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\ListaPrecio;

class PrecioService
{
    /**
     * Obtener precio vigente de un producto
     */
    public function obtenerPrecioVigente(
        Producto $producto,
        ?int $listaId = null
    ): ?ProductoPrecio {
        $query = $producto->precios()
            ->vigentes();

        if ($listaId) {
            $query->where('lista_precio_id', $listaId);
        }

        return $query->latest('vigente_desde')->first();
    }

    /**
     * Crear nuevo precio
     */
    public function crearPrecio(
        Producto $producto,
        int $listaId,
        float $precioNeto,
        float $impuestoPct = 19.0
    ): ProductoPrecio {
        $precioVenta = $precioNeto * (1 + ($impuestoPct / 100));

        return ProductoPrecio::create([
            'producto_id' => $producto->id,
            'lista_precio_id' => $listaId,
            'precio_neto' => $precioNeto,
            'impuesto_pct' => $impuestoPct,
            'precio_venta' => $precioVenta,
            'vigente_desde' => now(),
        ]);
    }

    /**
     * Actualizar precio (crear nuevo registro con fecha vigente)
     */
    public function actualizarPrecio(
        Producto $producto,
        int $listaId,
        float $precioNeto,
        float $impuestoPct = 19.0
    ): ProductoPrecio {
        // Finalizar precio anterior
        $precioPrevio = $this->obtenerPrecioVigente($producto, $listaId);
        if ($precioPrevio) {
            $precioPrevio->update(['vigente_hasta' => now()->subSecond()]);
        }

        // Crear nuevo precio
        return $this->crearPrecio($producto, $listaId, $precioNeto, $impuestoPct);
    }

    /**
     * Obtener historial de precios
     */
    public function obtenerHistorial(Producto $producto)
    {
        return $producto->precios()
            ->orderBy('vigente_desde', 'desc')
            ->get();
    }

    /**
     * Comparar precio entre listas
     */
    public function compararPrecios(Producto $producto)
    {
        $listas = ListaPrecio::activas()->vigentes()->get();
        $comparacion = [];

        foreach ($listas as $lista) {
            $precio = $this->obtenerPrecioVigente($producto, $lista->id);
            $comparacion[$lista->nombre] = $precio?->precio_venta ?? null;
        }

        return $comparacion;
    }
}