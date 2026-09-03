<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'producto_id',
        'numero_lote',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'cantidad_inicial',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function stock()
    {
        return $this->hasMany(Stock::class, 'lote_id');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'lote_id');
    }

    public function ventasDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'lote_id');
    }

    public function librosControlIsp()
    {
        return $this->hasMany(LibroControlEstupefaciente::class, 'lote_id');
    }

    // Métodos
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento->isPast();
    }

    public function diasParaVencimiento(): int
    {
        return now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function proximoAVencer(): bool
    {
        return $this->diasParaVencimiento() <= 30 && !$this->estaVencido();
    }

    public function stockTotal(): int
    {
        return $this->stock()->sum('cantidad');
    }
}