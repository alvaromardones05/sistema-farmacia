<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaRetenida extends Model
{
    use HasFactory;

    protected $table = 'recetas_retenidas';

    protected $fillable = [
        'receta_id',
        'venta_id',
        'fecha_retencion',
        'razon_retencion',
        'estado',
    ];

    protected $casts = [
        'fecha_retencion' => 'datetime',
    ];

    // Relaciones
    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    // Scopes
    public function scopeRetenidas($query)
    {
        return $query->where('estado', 'retenida');
    }

    public function scopeDispensadas($query)
    {
        return $query->where('estado', 'dispensada');
    }
}