<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierres_caja';

    protected $fillable = [
        'apertura_caja_id',
        'usuario_id',
        'fecha_cierre',
        'saldo_inicial',
        'monto_vendido',
        'monto_reembolso',
        'monto_gastos',
        'saldo_contado',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'monto_vendido' => 'decimal:2',
        'monto_reembolso' => 'decimal:2',
        'monto_gastos' => 'decimal:2',
        'saldo_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    // Relaciones
    public function aperturaCaja()
    {
        return $this->belongsTo(AperturaCaja::class, 'apertura_caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}