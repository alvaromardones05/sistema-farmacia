<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteGenerado extends Model
{
    use HasFactory;

    protected $table = 'reportes_generados';

    protected $fillable = [
        'tipo_reporte',
        'usuario_id',
        'parametros',
        'formato',
        'archivo_path',
    ];

    protected $casts = [
        'parametros' => 'json',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Métodos
    public function esPDF(): bool
    {
        return $this->formato === 'pdf';
    }

    public function esExcel(): bool
    {
        return $this->formato === 'excel';
    }
}