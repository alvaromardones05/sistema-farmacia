<?php

namespace App\Http\Controllers;

use App\Models\DescuadreStockControlado;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Receta; // Asegúrate de importar tu modelo de Recetas
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\VentaDetalle;
use App\Models\Canje;
use Illuminate\Http\JsonResponse;

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
            'ventas_mes' => null,
            'stock_critico' => null,
            'lotes_por_vencer' => null,
            'descuadres_pendientes' => null,
            'recetas_pendientes' => null,
        ];

        $productosCriticos = collect();
        $lotesPorVencer = collect();

        // Variables para gráficos
        $chartVentasLabels = [];
        $chartVentasData = [];
        $chartStockLabels = [];
        $chartStockData = [];

        // Variables para gráficos
        $chartVentasLabels = [];
        $chartVentasData = [];
        $chartStockLabels = [];
        $chartStockData = [];

        // Variables para Top Productos
        $topProductosLabels = [];
        $topProductosData = [];

        if ($puedeVerVentas) {
            // 1. Ventas de Hoy (Cambiado completadas() por where)
            $ventasHoy = Venta::where('estado', 'completada')
                ->where('fecha_venta', '>=', $hoy)
                ->where('fecha_venta', '<', $hoy->copy()->addDay())
                ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total')
                ->first();

            $metricas['cantidad_ventas'] = (int) $ventasHoy->cantidad;
            $metricas['total_ventas'] = (float) $ventasHoy->total;

            // 2. Ventas del Mes
            $metricas['ventas_mes'] = (float) Venta::where('estado', 'completada')
                ->whereBetween('fecha_venta', [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()])
                ->sum('total');

            // 3. Datos para el Gráfico de Ventas (Últimos 14 días)
            for ($i = 13; $i >= 0; $i--) {
                $fecha = $hoy->copy()->subDays($i);
                $chartVentasLabels[] = $fecha->format('d M');

                $ventasDia = Venta::where('estado', 'completada')
                    ->whereDate('fecha_venta', $fecha)
                    ->sum('total');
                $chartVentasData[] = (float) $ventasDia;
            }

            // 4. Datos para el Gráfico de Top Productos del Mes
            $topProductos = VentaDetalle::query()
                ->join('ventas', 'venta_detalle.venta_id', '=', 'ventas.id')
                ->join('productos', 'venta_detalle.producto_id', '=', 'productos.id')
                ->where('ventas.estado', 'completada')
                ->whereMonth('ventas.fecha_venta', $hoy->month)
                ->whereYear('ventas.fecha_venta', $hoy->year)
                ->select('productos.nombre', DB::raw('SUM(venta_detalle.cantidad) as total_vendido'))
                ->groupBy('productos.id', 'productos.nombre')
                ->orderByDesc('total_vendido')
                ->limit(5)
                ->get();

            $topProductosLabels = $topProductos->pluck('nombre')->toArray();
            $topProductosData = $topProductos->pluck('total_vendido')->map(fn($val) => (int) $val)->toArray();
        }

        if ($puedeVerInventario) {
            $criticos = $this->queryCriticos();

            $metricas['stock_critico'] = (clone $criticos)->count();
            $productosCriticos = (clone $criticos)
                ->select('productos.id', 'productos.nombre', 'productos.stock_critico')
                ->selectRaw('COALESCE(existencias.stock_utilizable, 0) as stock_utilizable')
                ->orderBy('stock_utilizable')
                ->orderByRaw('(productos.stock_critico - COALESCE(existencias.stock_utilizable, 0)) DESC')
                ->orderBy('productos.id')
                ->limit(5)
                ->get();

            $porVencer = $this->queryPorVencer();

            $metricas['lotes_por_vencer'] = (clone $porVencer)->count();
            $lotesPorVencer = (clone $porVencer)
                ->with('producto:id,nombre')
                ->withSum(['stock as stock_comercial' => fn($query) => $query->enUbicacionesComerciales()], 'cantidad')
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->limit(5)
                ->get();

            // 5. Datos para el Gráfico de Dona (Distribución de Stock)
            $stockPorUbicacion = DB::table('stock')
                ->join('ubicaciones_almacen', 'stock.ubicacion_id', '=', 'ubicaciones_almacen.id')
                ->select('ubicaciones_almacen.tipo', DB::raw('SUM(stock.cantidad) as total'))
                ->where('ubicaciones_almacen.activo', true)
                ->groupBy('ubicaciones_almacen.tipo')
                ->pluck('total', 'tipo')
                ->toArray();

            $tiposUbicacion = [
                'vitrina' => 'Vitrina',
                'vitrina_refrigerada' => 'Vitrina Ref.',
                'bodega' => 'Bodega',
                'frigorifica' => 'Frigorífica',
                'cuarentena' => 'Cuarentena',
                'canje' => 'Canje'
            ];

            foreach ($tiposUbicacion as $key => $label) {
                $chartStockLabels[] = $label;
                $chartStockData[] = (int) ($stockPorUbicacion[$key] ?? 0);
            }
        }

        if ($puedeRevisarDescuadres) {
            $metricas['descuadres_pendientes'] = DescuadreStockControlado::pendientes()->count();
            $metricas['recetas_pendientes'] = Receta::where('estado', 'pendiente')->count();
        }

        $esQuimico = $usuario->hasRole('Químico Farmacéutico');
        $puedeVerCanjes = $puedeVerInventario || $esQuimico;
        $metricas['canjes'] = $puedeVerCanjes ? $this->contarCanjes($esQuimico) : null;

        return view('dashboard', compact(
            'metricas',
            'productosCriticos',
            'lotesPorVencer',
            'puedeVerInventario',
            'puedeAccederVentas',
            'puedeVerVentas',
            'puedeRevisarDescuadres',
            'chartVentasLabels',
            'chartVentasData',
            'chartStockLabels',
            'chartStockData',
            'topProductosLabels',  // <-- AGREGAR
            'topProductosData',     // <-- AGREGAR
            'puedeVerCanjes',
            'esQuimico'
        ));
    }

    private function queryCriticos()
    {
        return Producto::activos()
            ->leftJoinSub(Producto::queryStockUtilizablePorProducto(), 'existencias', function ($join) {
                $join->on('productos.id', '=', 'existencias.producto_id');
            })
            ->whereRaw('COALESCE(existencias.stock_utilizable, 0) <= productos.stock_critico');
    }

    private function queryPorVencer()
    {
        return Lote::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha_vencimiento', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->whereHas('stock', fn($query) => $query
                ->enUbicacionesComerciales()
                ->where('cantidad', '>', 0));
    }

    private function contarCanjes(bool $esQuimico): int
    {
        return Canje::whereIn('estado', $esQuimico ? ['pendiente'] : ['aprobado', 'en_proceso'])->count();
    }

    public function kpis(): JsonResponse
    {
        $usuario = auth()->user();
        $esQuimico = $usuario->hasRole('Químico Farmacéutico');
        $puedeVerInventario = $usuario->can('ver_stock');
        $puedeVerVentas = $usuario->hasAnyRole(['Químico Farmacéutico', 'Técnico Farmacéutico'])
            || $usuario->can('ver_reportes_globales');
        $hoy = today();

        $datos = ['actualizado' => now()->format('H:i')];

        if ($puedeVerVentas) {
            $ventasHoy = Venta::where('estado', 'completada')
                ->where('fecha_venta', '>=', $hoy)
                ->where('fecha_venta', '<', $hoy->copy()->addDay())
                ->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total')
                ->first();

            $datos['cantidad_ventas'] = (int) $ventasHoy->cantidad;
            $datos['total_ventas'] = (float) $ventasHoy->total;
            $datos['ventas_mes'] = (float) Venta::where('estado', 'completada')
                ->whereBetween('fecha_venta', [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth()])
                ->sum('total');
        }

        if ($puedeVerInventario) {
            $datos['stock_critico'] = $this->queryCriticos()->count();
            $datos['lotes_por_vencer'] = $this->queryPorVencer()->count();
        }

        if ($esQuimico) {
            $datos['descuadres_pendientes'] = DescuadreStockControlado::pendientes()->count();
            $datos['recetas_pendientes'] = Receta::where('estado', 'pendiente')->count();
        }

        if ($puedeVerInventario || $esQuimico) {
            $datos['canjes'] = $this->contarCanjes($esQuimico);
        }

        return response()->json($datos);
    }
}
