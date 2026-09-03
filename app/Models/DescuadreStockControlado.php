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
        'cantidad_esperada',
        'cantidad_contada',
        'diferencia',
        'fecha_inventario',
        'usuario_id',
        'estado',
        'explicacion',
    ];

    protected $casts = [
        'fecha_inventario' => 'datetime',
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopeNoResueltos($query)
    {
        return $query->whereIn('estado', ['registrado', 'investigado']);
    }

    public function scopeResueltos($query)
    {
        return $query->where('estado', 'resuelto');
    }
}