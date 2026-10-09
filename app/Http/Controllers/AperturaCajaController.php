<?php

namespace App\Http\Controllers;

use App\Models\AperturaCaja;
use App\Models\Caja;
use App\Models\CierreCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AperturaCajaController extends Controller
{
    public function index()
    {
        $apertura = AperturaCaja::abiertas()
            ->with(['caja', 'usuario', 'ventas.pagos'])
            ->first();

        $cajas = Caja::activas()
            ->orderBy('numero_caja')
            ->get();

        $resumen = $apertura
            ? $this->resumenCaja($apertura)
            : null;

        return view('caja.index', compact(
            'apertura',
            'cajas',
            'resumen'
        ));
    }

    public function abrir(Request $request)
    {
        $datos = $request->validate([
            'caja_id' => [
                'required',
                Rule::exists('cajas', 'id')
                    ->where(fn ($query) => $query->where('activa', true)),
            ],
            'saldo_inicial' => 'required|numeric|min:0',
            'redirect_to' => 'nullable|string',
        ]);

        if (AperturaCaja::abiertas()->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'caja' => 'Ya existe una caja abierta.'
                ]);
        }

        AperturaCaja::create([
            'caja_id' => $datos['caja_id'],
            'usuario_id' => auth()->id(),
            'fecha_apertura' => now(),
            'saldo_inicial' => $datos['saldo_inicial'],
            'estado' => 'abierta',
        ]);

        /*
         * Solo permitimos este retorno conocido.
         * Evita redirecciones arbitrarias.
         */
        if (
            !empty($datos['redirect_to']) &&
            $datos['redirect_to'] === route('ventas.create')
        ) {
            return redirect()
                ->route('ventas.create')
                ->with('success', 'Caja abierta correctamente.');
        }

        return redirect()
            ->route('caja.index')
            ->with('success', 'Caja abierta correctamente.');
    }

    public function cerrar(Request $request, AperturaCaja $apertura)
    {
        $datos = $request->validate([
            'saldo_contado' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($apertura, $datos) {

            /*
             * Bloqueamos la apertura mientras se cierra para evitar
             * que dos solicitudes puedan cerrarla al mismo tiempo.
             */
            $apertura = AperturaCaja::query()
                ->whereKey($apertura->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($apertura->estado !== 'abierta') {
                return redirect()
                    ->route('caja.index')
                    ->with('error', 'Esta caja ya está cerrada.');
            }

            if ($apertura->cierre()->exists()) {
                return redirect()
                    ->route('caja.index')
                    ->with('error', 'Esta apertura ya tiene un cierre registrado.');
            }

            $apertura->load(['ventas.pagos']);

            $resumen = $this->resumenCaja($apertura);

            $saldoContado = (float) $datos['saldo_contado'];

            $diferencia = round(
                $saldoContado - $resumen['saldo_esperado'],
                2
            );

            CierreCaja::create([
                'apertura_caja_id' => $apertura->id,
                'usuario_id' => auth()->id(),
                'fecha_cierre' => now(),

                'saldo_inicial' => $apertura->saldo_inicial,
                'monto_vendido' => $resumen['total_vendido'],

                /*
                 * Todavía no existen flujos reales de gastos/reembolsos
                 * conectados a Caja, por lo que no inventamos valores.
                 */
                'monto_reembolso' => 0,
                'monto_gastos' => 0,

                'saldo_contado' => $saldoContado,
                'diferencia' => $diferencia,

                'estado' => abs($diferencia) < 0.01
                    ? 'aceptado'
                    : 'con_diferencia',

                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $apertura->update([
                'estado' => 'cerrada'
            ]);

            return redirect()
                ->route('caja.index')
                ->with(
                    'success',
                    abs($diferencia) < 0.01
                        ? 'Caja cerrada correctamente. El arqueo está cuadrado.'
                        : 'Caja cerrada correctamente, pero se registró una diferencia en el arqueo.'
                );
        });
    }

    public function historial()
    {
        $cierres = CierreCaja::with([
                'aperturaCaja.caja',
                'aperturaCaja.usuario',
                'usuario',
            ])
            ->latest('fecha_cierre')
            ->paginate(15);

        return view('caja.historial', compact('cierres'));
    }

    private function resumenCaja(AperturaCaja $apertura): array
    {
        $ventas = $apertura->relationLoaded('ventas')
            ? $apertura->ventas
            : $apertura->ventas()->with('pagos')->get();

        $ventasCompletadas = $ventas
            ->where('estado', 'completada')
            ->values();

        $ventasAnuladas = $ventas
            ->where('estado', 'anulada')
            ->values();

        $pagos = $ventasCompletadas
            ->flatMap(fn ($venta) => $venta->pagos);

        $totalVendido = (float) $ventasCompletadas->sum('total');
        $totalPagosRegistrados = (float) $pagos->sum('monto');

        /*
         * Ventas antiguas o todavía no integradas con Pago.
         * No desaparecen del arqueo: se muestran claramente como
         * "medio de pago no registrado".
         */
        $sinMedioRegistrado = max(
            0,
            $totalVendido - $totalPagosRegistrados
        );

        $pagosPorMetodo = [
            'efectivo' => (float) $pagos->where('metodo_pago', 'efectivo')->sum('monto'),
            'debito' => (float) $pagos->where('metodo_pago', 'debito')->sum('monto'),
            'credito' => (float) $pagos->where('metodo_pago', 'credito')->sum('monto'),
            'transferencia' => (float) $pagos->where('metodo_pago', 'transferencia')->sum('monto'),
            'cheque' => (float) $pagos->where('metodo_pago', 'cheque')->sum('monto'),
        ];

        /*
         * Mientras una venta no tenga Pago registrado la tratamos
         * provisionalmente como efectivo para no perderla del arqueo.
         * La interfaz avisa claramente esta situación.
         */
        $efectivoVentas = $pagosPorMetodo['efectivo'] + $sinMedioRegistrado;

        $saldoEsperado = (float) $apertura->saldo_inicial +$efectivoVentas;

        $totalRecaudado = array_sum($pagosPorMetodo) + $sinMedioRegistrado;

        return [
            'cantidad_ventas' => $ventasCompletadas->count(),
            'cantidad_anuladas' => $ventasAnuladas->count(),

            'total_vendido' => $totalVendido,
            'total_pagos_registrados' => $totalPagosRegistrados,
            'sin_medio_registrado' => $sinMedioRegistrado,

            'pagos' => $pagosPorMetodo,
            'efectivo_ventas' => $efectivoVentas,
            'saldo_esperado' => $saldoEsperado,
            'total_recaudado' => $totalRecaudado,

            'ventas_recientes' => $ventasCompletadas
                ->sortByDesc('fecha_venta')
                ->take(8),
        ];
    }
}