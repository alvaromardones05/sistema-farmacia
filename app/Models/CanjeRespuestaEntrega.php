<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanjeRespuestaEntrega extends Model
{
    protected $table = 'canje_respuestas_entrega';

    protected $fillable = [
        'canje_id',
        'canje_entrega_id',
        'cantidad_aceptada',
        'cantidad_rechazada',
        'fecha_respuesta',
        'usuario_id',
        'proveedor_id',
        'numero_documento',
        'documento_key',
        'idempotency_token',
    ];

    protected $casts = ['fecha_respuesta' => 'datetime'];

    public function canje()
    {
        return $this->belongsTo(Canje::class, 'canje_id');
    }

    public function entrega()
    {
        return $this->belongsTo(CanjeEntrega::class, 'canje_entrega_id');
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