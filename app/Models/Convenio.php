<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    use HasFactory;

    protected $table = 'convenios';

    protected $fillable = [
        'nombre',
        'codigo_convenio',
        'tipo',
        'descuento_pct',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'descuento_pct' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'convenio_id');
    }

    public function coberturas()
    {
        return $this->hasMany(
            ConvenioCoberturaProducto::class,
            'convenio_id'
        );
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'convenio_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}