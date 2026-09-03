<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'valores_anteriores',
        'valores_nuevos',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'valores_anteriores' => 'json',
        'valores_nuevos' => 'json',
        'created_at' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeConAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('user_id', $usuarioId);
    }

    public function scopeDelTipo($query, $tipo)
    {
        return $query->where('entidad_tipo', $tipo);
    }
}