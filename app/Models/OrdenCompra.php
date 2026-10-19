<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'numero_orden',
        'proveedor_id',
        'fecha_orden',
        'fecha_entrega_esperada',
        'estado',
        'usuario_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_orden' => 'datetime',
        'fecha_entrega_esperada' => 'datetime',
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(OrdenCompraDetalle::class, 'orden_compra_id');
    }

    public function recepciones()
    {
        return $this->hasMany(RecepcionMercaderia::class, 'orden_compra_id');
    }

    public function puedeRecibirMercaderia(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada', 'parcial'])
            && !$this->recepciones()->where('estado', 'pendiente')->exists()
            && $this->detalles()
                ->where('estado_linea', '!=', 'cancelada')
                ->whereColumn('cantidad_recibida', '<', 'cantidad_solicitada')
                ->exists();
    }
}
