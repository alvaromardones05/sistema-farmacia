<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPrecio extends Model
{
    use HasFactory;

    protected $table = 'producto_precios';

    protected $fillable = [
        'producto_id',
        'lista_precio_id',
        'precio_neto',
        'impuesto_pct',
        'precio_venta',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected $casts = [
        'precio_neto' => 'decimal:2',
        'impuesto_pct' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function listaPrecio()
    {
        return $this->belongsTo(ListaPrecio::class, 'lista_precio_id');
    }

    // Scopes
    public function scopeVigentes($query)
    {
        return $query->whereDate('vigente_desde', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigente_hasta')
                    ->orWhereDate('vigente_hasta', '>=', now());
            });
    }
}