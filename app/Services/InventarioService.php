<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\MovimientoStock;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\UbicacionAlmacen;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InventarioService
{
    /**
     * Registrar entrada de stock
     */
    public function registrarEntrada(
        Producto $producto,
        Lote $lote,
        UbicacionAlmacen $ubicacion,
        int $cantidad,
        int $usuarioId,
        ?string $referencia = null,
        ?string $motivo = null
    ): MovimientoStock {
        // Obtener o crear registro de stock
        $stock = Stock::firstOrCreate(
            [
                'producto_id' => $producto->id,
                'lote_id' => $lote->id,
                'ubicacion_id' => $ubicacion->id,
            ],
            ['cantidad' => 0]
        );

        // Incrementar cantidad
        $stock->increment('cantidad', $cantidad);

        // Registrar movimiento
        return MovimientoStock::create([
            'stock_id' => $stock->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'tipo_movimiento' => 'entrada',
            'cantidad_movida' => $cantidad,
            'usuario_id' => $usuarioId,
            'referencia_documento' => $referencia,
            'motivo' => $motivo,
        ]);
    }

    /**
     * Registrar salida de stock
     */
    public function registrarSalida(
        Producto $producto,
        Lote $lote,
        UbicacionAlmacen $ubicacion,
        int $cantidad,
        int $usuarioId,
        string $tipoMovimiento = 'salida',
        ?string $referencia = null,
        ?string $motivo = null
    ): MovimientoStock {
        $stock = Stock::where('producto_id', $producto->id)
            ->where('lote_id', $lote->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->first();

        if (!$stock || $stock->cantidad < $cantidad) {
            throw new \Exception("Stock insuficiente para el producto {$producto->nombre}");
        }

        // Decrementar cantidad
        $stock->decrement('cantidad', $cantidad);

        // Registrar movimiento
        return MovimientoStock::create([
            'stock_id' => $stock->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad_movida' => $cantidad,
            'usuario_id' => $usuarioId,
            'referencia_documento' => $referencia,
            'motivo' => $motivo,
        ]);
    }

    /**
     * Transferir stock entre ubicaciones
     */
    public function transferir(
        Producto $producto,
        Lote $lote,
        UbicacionAlmacen $origenUbicacion,
        UbicacionAlmacen $destinoUbicacion,
        int $cantidad,
        int $usuarioId,
        ?string $motivo = null
    ): void {
        // Validar stock en origen
        $stockOrigen = Stock::where('producto_id', $producto->id)
            ->where('lote_id', $lote->id)
            ->where('ubicacion_id', $origenUbicacion->id)
            ->first();

        if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
            throw new \Exception("Stock insuficiente en ubicación de origen");
        }

        // Restar de origen
        $this->registrarSalida(
            $producto,
            $lote,
            $origenUbicacion,
            $cantidad,
            $usuarioId,
            'transferencia',
            null,
            $motivo
        );

        // Sumar a destino
        $this->registrarEntrada(
            $producto,
            $lote,
            $destinoUbicacion,
            $cantidad,
            $usuarioId,
            null,
            $motivo
        );
    }

    /**
     * Obtener stock disponible por producto
     */
    public function obtenerStockDisponible(Producto $producto): int
    {
        return Stock::where('producto_id', $producto->id)
            ->sum('cantidad');
    }

    /**
     * Obtener detalles de stock por ubicación
     */
    public function obtenerStockPorUbicacion(Producto $producto)
    {
        return Stock::where('producto_id', $producto->id)
            ->with('ubicacion', 'lote')
            ->get();
    }

    /**
     * Actualizar estado de lote (FEFO)
     */
    public function actualizarEstadoLote(Lote $lote): void
    {
        if ($lote->estaVencido()) {
            $lote->update(['estado' => 'vencido']);
            return;
        }

        $stockTotal = Stock::where('lote_id', $lote->id)->sum('cantidad');
        if ($stockTotal === 0) {
            $lote->update(['estado' => 'agotado']);
            return;
        }

        $lote->update(['estado' => 'activo']);
    }

    /**
     * Registrar ajuste de inventario
     */
    public function registrarAjuste(
        Producto $producto,
        Lote $lote,
        UbicacionAlmacen $ubicacion,
        int $diferencia,
        int $usuarioId,
        string $motivo
    ): MovimientoStock {
        if ($diferencia > 0) {
            return $this->registrarEntrada(
                $producto,
                $lote,
                $ubicacion,
                $diferencia,
                $usuarioId,
                null,
                "Ajuste: {$motivo}"
            );
        }

        return $this->registrarSalida(
            $producto,
            $lote,
            $ubicacion,
            abs($diferencia),
            $usuarioId,
            'ajuste',
            null,
            "Ajuste: {$motivo}"
        );
    }
}