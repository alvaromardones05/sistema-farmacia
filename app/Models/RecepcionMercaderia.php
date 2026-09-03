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
        'fecha_recepcion',
        'numero_comprobante',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
    ];

    // Relaciones
    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(RecepcionMercaderiaDetalle::class, 'recepcion_mercaderia_id');
    }
}