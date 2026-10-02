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

    public function agregarDetalle(VentaDetalle $detalle): void
    {
        $this->detalles()->save($detalle);
    }

    public function montoPagado(): float
    {
        return (float) $this->pagos()->sum('monto');
    }

    public function saldoPendiente(): float
    {
        return (float) $this->total - $this->montoPagado();
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estado', 'anulada');
    }

    public static function generarNumeroVenta(): string
    {
        $ultimaVenta = static::withTrashed()
            ->latest('id')
            ->first();

        $numero = $ultimaVenta
            ? $ultimaVenta->id + 1
            : 1;

        return str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    public function calcularTotales(): void
    {
        $detalles = $this->detalles()->get();

        $this->subtotal = round(
            $detalles->sum(fn ($detalle) => (float) $detalle->subtotal),
            2
        );

        $this->descuento = round(
            $detalles->sum(function ($detalle) {
                $precioOriginal =
                    (float) $detalle->precio_unitario * $detalle->cantidad;

                return $precioOriginal *
                    ((float) $detalle->descuento_pct / 100);
            }),
            2
        );

        $this->impuesto = round(
            $detalles->sum(fn ($detalle) => (float) $detalle->impuesto),
            2
        );

        $this->total = round(
            $detalles->sum(fn ($detalle) => (float) $detalle->total),
            2
        );

        $this->save();
    }
}