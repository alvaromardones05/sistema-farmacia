<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaPrecio extends Model
{
    use HasFactory;

    protected $table = 'listas_precios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'vigente_desde',
        'vigente_hasta',
        'activa',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
        'activa' => 'boolean',
    ];

    // Relaciones
    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class, 'lista_precio_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeVigentes($query)
    {
        return $query->whereDate('vigente_desde', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigente_hasta')
                    ->orWhereDate('vigente_hasta', '>=', now());
            });
    }
}