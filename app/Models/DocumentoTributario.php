<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoTributario extends Model
{
    use HasFactory;

    protected $table = 'documentos_tributarios';

    protected $fillable = [
        'venta_id',
        'tipo',
        'folio',
        'fecha_emision',
        'pdf_path',
        'estado_sii',
        'track_id',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    // Métodos
    public function esBoleta(): bool
    {
        return $this->tipo === 'boleta_electronica';
    }

    public function esFactura(): bool
    {
        return $this->tipo === 'factura_electronica';
    }

    public function esSimulada(): bool
    {
        return $this->estado_sii === 'simulado';
    }
}