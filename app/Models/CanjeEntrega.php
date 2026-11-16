<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanjeEntrega extends Model
{
    protected $table = 'canje_entregas';

    protected $fillable = [
        'canje_id',
        'movimiento_stock_id',
        'cantidad',
        'cantidad_enviada',
        'cantidad_aceptada',
        'cantidad_rechazada',
        'fecha_entrega',
        'usuario_id',
        'proveedor_id',
        'numero_documento',
        'documento_key',
        'idempotency_token',
    ];

    protected $casts = ['fecha_entrega' => 'datetime'];

    public function canje()
    {
        return $this->belongsTo(Canje::class, 'canje_id');
    }

    public function movimiento()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_stock_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(CanjeDevolucion::class, 'canje_entrega_id');
    }

    public function respuestas()
    {
        return $this->hasMany(CanjeRespuestaEntrega::class, 'canje_entrega_id');
    }
}