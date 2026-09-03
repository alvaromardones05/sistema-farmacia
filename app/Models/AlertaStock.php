<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertaStock extends Model
{
    use HasFactory;

    protected $table = 'alertas_stock';

    protected $fillable = [
        'producto_id',
        'tipo_alerta',
        'cantidad_actual',
        'estado',
        'observaciones',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}