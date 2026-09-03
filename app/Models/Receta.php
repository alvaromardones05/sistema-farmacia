<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recetas';

    protected $fillable = [
        'numero_receta',
        'paciente_id',
        'medico_id',
        'fecha_emision',
        'fecha_vencimiento',
        'tipo',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function detalles()
    {
        return $this->hasMany(RecetaDetalle::class, 'receta_id');
    }

    public function recetasRetenidas()
    {
        return $this->hasMany(RecetaRetenida::class, 'receta_id');
    }

    // Métodos
    public function estaVigente(): bool
    {
        return $this->estado === 'vigente' && $this->fecha_vencimiento->isFuture();
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento->isPast();
    }

    // Scopes
    public function scopeVigentes($query)
    {
        return $query->where('estado', 'vigente')
            ->whereDate('fecha_vencimiento', '>', now());
    }
}