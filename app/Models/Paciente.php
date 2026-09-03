<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pacientes';

    protected $fillable = [
        'rut',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'convenio_id',
        'numero_poliza',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'paciente_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'paciente_id');
    }

    // Métodos
    public function nombreCompleto(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}