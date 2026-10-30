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
        'archivo_path',
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
        return $this->estado === 'vigente'
            && $this->fecha_emision->startOfDay()->lte(today())
            && $this->fecha_vencimiento->startOfDay()->gte(today());
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento->startOfDay()->lt(today());
    }

    // Scopes
    public function scopeVigentes($query)
    {
        return $query->where('estado', 'vigente')
            ->whereDate('fecha_emision', '<=', today())
            ->whereDate('fecha_vencimiento', '>=', today());
    }

    public function getEstadoVisibleAttribute(): string
    {
        if (in_array($this->estado, ['pendiente', 'vigente']) && $this->estaVencida()) {
            return 'Vencida';
        }

        return $this->estado === 'vigente' ? 'Aprobada' : ucfirst($this->estado);
    }

        /**
     * Generar número de receta único
     */
    public static function generarNumeroReceta(): string
    {
        $ultimaReceta = static::latest('id')->first();
        $numero = $ultimaReceta ? $ultimaReceta->id + 1 : 1;
        return str_pad($numero, 10, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si receta es válida
     */
    public function esValida(): bool
    {
        return $this->estaVigente() && !$this->estaVencida();
    }

}
