<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'codigo_interno',
        'codigo_barra',
        'descripcion',
        'precio_base',
        'dosis',
        'es_controlado',
        'principio_activo',
        'forma_farmaceutica',
        'concentracion',
        'categoria_id',
        'laboratorio_id',
        'unidad_medida',
        'condicion_almacenamiento',
        'requiere_receta',
        'tipo_receta',
        'es_controlado',
        'registro_isp',
        'stock_minimo',
        'stock_critico',
        'activo',
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'es_controlado' => 'boolean',
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    public function stock()
    {
        return $this->hasMany(Stock::class, 'producto_id');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'producto_id');
    }

    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class, 'producto_id');
    }

    public function alertas()
    {
        return $this->hasMany(AlertaStock::class, 'producto_id');
    }

    public function recetasDetalles()
    {
        return $this->hasMany(RecetaDetalle::class, 'producto_id');
    }

    public function ventasDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'producto_id');
    }

    public function librosControlIsp()
    {
        return $this->hasMany(LibroControlEstupefaciente::class, 'producto_id');
    }

    public function descuadresStock()
    {
        return $this->hasMany(DescuadreStockControlado::class, 'producto_id');
    }

    public function convenioCoberturas()
    {
        return $this->hasMany(ConvenioCoberturaProducto::class, 'producto_id');
    }

    // Scope: Productos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope: Productos controlados
    public function scopeControlados($query)
    {
        return $query->where('es_controlado', true);
    }

    // Scope: Productos que requieren receta
    public function scopeConReceta($query)
    {
        return $query->where('requiere_receta', true);
    }

    // Método: Obtener precio vigente
    public function getPrecioVigente(?int $listaId = null)
    {
        $query = $this->precios()
            ->whereDate('vigente_desde', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigente_hasta')
                    ->orWhereDate('vigente_hasta', '>=', now());
            });

        if ($listaId) {
            $query->where('lista_precio_id', $listaId);
        }

        return $query->latest('vigente_desde')->first();
    }

    // Método: Obtener stock total
    public function getStockTotal(): int
    {
        return $this->stock()->sum('cantidad');
    }

    // Método: Está bajo de stock
    public function estaBajoStock(): bool
    {
        return $this->getStockTotal() <= $this->stock_minimo;
    }

    // Método: Está en stock crítico
    public function estaEnStockCritico(): bool
    {
        return $this->getStockTotal() <= $this->stock_critico;
    }


    //LOGICA REUTILIZABLE
    public function obtenerStock(): int
    {
        return Stock::where('producto_id', $this->id)->sum('cantidad');
    }

    public function getStockReserva(): int
    {
        return $this->stockEnUbicaciones(['bodega', 'frigorifica'])->sum('cantidad');
    }

    public function getStockVendible(): int
    {
        $ubicacionVenta = match ($this->condicion_almacenamiento) {
            'ambiente' => 'vitrina',
            'refrigerado' => 'vitrina_refrigerada',
            default => null,
        };

        if (!$this->activo || !$ubicacionVenta) {
            return 0;
        }

        $ubicacion = UbicacionAlmacen::query()
            ->where('tipo', $ubicacionVenta)
            ->where('activo', true)
            ->orderByDesc('id')
            ->first();

        if (!$ubicacion) {
            return 0;
        }

        return $this->stock()
            ->where('cantidad', '>', 0)
            ->where('ubicacion_id', $ubicacion->id)
            ->whereHas('lote', fn($query) => $query
                ->where('estado', 'activo')
                ->whereDate('fecha_vencimiento', '>=', today()))
            ->sum('cantidad');
    }

    public function getStockNoVendible(): int
    {
        return $this->stockEnUbicaciones(['cuarentena', 'canje'])->sum('cantidad');
    }

    public function getStockUtilizable(): int
    {
        return (int) (static::queryStockUtilizablePorProducto()
            ->where('stock.producto_id', $this->id)
            ->value('stock_utilizable') ?? 0);
    }

    public function estaBajoStockUtilizable(): bool
    {
        return $this->getStockUtilizable() <= $this->stock_minimo;
    }

    public function estaEnStockCriticoUtilizable(): bool
    {
        return $this->getStockUtilizable() <= $this->stock_critico;
    }

    public static function queryStockUtilizablePorProducto()
    {
        return Stock::query()
            ->join('productos as productos_utilizables', 'stock.producto_id', '=', 'productos_utilizables.id')
            ->join('ubicaciones_almacen as ubicaciones_utilizables', 'stock.ubicacion_id', '=', 'ubicaciones_utilizables.id')
            ->where(function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('productos_utilizables.condicion_almacenamiento', 'ambiente')
                        ->whereIn('ubicaciones_utilizables.tipo', ['bodega', 'vitrina']);
                })->orWhere(function ($subquery) {
                    $subquery->where('productos_utilizables.condicion_almacenamiento', 'refrigerado')
                        ->whereIn('ubicaciones_utilizables.tipo', ['frigorifica', 'vitrina_refrigerada']);
                });
            })
            ->select('stock.producto_id')
            ->selectRaw('SUM(stock.cantidad) as stock_utilizable')
            ->groupBy('stock.producto_id');
    }

    private function stockEnUbicaciones(array $tipos)
    {
        return $this->stock()->whereHas('ubicacion', fn($query) => $query->whereIn('tipo', $tipos));
    }

    /**
     * Obtener próximo lote FEFO
     */
    public function obtenerProximoLoteFEFO(): ?Lote
    {
        return Lote::where('producto_id', $this->id)
            ->where('estado', 'activo')
            ->orderBy('fecha_vencimiento')
            ->first();
    }
}
