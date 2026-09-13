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
        $esAdministrador = $usuario->hasRole('Administrador');
        $puedeVerProductos = $esAdministrador || $usuario->hasAnyRole([
            'Químico Farmacéutico',
        ]);
        $puedeVerVentas = $esAdministrador || $usuario->hasAnyRole([
            'Técnico Farmacéutico',
            'Químico Farmacéutico',
        ]);
        $puedeVerLotes = $esAdministrador || $usuario->hasAnyRole([
            'Químico Farmacéutico',
            'Bodeguero',
        ]);

        $productos = $puedeVerProductos
            ? Producto::with('stock')->latest()->limit(8)->get()
            : collect();

        $ventas = $puedeVerVentas
            ? Venta::with('usuario')->latest('fecha_venta')->limit(8)->get()
            : collect();

        $lotes = $puedeVerLotes
            ? Lote::with(['producto', 'stock'])->latest('fecha_vencimiento')->limit(8)->get()
            : collect();

        $usuarios = $esAdministrador
            ? User::with('roles')->latest()->limit(8)->get()
            : collect();

        $metricas = [
            'productos_activos' => $puedeVerProductos ? Producto::activos()->count() : null,
            'stock_critico' => $puedeVerProductos
                ? Producto::activos()->get()->filter(fn (Producto $producto) => $producto->estaEnStockCritico())->count()
                : null,
            'lotes_por_vencer' => $puedeVerLotes
                ? Lote::whereBetween('fecha_vencimiento', [today(), today()->copy()->addDays(30)])->count()
                : null,
            'ventas_dia' => $puedeVerVentas
                ? Venta::whereDate('fecha_venta', today())->completadas()->sum('total')
                : null,
            'ventas_mes' => $puedeVerVentas
                ? Venta::whereBetween('fecha_venta', [now()->startOfMonth(), now()->endOfMonth()])->completadas()->sum('total')
                : null,
        ];

        return view('dashboard', compact(
            'productos',
            'ventas',
            'lotes',
            'usuarios',
            'puedeVerProductos',
            'puedeVerVentas',
            'puedeVerLotes',
            'esAdministrador',
            'metricas'
        ));
    }
}