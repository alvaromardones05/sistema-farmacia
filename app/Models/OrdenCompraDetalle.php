<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompraDetalle extends Model
{
    use HasFactory;

    protected $table = 'orden_compra_detalle';

    protected $fillable = [
        'orden_compra_id',
        'producto_id',
        'cantidad_solicitada',
        'precio_unitario',
        'cantidad_recibida',
        'estado_linea',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    // Relaciones
    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}