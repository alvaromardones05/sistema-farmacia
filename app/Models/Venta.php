<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'numero_venta',
        'paciente_id',
        'usuario_id',
        'apertura_caja_id',
        'convenio_id',
        'fecha_venta',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function aperturaCaja()
    {
        return $this->belongsTo(AperturaCaja::class, 'apertura_caja_id');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }

    public function documentoTributario()
    {
        return $this->hasOne(DocumentoTributario::class, 'venta_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(Devolucion::class, 'venta_id');
    }

    public function recetasRetenidas()
    {
        return $this->hasMany(RecetaRetenida::class, 'venta_id');
    }

    // Métodos
    public function agregarDetalle(VentaDetalle $detalle): void
    {
        $this->detalles()->save($detalle);
    }

    public function calcularTotales(): void
    {
        $this->subtotal = $this->detalles()->sum('subtotal');
        $this->impuesto = $this->detalles()->sum('impuesto');
        $this->total = $this->detalles()->sum('total');
        $this->save();
    }

    public function montoPagado(): float
    {
        return $this->pagos()->sum('monto');
    }

    public function saldoPendiente(): float
    {
        return $this->total - $this->montoPagado();
    }

    // Scopes
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estado', 'anulada');
    }
}