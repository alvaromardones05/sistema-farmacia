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
        'codigo_interno',
        'codigo_barra',
        'nombre',
        'principio_activo',
        'forma_farmaceutica',
        'concentracion',
        'categoria_id',
        'laboratorio_id',
        'unidad_medida',
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
}