<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'producto_id',
        'lote_id',
        'ubicacion_id',
        'cantidad',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(UbicacionAlmacen::class, 'ubicacion_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoStock::class, 'stock_id');
    }
}