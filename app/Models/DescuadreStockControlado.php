<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescuadreStockControlado extends Model
{
    use HasFactory;

    protected $table = 'descuadres_stock_controlado';

    protected $fillable = [
        'producto_id',
        'lote_id',
        'ubicacion_id',
        'cantidad_esperada',
        'cantidad_contada',
        'diferencia',
        'fecha_inventario',
        'usuario_id',
        'revisado_por',
        'estado',
        'fecha_resolucion',
        'explicacion',
        'observacion_revision',
    ];

    protected $casts = [
        'fecha_inventario' => 'datetime',
        'fecha_resolucion' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(UbicacionAlmacen::class, 'ubicacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['registrado', 'investigado']);
    }
}