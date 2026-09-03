<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionAlmacen extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones_almacen';

    protected $fillable = [
        'nombre',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function stock()
    {
        return $this->hasMany(Stock::class, 'ubicacion_id');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'ubicacion_id');
    }
}