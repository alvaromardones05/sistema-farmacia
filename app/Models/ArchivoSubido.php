<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoSubido extends Model
{
    use HasFactory;

    protected $table = 'archivos_subidos';

    protected $fillable = [
        'usuario_id',
        'nombre_original',
        'nombre_almacenado',
        'mime_type',
        'tamano_bytes',
        'hash_sha256',
        'tipo_documento',
        'validado',
    ];

    protected $casts = [
        'validado' => 'boolean',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Métodos
    public function esReceta(): bool
    {
        return $this->tipo_documento === 'receta';
    }

    public function esPDF(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function esImagen(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png']);
    }

    public function tamanoEnMB(): float
    {
        return round($this->tamano_bytes / (1024 * 1024), 2);
    }

    // Scopes
    public function scopeValidados($query)
    {
        return $query->where('validado', true);
    }

    public function scopeRecetas($query)
    {
        return $query->where('tipo_documento', 'receta');
    }
}