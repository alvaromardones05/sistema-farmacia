<?php

namespace App\Services;

use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Producto;

class RecetaService
{
    /**
     * Crear receta
     */
    public function crearReceta(
        Paciente $paciente,
        Medico $medico,
        string $tipo = 'simple',
        ?string $observaciones = null
    ): Receta {
        return Receta::create([
            'numero_receta' => $this->generarNumeroReceta(),
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'tipo' => $tipo,
            'estado' => 'vigente',
            'observaciones' => $observaciones,
        ]);
    }

    /**
     * Agregar medicamento a receta
     */
    public function agregarMedicamento(
        Receta $receta,
        Producto $producto,
        int $cantidad,
        string $unidadMedida,
        ?string $instrucciones = null
    ): RecetaDetalle {
        return RecetaDetalle::create([
            'receta_id' => $receta->id,
            'producto_id' => $producto->id,
            'cantidad_prescrita' => $cantidad,
            'unidad_medida' => $unidadMedida,
            'instrucciones' => $instrucciones,
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Marcar medicamento como dispensado
     */
    public function dispensarMedicamento(RecetaDetalle $detalle, int $cantidad): RecetaDetalle
    {
        $detalle->cantidad_dispensada += $cantidad;

        if ($detalle->cantidad_dispensada >= $detalle->cantidad_prescrita) {
            $detalle->estado = 'dispensado';
        } else {
            $detalle->estado = 'parcial';
        }

        $detalle->save();
        return $detalle;
    }

    /**
     * Generar número de receta único
     */
    private function generarNumeroReceta(): string
    {
        $ultimaReceta = Receta::latest('id')->first();
        $numero = $ultimaReceta ? $ultimaReceta->id + 1 : 1;
        return str_pad($numero, 10, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si receta es válida
     */
    public function esValida(Receta $receta): bool
    {
        return $receta->estaVigente() && !$receta->estaVencida();
    }

    /**
     * Obtener recetas vigentes del paciente
     */
    public function obtenerRecetasVigentes(Paciente $paciente)
    {
        return Receta::where('paciente_id', $paciente->id)
            ->vigentes()
            ->with('detalles.producto')
            ->get();
    }
}