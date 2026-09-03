<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AperturaCaja extends Model
{
    use HasFactory;

    protected $table = 'aperturas_caja';

    protected $fillable = [
        'caja_id',
        'usuario_id',
        'fecha_apertura',
        'saldo_inicial',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'saldo_inicial' => 'decimal:2',
    ];

    // Relaciones
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'apertura_caja_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'apertura_caja_id');
    }

    public function cierre()
    {
        return $this->hasOne(CierreCaja::class, 'apertura_caja_id');
    }

    // Scopes
    public function scopeAbiertas($query)
    {
        return $query->where('estado', 'abierta');
    }
}