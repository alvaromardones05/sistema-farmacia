<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'producto_id',
        'lote_id',
        'ubicacion_id',
        'cantidad',
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

    public function ubicacion()
    {
        return $this->belongsTo(UbicacionAlmacen::class, 'ubicacion_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoStock::class, 'stock_id');
    }

        /**
     * Registrar entrada de stock
     */
    public static function registrarEntrada(
        Producto $producto,
        Lote $lote,
        UbicacionAlmacen $ubicacion,
        int $cantidad,
        int $usuarioId,
        ?string $referencia = null,
        ?string $motivo = null
    ): MovimientoStock
    {
        $stock = static::firstOrCreate(
            [
                'producto_id' => $producto->id,
                'lote_id' => $lote->id,
                'ubicacion_id' => $ubicacion->id,
            ],
            ['cantidad' => 0]
        );

        $stock->increment('cantidad', $cantidad);

        return MovimientoStock::create([
            'stock_id' => $stock->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'tipo_movimiento' => 'entrada',
            'cantidad_movida' => $cantidad,
            'usuario_id' => $usuarioId,
            'referencia_documento' => $referencia,
            'motivo' => $motivo,
        ]);
    }

    
    //Registrar salida de stock
    
    public static function registrarSalida(
        Producto $producto,
        Lote $lote,
        UbicacionAlmacen $ubicacion,
        int $cantidad,
        int $usuarioId,
        string $tipoMovimiento = 'salida',
        ?string $referencia = null,
        ?string $motivo = null
    ): MovimientoStock
    {
        $stock = static::where('producto_id', $producto->id)
            ->where('lote_id', $lote->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->first();

        if (!$stock || $stock->cantidad < $cantidad) {
            throw new \Exception("Stock insuficiente para {$producto->nombre}");
        }

        $stock->decrement('cantidad', $cantidad);

        return MovimientoStock::create([
            'stock_id' => $stock->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'ubicacion_id' => $ubicacion->id,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad_movida' => $cantidad,
            'usuario_id' => $usuarioId,
            'referencia_documento' => $referencia,
            'motivo' => $motivo,
        ]);
    }

}