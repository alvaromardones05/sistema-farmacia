<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanjeDevolucion extends Model
{
    protected $table = 'canje_devoluciones';

    protected $fillable = [
        'canje_id',
        'canje_entrega_id',
        'movimiento_stock_id',
        'cantidad',
        'fecha_devolucion',
        'usuario_id',
        'proveedor_id',
        'numero_documento',
        'documento_key',
        'idempotency_token',
    ];

    protected $casts = ['fecha_devolucion' => 'datetime'];

    public function canje()
    {
        return $this->belongsTo(Canje::class, 'canje_id');
    }

    public function entrega()
    {
        return $this->belongsTo(CanjeEntrega::class, 'canje_entrega_id');
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
}