<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecepcionMercaderiaDetalle extends Model
{
    use HasFactory;

    protected $table = 'recepcion_mercaderia_detalle';

    protected $fillable = [
        'recepcion_mercaderia_id',
        'producto_id',
        'lote_id',
        'cantidad_recibida',
        'precio_unitario',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    // Relaciones
    public function recepcion()
    {
        return $this->belongsTo(RecepcionMercaderia::class, 'recepcion_mercaderia_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}