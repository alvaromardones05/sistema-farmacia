<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrellativoLibro extends Model
{
    use HasFactory;

    protected $table = 'correlativos_libro';

    protected $fillable = [
        'tipo_libro',
        'proximo_folio',
    ];

    // Método: Obtener próximo folio
    public static function obtenerYIncrementarFolio(string $tipoLibro): int
    {
        $correlativo = static::where('tipo_libro', $tipoLibro)->lockForUpdate()->first();

        if (!$correlativo) {
            static::create([
                'tipo_libro' => $tipoLibro,
                'proximo_folio' => 2,
            ]);
            return 1;
        }

        $folioActual = $correlativo->proximo_folio;
        $correlativo->increment('proximo_folio');

        return $folioActual;
    }
}