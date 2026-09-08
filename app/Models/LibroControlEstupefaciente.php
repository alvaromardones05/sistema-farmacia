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


        /**
     * Registrar entrada en libro ISP
     */
    public static function registrarEntrada(
        Producto $producto,
        Lote $lote,
        MovimientoStock $movimiento,
        int $usuarioISPId,
        ?string $observaciones = null
    ): self
    {
        if (!$producto->es_controlado) {
            throw new \Exception("Producto no es controlado");
        }

        $folio = CorrellativoLibro::obtenerYIncrementarFolio('entrada');

        return static::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'movimiento_stock_id' => $movimiento->id,
            'tipo_libro' => 'entrada',
            'numero_folio' => $folio,
            'fecha_registro' => now(),
            'cantidad_movida' => $movimiento->cantidad_movida,
            'usuario_isp_id' => $usuarioISPId,
            'observaciones' => $observaciones,
            'editable' => false,
        ]);
    }

    /**
     * Registrar salida en libro ISP
     */
    public static function registrarSalida(
        Producto $producto,
        Lote $lote,
        MovimientoStock $movimiento,
        int $usuarioISPId,
        ?string $observaciones = null
    ): self
    {
        if (!$producto->es_controlado) {
            throw new \Exception("Producto no es controlado");
        }

        $folio = CorrellativoLibro::obtenerYIncrementarFolio('salida');

        return static::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'movimiento_stock_id' => $movimiento->id,
            'tipo_libro' => 'salida',
            'numero_folio' => $folio,
            'fecha_registro' => now(),
            'cantidad_movida' => $movimiento->cantidad_movida,
            'usuario_isp_id' => $usuarioISPId,
            'observaciones' => $observaciones,
            'editable' => false,
        ]);
    }

    /**
     * Obtener saldo de producto controlado
     */
    public static function obtenerSaldo(Producto $producto, ?Lote $lote = null): int
    {
        $query = static::where('producto_id', $producto->id);

        if ($lote) {
            $query->where('lote_id', $lote->id);
        }

        $entradas = $query->clone()->entradas()->sum('cantidad_movida');
        $salidas = $query->clone()->salidas()->sum('cantidad_movida');

        return $entradas - $salidas;
    }

    
    //Verificar integridad de folios
    
    public static function verificarIntegridad(string $tipoLibro): bool
    {
        $registros = static::where('tipo_libro', $tipoLibro)
            ->orderBy('numero_folio')
            ->get();

        $folioEsperado = 1;
        foreach ($registros as $registro) {
            if ($registro->numero_folio !== $folioEsperado) {
                return false;
            }
            $folioEsperado++;
        }

        return true;
    }

}