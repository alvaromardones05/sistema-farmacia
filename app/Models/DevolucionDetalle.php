<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucionDetalle extends Model
{
    use HasFactory;

    protected $table = 'devolucion_detalle';

    protected $fillable = [
        'devolucion_id',
        'venta_detalle_id',
        'cantidad_devuelta',
        'monto_devuelto',
        'retorna_a_stock',
    ];

    protected $casts = [
        'monto_devuelto' => 'decimal:2',
        'retorna_a_stock' => 'boolean',
    ];

    // Relaciones
    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'devolucion_id');
    }

    public function ventaDetalle()
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }
}