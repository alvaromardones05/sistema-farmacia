<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canje extends Model
{
    protected $table = 'canjes';

    protected $fillable = [
        'producto_id',
        'lote_original_id',
        'ubicacion_origen_id',
        'movimiento_salida_id',
        'movimiento_entrada_id',
        'cantidad_solicitada',
        'solicitado_por',
        'fecha_solicitud',
        'motivo_solicitud',
        'idempotency_token',
        'estado',
        'decidido_por',
        'fecha_decision',
        'motivo_decision',
        'cerrado_por',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_decision' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }

    public function loteOriginal()
    {
        return $this->belongsTo(Lote::class, 'lote_original_id');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(UbicacionAlmacen::class, 'ubicacion_origen_id');
    }

    public function movimientoSalida()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_salida_id');
    }

    public function movimientoEntrada()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_entrada_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function decisor()
    {
        return $this->belongsTo(User::class, 'decidido_por');
    }

    public function cerrador()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function entregas()
    {
        return $this->hasMany(CanjeEntrega::class, 'canje_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(CanjeDevolucion::class, 'canje_id');
    }

    public function reposiciones()
    {
        return $this->hasMany(CanjeReposicion::class, 'canje_id');
    }
}