<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimientos_stock';

    protected $fillable = [
        'stock_id',
        'producto_id',
        'lote_id',
        'ubicacion_id',
        'tipo_movimiento',
        'cantidad_movida',
        'usuario_id',
        'referencia_documento',
        'motivo',
    ];

    // Relaciones
    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function libroControlIsp()
    {
        return $this->hasOne(LibroControlEstupefaciente::class, 'movimiento_stock_id');
    }
}