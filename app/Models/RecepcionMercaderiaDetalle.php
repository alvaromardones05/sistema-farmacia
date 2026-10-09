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
        'orden_compra_detalle_id',
        'producto_id',
        'lote_id',
        'cantidad_recibida',
        'precio_unitario',
        'numero_lote',
        'fecha_fabricacion',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'precio_unitario' => 'integer',
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function recepcion()
    {
        return $this->belongsTo(
            RecepcionMercaderia::class,
            'recepcion_mercaderia_id'
        );
    }

    public function ordenCompraDetalle()
    {
        return $this->belongsTo(
            OrdenCompraDetalle::class,
            'orden_compra_detalle_id'
        );
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