<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanjeReposicion extends Model
{
    protected $table = 'canje_reposiciones';

    protected $fillable = [
        'canje_id',
        'movimiento_stock_id',
        'lote_id',
        'cantidad',
        'fecha_recepcion',
        'usuario_id',
        'proveedor_id',
        'numero_documento',
        'documento_key',
        'idempotency_token',
    ];

    protected $casts = ['fecha_recepcion' => 'datetime'];

    public function canje()
    {
        return $this->belongsTo(Canje::class, 'canje_id');
    }

    public function movimiento()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_stock_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
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