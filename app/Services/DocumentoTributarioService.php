<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DocumentoTributario;

class DocumentoTributarioService
{
    /**
     * Crear boleta electrónica (simulada)
     */
    public function crearBoletaSimulada(Venta $venta): DocumentoTributario
    {
        return DocumentoTributario::create([
            'venta_id' => $venta->id,
            'tipo' => 'boleta_electronica',
            'folio' => $this->generarFolio(),
            'fecha_emision' => now(),
            'pdf_path' => null,
            'estado_sii' => 'simulado',
            'track_id' => null,
        ]);
    }

    /**
     * Generar folio único
     */
    private function generarFolio(): int
    {
        $ultimoDoc = DocumentoTributario::latest('folio')->first();
        return $ultimoDoc ? $ultimoDoc->folio + 1 : 1000000;
    }

    /**
     * Marcar como aceptado por SII (simulado)
     */
    public function aceptarPorSII(DocumentoTributario $doc): DocumentoTributario
    {
        return $doc->update([
            'estado_sii' => 'aceptado',
            'track_id' => 'TRACK-' . uniqid(),
        ]) ? $doc->refresh() : $doc;
    }
}