<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medicos';

    protected $fillable = [
        'rut',
        'nombres',
        'apellidos',
        'registro_profesional',
        'especialidad',
        'telefono',
        'email',
        'consultorio',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function recetas()
    {
        return $this->hasMany(Receta::class, 'medico_id');
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