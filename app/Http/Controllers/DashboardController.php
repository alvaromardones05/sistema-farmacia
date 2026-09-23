<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usuario = auth()->user();

        // =====================================================
        // ROLES
        // =====================================================

        $esAdministrador = $usuario->hasRole('Administrador');

        $puedeVerInventario = $usuario->hasAnyRole([
            'Químico Farmacéutico',
            'Técnico Farmacéutico',
        ]);

        // =====================================================
        // PRODUCTOS
        // =====================================================

        $productos = $puedeVerInventario
            ? Producto::with('stock')
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        // =====================================================
        // LOTES
        // =====================================================

        $lotes = $puedeVerInventario
            ? Lote::with(['producto', 'stock'])
                ->latest('fecha_vencimiento')
                ->limit(8)
                ->get()
            : collect();

        // =====================================================
        // VENTAS
        // =====================================================

        $ventas = $puedeVerInventario
            ? Venta::with('usuario')
                ->latest('fecha_venta')
                ->limit(8)
                ->get()
            : collect();

        // =====================================================
        // USUARIOS
        // =====================================================

        $usuarios = $esAdministrador
            ? User::with('roles')
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        // =====================================================
        // MÉTRICAS
        // =====================================================

        $metricas = [
            'productos_activos' => $puedeVerInventario
                ? Producto::activos()->count()
                : null,

            'stock_critico' => $puedeVerInventario
                ? Producto::activos()
                    ->get()
                    ->filter(
                        fn (Producto $producto) =>
                            $producto->estaEnStockCritico()
                    )
                    ->count()
                : null,

            'lotes_por_vencer' => $puedeVerInventario
                ? Lote::whereBetween(
                    'fecha_vencimiento',
                    [
                        today(),
                        today()->copy()->addDays(30)
                    ]
                )->count()
                : null,

            'ventas_dia' => $puedeVerInventario
                ? Venta::whereDate('fecha_venta', today())
                    ->completadas()
                    ->sum('total')
                : null,

            'ventas_mes' => $puedeVerInventario
                ? Venta::whereBetween(
                    'fecha_venta',
                    [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ]
                )
                    ->completadas()
                    ->sum('total')
                : null,
        ];

        // =====================================================
        // DASHBOARD
        // =====================================================

        return view('dashboard', compact(
            'productos',
            'lotes',
            'ventas',
            'usuarios',
            'puedeVerInventario',
            'esAdministrador',
            'metricas'
        ));
    }
}   