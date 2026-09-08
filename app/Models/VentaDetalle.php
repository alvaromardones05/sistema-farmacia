<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalle';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'lote_id',
        'receta_detalle_id',
        'cantidad',
        'precio_unitario',
        'descuento_pct',
        'subtotal',
        'impuesto',
        'total',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function recetaDetalle()
    {
        return $this->belongsTo(RecetaDetalle::class, 'receta_detalle_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(DevolucionDetalle::class, 'venta_detalle_id');
    }

    // Métodos
    public function calcularTotales(): void
    {
        $this->subtotal = $this->precio_unitario * $this->cantidad * (1 - ($this->descuento_pct / 100));
        $this->impuesto = $this->subtotal * 0.19;
        $this->total = $this->subtotal + $this->impuesto;
        $this->save();
    }


     //Calcular totales del detalle
     
    public function calcularTotales(): void
    {
        $this->subtotal = $this->precio_unitario * $this->cantidad * (1 - ($this->descuento_pct / 100));
        $this->impuesto = $this->subtotal * 0.19;
        $this->total = $this->subtotal + $this->impuesto;
        $this->save();
    }
}