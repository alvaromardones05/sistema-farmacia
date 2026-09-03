<?php

namespace App\Services;

use App\Models\LibroControlEstupefaciente;
use App\Models\CorrellativoLibro;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\MovimientoStock;
use Illuminate\Database\Eloquent\Collection;

class ControlEstupefacientesService
{
    /**
     * Registrar entrada de controlado en el libro ISP
     */
    public function registrarEntrada(
        Producto $producto,
        Lote $lote,
        MovimientoStock $movimiento,
        int $usuarioISPId,
        ?string $observaciones = null
    ): LibroControlEstupefaciente {
        if (!$producto->es_controlado) {
            throw new \Exception("Producto no es controlado");
        }

        $folio = CorrellativoLibro::obtenerYIncrementarFolio('entrada');

        return LibroControlEstupefaciente::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'movimiento_stock_id' => $movimiento->id,
            'tipo_libro' => 'entrada',
            'numero_folio' => $folio,
            'fecha_registro' => now(),
            'cantidad_movida' => $movimiento->cantidad_movida,
            'usuario_isp_id' => $usuarioISPId,
            'observaciones' => $observaciones,
            'editable' => false,
        ]);
    }

    /**
     * Registrar salida de controlado en el libro ISP
     */
    public function registrarSalida(
        Producto $producto,
        Lote $lote,
        MovimientoStock $movimiento,
        int $usuarioISPId,
        ?string $observaciones = null
    ): LibroControlEstupefaciente {
        if (!$producto->es_controlado) {
            throw new \Exception("Producto no es controlado");
        }

        $folio = CorrellativoLibro::obtenerYIncrementarFolio('salida');

        return LibroControlEstupefaciente::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'movimiento_stock_id' => $movimiento->id,
            'tipo_libro' => 'salida',
            'numero_folio' => $folio,
            'fecha_registro' => now(),
            'cantidad_movida' => $movimiento->cantidad_movida,
            'usuario_isp_id' => $usuarioISPId,
            'observaciones' => $observaciones,
            'editable' => false,
        ]);
    }

    /**
     * Obtener saldo de producto controlado
     */
    public function obtenerSaldo(Producto $producto, ?Lote $lote = null)
    {
        $query = LibroControlEstupefaciente::where('producto_id', $producto->id);

        if ($lote) {
            $query->where('lote_id', $lote->id);
        }

        $entradas = $query->entradas()->sum('cantidad_movida');
        $salidas = $query->salidas()->sum('cantidad_movida');

        return $entradas - $salidas;
    }

    /**
     * Obtener historial de movimientos de un controlado
     */
    public function obtenerHistorial(Producto $producto, ?Lote $lote = null): Collection
    {
        $query = LibroControlEstupefaciente::where('producto_id', $producto->id)
            ->with('lote', 'usuarioISP', 'movimientoStock');

        if ($lote) {
            $query->where('lote_id', $lote->id);
        }

        return $query->orderBy('numero_folio')->get();
    }

    /**
     * Verificar que no hay saltos en la numeración
     */
    public function verificarIntegridad(string $tipoLibro): bool
    {
        $registros = LibroControlEstupefaciente::where('tipo_libro', $tipoLibro)
            ->orderBy('numero_folio')
            ->get();

        $folioEsperado = 1;
        foreach ($registros as $registro) {
            if ($registro->numero_folio !== $folioEsperado) {
                return false;
            }
            $folioEsperado++;
        }

        return true;
    }

    /**
     * Generar reporte ISP
     */
    public function generarReporteISP($fechaInicio, $fechaFin)
    {
        return LibroControlEstupefaciente::whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->with('producto', 'lote', 'usuarioISP')
            ->orderBy('numero_folio')
            ->get()
            ->groupBy('tipo_libro');
    }
}