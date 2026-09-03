<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LibroControlEstupefaciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'libro_control_estupefacientes';

    protected $fillable = [
        'producto_id',
        'lote_id',
        'movimiento_stock_id',
        'tipo_libro',
        'numero_folio',
        'fecha_registro',
        'cantidad_movida',
        'usuario_isp_id',
        'observaciones',
        'editable',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'editable' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function movimientoStock()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_stock_id');
    }

    public function usuarioISP()
    {
        return $this->belongsTo(User::class, 'usuario_isp_id');
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo_libro', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo_libro', 'salida');
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }
}