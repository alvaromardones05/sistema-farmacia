<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecepcionMercaderia extends Model
{
    use HasFactory;

    protected $table = 'recepciones_mercaderia';

    protected $fillable = [
        'orden_compra_id',
        'usuario_id',
        'revisado_por',
        'fecha_recepcion',
        'fecha_revision',
        'numero_comprobante',
        'estado',
        'tipo_recepcion',
        'motivo_rechazo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function detalles()
    {
        return $this->hasMany(
            RecepcionMercaderiaDetalle::class,
            'recepcion_mercaderia_id'
        );
    }
}