<?php

namespace App\Http\Controllers;

use App\Models\DescuadreStockControlado;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\Venta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usuario = auth()->user();
        $puedeVerInventario = $usuario->can('ver_stock');
        $puedeAccederVentas = $usuario->hasAnyRole([
            'Químico Farmacéutico',
            'Técnico Farmacéutico',
        ]);
        $puedeVerVentas = $puedeAccederVentas || $usuario->can('ver_reportes_globales');
        $puedeRevisarDescuadres = $usuario->hasRole('Químico Farmacéutico');

        $hoy = today();
        $hasta = $hoy->copy()->addDays(30);
        $metricas = [
            'cantidad_ventas' => null,
            'total_ventas' => null,
            'stock_critico' => null,
            'lotes_por_vencer' => null,
            'descuadres_pendientes' => null,
        ];
        $productosCriticos = collect();
        $lotesPorVencer = collect();

        if ($puedeVerVentas) {
            $ventasHoy = Venta::completadas()
                ->where('fecha_venta', '>=', $hoy)
                ->where('fecha_venta', '<', $hoy->copy()->addDay())
                ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total')
                ->first();

            $metricas['cantidad_ventas'] = (int) $ventasHoy->cantidad;
            $metricas['total_ventas'] = (float) $ventasHoy->total;
        }

        if ($puedeVerInventario) {
            // Suma todas las ubicaciones y lotes; sin registros de stock equivale a cero.
            $stockPorProducto = Stock::query()
                ->selectRaw('producto_id, SUM(cantidad) as stock_total')
                ->groupBy('producto_id');

            $criticos = Producto::activos()
                ->leftJoinSub($stockPorProducto, 'existencias', function ($join) {
                    $join->on('productos.id', '=', 'existencias.producto_id');
                })
                ->whereRaw('COALESCE(existencias.stock_total, 0) <= productos.stock_critico');

            $metricas['stock_critico'] = (clone $criticos)->count();
            $productosCriticos = (clone $criticos)
                ->select('productos.id', 'productos.nombre', 'productos.stock_critico')
                ->selectRaw('COALESCE(existencias.stock_total, 0) as stock_total')
                ->orderBy('stock_total')
                ->orderByRaw('(productos.stock_critico - COALESCE(existencias.stock_total, 0)) DESC')
                ->orderBy('productos.id')
                ->limit(5)
                ->get();

            $porVencer = Lote::query()
                ->where('estado', 'activo')
                ->whereBetween('fecha_vencimiento', [$hoy->toDateString(), $hasta->toDateString()]);

            $metricas['lotes_por_vencer'] = (clone $porVencer)->count();
            $lotesPorVencer = (clone $porVencer)
                ->with('producto:id,nombre')
                ->withSum('stock as stock_total', 'cantidad')
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->limit(5)
                ->get();
        }

        if ($puedeRevisarDescuadres) {
            $metricas['descuadres_pendientes'] = DescuadreStockControlado::pendientes()->count();
        }

        return view('dashboard', compact(
            'metricas',
            'productosCriticos',
            'lotesPorVencer',
            'puedeVerInventario',
            'puedeAccederVentas',
            'puedeVerVentas',
            'puedeRevisarDescuadres'
        ));
    }
}
