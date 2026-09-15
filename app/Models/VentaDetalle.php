<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    // Nombre de la tabla utilizada por este modelo
    protected $table = 'venta_detalle';

    // Campos que pueden ser asignados mediante create() o fill()
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

    // Define el formato de los valores numéricos
    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación con la venta a la que pertenece este detalle
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    // Relación con el producto vendido
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Relación con el lote utilizado en la venta
    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    // Relación con el detalle de receta, cuando corresponde
    public function recetaDetalle()
    {
        return $this->belongsTo(RecetaDetalle::class, 'receta_detalle_id');
    }

    // Relación con las devoluciones asociadas al detalle
    public function devoluciones()
    {
        return $this->hasMany(DevolucionDetalle::class, 'venta_detalle_id');
    }

    // Calcula los valores económicos del detalle de la venta
    public function calcularTotales(): void
    {
        // Calcula el subtotal considerando la cantidad y el descuento
        $this->subtotal = $this->precio_unitario
            * $this->cantidad
            * (1 - ($this->descuento_pct / 100));

        // Calcula el impuesto correspondiente al 19% de IVA
        $this->impuesto = $this->subtotal * 0.19;

        // Calcula el total final sumando subtotal e impuesto
        $this->total = $this->subtotal + $this->impuesto;

        // Guarda los valores calculados en la base de datos
        $this->save();
    }
}