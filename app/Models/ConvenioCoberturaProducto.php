<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvenioCoberturaProducto extends Model
{
    use HasFactory;

    protected $table = 'convenio_cobertura_producto';

    protected $fillable = [
        'convenio_id',
        'producto_id',
        'copago_pct',
        'copago_fijo',
        'observaciones',
    ];

    protected $casts = [
        'copago_pct' => 'decimal:2',
        'copago_fijo' => 'decimal:2',
    ];

    // Relaciones
    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}