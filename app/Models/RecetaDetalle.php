<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaDetalle extends Model
{
    use HasFactory;

    protected $table = 'receta_detalle';

    protected $fillable = [
        'receta_id',
        'producto_id',
        'cantidad_prescrita',
        'unidad_medida',
        'cantidad_dispensada',
        'instrucciones',
        'estado',
    ];

    // Relaciones
    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function ventasDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'receta_detalle_id');
    }


    //Marcar medicamento como dispensado
     
    public function dispensar(int $cantidad): void
    {
        $this->cantidad_dispensada += $cantidad;

        if ($this->cantidad_dispensada >= $this->cantidad_prescrita) {
            $this->estado = 'dispensado';
        } else {
            $this->estado = 'parcial';
        }

        $this->save();
    }

}