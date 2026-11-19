<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canje extends Model
{
    protected $table = 'canjes';

    protected $fillable = [
        'producto_id',
        'lote_original_id',
        'ubicacion_origen_id',
        'movimiento_salida_id',
        'movimiento_entrada_id',
        'cantidad_solicitada',
        'solicitado_por',
        'fecha_solicitud',
        'motivo_solicitud',
        'idempotency_token',
        'estado',
        'decidido_por',
        'fecha_decision',
        'motivo_decision',
        'cerrado_por',
        'fecha_cierre',
        'cantidad_original',
        'movimiento_saldo_id',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_decision' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public const DIAS_MINIMOS_PARA_REINGRESO = 30;

    public function movimientoSaldo()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_saldo_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }

    public function loteOriginal()
    {
        return $this->belongsTo(Lote::class, 'lote_original_id');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(UbicacionAlmacen::class, 'ubicacion_origen_id');
    }

    public function movimientoSalida()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_salida_id');
    }

    public function movimientoEntrada()
    {
        return $this->belongsTo(MovimientoStock::class, 'movimiento_entrada_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function decisor()
    {
        return $this->belongsTo(User::class, 'decidido_por');
    }

    public function cerrador()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function entregas()
    {
        return $this->hasMany(CanjeEntrega::class, 'canje_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(CanjeDevolucion::class, 'canje_id');
    }

    public function reposiciones()
    {
        return $this->hasMany(CanjeReposicion::class, 'canje_id');
    }

    public function resumen(): array
    {
        $enviada   = (int) $this->entregas->sum('cantidad_enviada');
        $aceptada  = (int) $this->entregas->sum('cantidad_aceptada');
        $rechazada = (int) $this->entregas->sum('cantidad_rechazada');
        $devuelta  = (int) $this->devoluciones->sum('cantidad');
        $repuesta  = (int) $this->reposiciones->sum('cantidad');
        $sinRespuesta = $enviada - $aceptada - $rechazada;

        return [
            'solicitada'    => (int) $this->cantidad_solicitada,
            'enviada'       => $enviada,
            'aceptada'      => $aceptada,
            'rechazada'     => $rechazada,
            'devuelta'      => $devuelta,
            'repuesta'      => $repuesta,
            'sin_respuesta' => $sinRespuesta,
            'por_enviar'    => (int) $this->cantidad_solicitada - $aceptada - $sinRespuesta,
            'por_devolver'  => $rechazada - $devuelta,
            'por_reponer'   => $aceptada - $repuesta,
        ];
    }

    public function accionesDisponibles(): array
    {
        if (!in_array($this->estado, ['aprobado', 'en_proceso'], true)) {
            return [];
        }

        $r = $this->resumen();
        $acciones = [];
        if ($r['sin_respuesta'] > 0) $acciones[] = 'respuesta';
        if ($r['por_devolver'] > 0)  $acciones[] = 'devolucion';
        if ($r['por_enviar'] > 0)    $acciones[] = 'entrega';
        if ($r['por_reponer'] > 0)   $acciones[] = 'reposicion';

        return $acciones ?: ['cerrar'];
    }

    public function siguienteAccion(): ?string
    {
        return $this->accionesDisponibles()[0] ?? null;
    }

    public function textoSiguientePaso(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'Esperando autorización del Químico',
            'rechazado'  => 'Solicitud rechazada',
            'completado' => 'Canje cerrado',
            default      => match ($this->siguienteAccion()) {
                'respuesta'  => 'Registrar respuesta del distribuidor',
                'devolucion' => 'Registrar retorno de unidades rechazadas',
                'entrega'    => 'Entregar unidades al distribuidor',
                'reposicion' => 'Recibir reposición',
                'cerrar'     => 'Cerrar el canje',
                default      => '',
            },
        };
    }
}
