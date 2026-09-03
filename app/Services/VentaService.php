<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\AperturaCaja;
use App\Models\ProductoPrecio;
use Illuminate\Database\Eloquent\Collection;

class VentaService
{
    protected InventarioService $inventarioService;
    protected PrecioService $precioService;

    public function __construct(
        InventarioService $inventarioService,
        PrecioService $precioService
    ) {
        $this->inventarioService = $inventarioService;
        $this->precioService = $precioService;
    }

    /**
     * Crear una venta
     */
    public function crearVenta(
        Paciente $paciente,
        AperturaCaja $aperturaCaja,
        int $usuarioId,
        ?int $convenioId = null,
        ?string $observaciones = null
    ): Venta {
        return Venta::create([
            'numero_venta' => $this->generarNumeroVenta(),
            'paciente_id' => $paciente->id,
            'usuario_id' => $usuarioId,
            'apertura_caja_id' => $aperturaCaja->id,
            'convenio_id' => $convenioId,
            'fecha_venta' => now(),
            'estado' => 'pendiente',
            'observaciones' => $observaciones,
        ]);
    }

    /**
     * Agregar detalle a la venta
     */
    public function agregarDetalle(
        Venta $venta,
        Producto $producto,
        Lote $lote,
        int $cantidad,
        float $descuentoPct = 0,
        ?int $recetaDetalleId = null
    ): VentaDetalle {
        // Obtener precio
        $precioPrecio = $this->precioService->obtenerPrecioVigente($producto, $venta->convenio_id);
        if (!$precioPrecio) {
            throw new \Exception("No hay precio vigente para {$producto->nombre}");
        }

        // Calcular subtotal
        $subtotal = $precioPrecio->precio_neto * $cantidad * (1 - ($descuentoPct / 100));
        $impuesto = $subtotal * 0.19;
        $total = $subtotal + $impuesto;

        return VentaDetalle::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'receta_detalle_id' => $recetaDetalleId,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioPrecio->precio_neto,
            'descuento_pct' => $descuentoPct,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
        ]);
    }

    /**
     * Procesar (completar) la venta
     */
    public function procesarVenta(Venta $venta): Venta
    {
        // Procesar cada detalle
        foreach ($venta->detalles as $detalle) {
            // Salir del inventario
            $this->inventarioService->registrarSalida(
                $detalle->producto,
                $detalle->lote,
                $venta->aperturaCaja->caja->ubicaciones->first(),
                $detalle->cantidad,
                $venta->usuario_id,
                'salida',
                "Venta #{$venta->numero_venta}"
            );
        }

        // Recalcular totales
        $venta->calcularTotales();

        // Cambiar estado
        $venta->update(['estado' => 'completada']);

        return $venta;
    }

    /**
     * Anular venta
     */
    public function anularVenta(Venta $venta, string $motivo): Venta
    {
        // Devolver stock
        foreach ($venta->detalles as $detalle) {
            $this->inventarioService->registrarEntrada(
                $detalle->producto,
                $detalle->lote,
                $venta->aperturaCaja->caja->ubicaciones->first(),
                $detalle->cantidad,
                auth()->id(),
                "Anulación: {$venta->numero_venta}",
                $motivo
            );
        }

        return $venta->update(['estado' => 'anulada']) ? $venta : $venta;
    }

    /**
     * Generar número de venta único
     */
    private function generarNumeroVenta(): string
    {
        $ultimaVenta = Venta::latest('id')->first();
        $numero = $ultimaVenta ? $ultimaVenta->id + 1 : 1;
        return str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener ventas del día
     */
    public function obtenerVentasDelDia()
    {
        return Venta::whereDate('fecha_venta', today())
            ->completadas()
            ->with('paciente', 'usuario', 'detalles.producto')
            ->get();
    }

    /**
     * Obtener ventas por rango de fecha
     */
    public function obtenerVentasPorRango($fechaInicio, $fechaFin)
    {
        return Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->completadas()
            ->with('paciente', 'usuario', 'detalles')
            ->get();
    }
}