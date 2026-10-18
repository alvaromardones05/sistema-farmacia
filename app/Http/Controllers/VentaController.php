<?php

namespace App\Http\Controllers;

use App\Models\AperturaCaja;
use App\Models\Convenio;
use App\Models\DocumentoTributario;
use App\Models\MovimientoCaja;
use App\Models\MovimientoStock;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    // ===================== LISTAR =====================

    public function index()
    {
        $ventas = Venta::with('paciente', 'usuario', 'detalles')
            ->latest()
            ->paginate(15);

        return view('ventas.index', compact('ventas'));
    }


    // ===================== CREAR =====================

    public function create()
    {
        $aperturaCaja = $this->obtenerCajaAbierta();
        $ubicacionVenta = $this->obtenerUbicacionVenta();

        $pacientes = Paciente::activos()
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        $conveniosParaJs = $this->obtenerConveniosParaJs();

        $productos = $ubicacionVenta
            ? $this->obtenerProductosDisponibles($ubicacionVenta)
            : collect();

        return view('ventas.create', compact(
            'pacientes',
            'productos',
            'aperturaCaja',
            'ubicacionVenta',
            'conveniosParaJs'
        ));
    }


    // ===================== GUARDAR =====================

    public function store(Request $request)
    {
        $validated = $this->validarVenta($request);
        $ubicacionVenta = $this->obtenerUbicacionVenta();

        if (!$ubicacionVenta) {
            return back()
                ->withInput()
                ->withErrors([
                    'ubicacion' => 'No existe una ubicación de tipo vitrina configurada.',
                ]);
        }

        try {
            $venta = DB::transaction(function () use ($validated, $ubicacionVenta) {

                $aperturaCaja = $this->obtenerCajaAbierta(true);

                if (!$aperturaCaja) {
                    throw ValidationException::withMessages([
                        'caja' => 'No hay una caja abierta para el usuario actual.',
                    ]);
                }

                [$paciente, $convenio] =
                    $this->obtenerPacienteYConvenio($validated['paciente_id'] ?? null);

                $detallesSolicitud = collect($validated['detalles']);

                $productoIds = $detallesSolicitud
                    ->pluck('producto_id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $productos = $this->obtenerProductosParaVenta($productoIds);

                if ($productos->count() !== count($productoIds)) {
                    throw ValidationException::withMessages([
                        'detalles' => 'Uno o más productos ya no están disponibles.',
                    ]);
                }

                $stocksPorProducto = $this->obtenerStockFefo(
                    $productoIds,
                    $ubicacionVenta
                );

                // Evita generar el mismo correlativo simultáneamente.
                Venta::query()
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $venta = Venta::create([
                    'numero_venta' => Venta::generarNumeroVenta(),
                    'paciente_id' => $paciente?->id,
                    'usuario_id' => auth()->id(),
                    'apertura_caja_id' => $aperturaCaja->id,
                    'convenio_id' => $convenio?->id,
                    'fecha_venta' => now(),
                    'subtotal' => 0,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'total' => 0,
                    'estado' => 'pendiente',
                ]);

                $resultado = $this->procesarDetalles(
                    $venta,
                    $detallesSolicitud,
                    $productos,
                    $stocksPorProducto,
                    $ubicacionVenta,
                    $convenio
                );

                $this->guardarResultadoVenta(
                    $venta,
                    $resultado
                );

                $this->registrarPagoCajaYDocumento(
                    $venta,
                    $aperturaCaja,
                    $validated
                );

                return $venta;
            }, 3);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'venta' => 'No fue posible procesar la venta. Intenta nuevamente.',
                ]);
        }

        return redirect()
            ->route('ventas.show', $venta)
            ->with(
                'success',
                'Venta procesada y boleta generada correctamente.'
            );
    }


    // ===================== VER =====================

    public function show(Venta $venta)
    {
        $venta->load(
            'paciente',
            'usuario',
            'detalles.producto',
            'detalles.lote',
            'pagos',
            'convenio',
            'documentoTributario'
        );

        return view('ventas.show', compact('venta'));
    }


    // ===================== BOLETA =====================

    public function boleta(Venta $venta)
    {
        $venta->load(
            'paciente',
            'usuario',
            'detalles.producto',
            'detalles.lote',
            'pagos',
            'documentoTributario'
        );

        if (!$venta->documentoTributario) {
            abort(404, 'La venta no tiene una boleta asociada.');
        }

        return view('ventas.boleta', compact('venta'));
    }


    // ===================== ANULAR =====================

    public function anular(Venta $venta, Request $request)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        if ($venta->estado !== 'completada') {
            return back()->withErrors([
                'venta' => 'Esta venta no puede ser anulada.',
            ]);
        }

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')->first();

        if (!$ubicacionVenta) {
            return back()->withErrors([
                'ubicacion' => 'No existe una ubicación de tipo vitrina.',
            ]);
        }

        DB::transaction(function () use ($venta, $ubicacionVenta, $request) {

            $venta->load('detalles.producto', 'detalles.lote');

            foreach ($venta->detalles as $detalle) {
                Stock::registrarEntrada(
                    $detalle->producto,
                    $detalle->lote,
                    $ubicacionVenta,
                    $detalle->cantidad,
                    auth()->id(),
                    "Anulación venta #{$venta->numero_venta}",
                    $request->input('motivo')
                );
            }

            MovimientoCaja::create([
                'apertura_caja_id' => $venta->apertura_caja_id,
                'tipo' => 'reembolso',
                'monto' => $venta->total,
                'referencia_documento' => $venta->numero_venta,
                'concepto' => "Anulación venta #{$venta->numero_venta}",
                'usuario_id' => auth()->id(),
            ]);

            $venta->update([
                'estado' => 'anulada',
                'observaciones' => $request->input('motivo'),
            ]);
        });

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta anulada y stock devuelto correctamente.');
    }


    // ===================== REPORTE DIARIO =====================

    public function reporteDiario()
    {
        $ventas = Venta::whereDate('fecha_venta', today())
            ->completadas()
            ->with('paciente', 'usuario', 'detalles.producto')
            ->get();

        return view('ventas.reporte-diario', [
            'ventas' => $ventas,
            'totalVentas' => $ventas->sum('total'),
            'cantidadVentas' => $ventas->count(),
        ]);
    }


    // =========================================================
    // MÉTODOS PRIVADOS
    // =========================================================

    private function validarVenta(Request $request): array
    {
        return $request->validate([
            'paciente_id' => [
                'nullable',
                'integer',
                'exists:pacientes,id',
            ],

            'metodo_pago' => [
                'required',
                'in:efectivo,debito,credito,transferencia,cheque',
            ],

            'referencia_transaccion' => [
                'nullable',
                'string',
                'max:100',
            ],

            'detalles' => [
                'required',
                'array',
                'min:1',
            ],

            'detalles.*.producto_id' => [
                'required',
                'integer',
                'distinct',
                'exists:productos,id',
            ],

            'detalles.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

        ], [
            'detalles.required' =>
                'Debes agregar al menos un producto.',

            'detalles.min' =>
                'Debes agregar al menos un producto.',

            'detalles.*.producto_id.distinct' =>
                'Un producto no puede aparecer duplicado en la venta.',

            'detalles.*.producto_id.exists' =>
                'Uno de los productos seleccionados no existe.',

            'detalles.*.cantidad.min' =>
                'La cantidad debe ser mayor a cero.',

            'paciente_id.exists' =>
                'El paciente seleccionado no existe.',
        ]);
    }


    private function obtenerCajaAbierta(bool $bloquear = false)
    {
        $query = AperturaCaja::abiertas()
            ->where('usuario_id', auth()->id())
            ->latest('fecha_apertura');

        if ($bloquear) {
            $query->lockForUpdate();
        }

        return $query->first();
    }


    private function obtenerUbicacionVenta()
    {
        return UbicacionAlmacen::where('tipo', 'vitrina')
            ->where('activo', true)
            ->orderBy('id')
            ->first();
    }


    private function obtenerConveniosParaJs(): array
    {
        return Convenio::where('activo', true)
            ->with(
                'coberturas:id,convenio_id,producto_id,copago_pct,copago_fijo'
            )
            ->get()
            ->mapWithKeys(function (Convenio $convenio) {
                return [
                    $convenio->id => [
                        'nombre' => $convenio->nombre,
                        'descuento_pct' => (float) $convenio->descuento_pct,

                        'coberturas' => $convenio->coberturas
                            ->mapWithKeys(fn ($cobertura) => [
                                $cobertura->producto_id => [
                                    'copago_pct' =>
                                        $cobertura->copago_pct !== null
                                            ? (float) $cobertura->copago_pct
                                            : null,

                                    'copago_fijo' =>
                                        $cobertura->copago_fijo !== null
                                            ? (float) $cobertura->copago_fijo
                                            : null,
                                ],
                            ])
                            ->all(),
                    ],
                ];
            })
            ->all();
    }


    private function obtenerProductosDisponibles(UbicacionAlmacen $ubicacionVenta)
    {
        return Producto::activos()
            ->whereHas('stock', function ($query) use ($ubicacionVenta) {
                $query
                    ->where('ubicacion_id', $ubicacionVenta->id)
                    ->where('cantidad', '>', 0)
                    ->whereHas('lote', function ($lote) {
                        $lote
                            ->where('estado', 'activo')
                            ->whereDate('fecha_vencimiento', '>=', today());
                    });
            })
            ->with([
                'precios' => function ($query) {
                    $query
                        ->whereDate('vigente_desde', '<=', today())
                        ->where(function ($q) {
                            $q
                                ->whereNull('vigente_hasta')
                                ->orWhereDate('vigente_hasta', '>=', today());
                        })
                        ->orderByDesc('vigente_desde');
                },
            ])
            ->withSum([
                'stock as disponible' => function ($query) use ($ubicacionVenta) {
                    $query
                        ->where('ubicacion_id', $ubicacionVenta->id)
                        ->where('cantidad', '>', 0)
                        ->whereHas('lote', function ($lote) {
                            $lote
                                ->where('estado', 'activo')
                                ->whereDate('fecha_vencimiento', '>=', today());
                        });
                },
            ], 'cantidad')
            ->orderBy('nombre')
            ->get()
            ->map(function (Producto $producto) {
                $precio = $producto->precios->first();

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'codigo' => $producto->codigo_interno,
                    'precio_neto' => $precio
                        ? (float) $precio->precio_neto
                        : 0,
                    'tiene_precio' => $precio !== null,
                    'disponible' => (int) ($producto->disponible ?? 0),
                    'requiere_receta' => (bool) $producto->requiere_receta,
                    'tipo_receta' => $producto->tipo_receta,
                    'codigo_barra' => $producto->codigo_barra,
                ];
            })
            ->filter(fn ($producto) =>
                $producto['disponible'] > 0
                && $producto['tiene_precio']
            )
            ->values();
    }


    private function obtenerPacienteYConvenio(?int $pacienteId): array
    {
        if (!$pacienteId) {
            return [null, null];
        }

        $paciente = Paciente::query()
            ->whereKey($pacienteId)
            ->where('activo', true)
            ->with([
                'convenio' => function ($query) {
                    $query
                        ->where('activo', true)
                        ->with(
                            'coberturas:id,convenio_id,producto_id,copago_pct,copago_fijo'
                        );
                },
            ])
            ->first();

        if (!$paciente) {
            throw ValidationException::withMessages([
                'paciente_id' =>
                    'El paciente seleccionado ya no está disponible.',
            ]);
        }

        return [$paciente, $paciente->convenio];
    }


    private function obtenerProductosParaVenta(array $productoIds)
    {
        return Producto::activos()
            ->whereIn('id', $productoIds)
            ->with([
                'precios' => function ($query) {
                    $query
                        ->whereDate('vigente_desde', '<=', today())
                        ->where(function ($q) {
                            $q
                                ->whereNull('vigente_hasta')
                                ->orWhereDate('vigente_hasta', '>=', today());
                        })
                        ->orderByDesc('vigente_desde');
                },
            ])
            ->get()
            ->keyBy('id');
    }


    private function obtenerStockFefo(
        array $productoIds,
        UbicacionAlmacen $ubicacionVenta
    ) {
        return Stock::query()
            ->select('stock.*')
            ->join('lotes', 'lotes.id', '=', 'stock.lote_id')
            ->whereIn('stock.producto_id', $productoIds)
            ->where('stock.ubicacion_id', $ubicacionVenta->id)
            ->where('stock.cantidad', '>', 0)
            ->where('lotes.estado', 'activo')
            ->whereDate('lotes.fecha_vencimiento', '>=', today())
            ->orderBy('stock.producto_id')
            ->orderBy('lotes.fecha_vencimiento')
            ->orderBy('lotes.id')
            ->lockForUpdate()
            ->get()
            ->groupBy('producto_id');
    }


    private function procesarDetalles(
        Venta $venta,
        $detallesSolicitud,
        $productos,
        $stocksPorProducto,
        UbicacionAlmacen $ubicacionVenta,
        ?Convenio $convenio
    ): array {
        $ahora = now();

        $detallesVenta = [];
        $movimientosStock = [];
        $stockActualizado = [];

        $subtotalVentaCentavos = 0;
        $descuentoVentaCentavos = 0;
        $impuestoVentaCentavos = 0;
        $totalVentaCentavos = 0;

        foreach ($detallesSolicitud as $detalle) {

            $productoId = (int) $detalle['producto_id'];
            $cantidadSolicitada = (int) $detalle['cantidad'];
            $producto = $productos->get($productoId);

            if ($producto->requiere_receta) {
                throw ValidationException::withMessages([
                    'detalles' =>
                        "El producto {$producto->nombre} requiere receta y no puede venderse como venta libre.",
                ]);
            }

            $precio = $producto->precios->first();

            if (!$precio) {
                throw ValidationException::withMessages([
                    'detalles' =>
                        "No existe un precio vigente para {$producto->nombre}.",
                ]);
            }

            $precioNormalCentavos =
                (int) round((float) $precio->precio_neto * 100);

            $beneficio = $this->calcularBeneficioConvenio(
                $convenio,
                $productoId,
                $precioNormalCentavos
            );

            $precioPacienteCentavos =
                $beneficio['precio_final_centavos'];

            $descuentoPct =
                $beneficio['descuento_pct'];

            $stocksProducto =
                $stocksPorProducto->get($productoId, collect());

            if (
                (int) $stocksProducto->sum('cantidad')
                < $cantidadSolicitada
            ) {
                throw ValidationException::withMessages([
                    'detalles' =>
                        "Stock insuficiente de {$producto->nombre} en vitrina.",
                ]);
            }

            $cantidadRestante = $cantidadSolicitada;

            foreach ($stocksProducto as $stock) {

                if ($cantidadRestante <= 0) {
                    break;
                }

                $cantidadLote = min(
                    $cantidadRestante,
                    (int) $stock->cantidad
                );

                $subtotalNormalCentavos =
                    $precioNormalCentavos * $cantidadLote;

                $subtotalConBeneficioCentavos =
                    $precioPacienteCentavos * $cantidadLote;

                $descuentoMontoCentavos =
                    $subtotalNormalCentavos
                    - $subtotalConBeneficioCentavos;

                $impuestoCentavos = (int) round(
                    $subtotalConBeneficioCentavos * 0.19
                );

                $totalLineaCentavos =
                    $subtotalConBeneficioCentavos
                    + $impuestoCentavos;

                $detallesVenta[] = [
                    'venta_id' => $venta->id,
                    'producto_id' => $productoId,
                    'lote_id' => $stock->lote_id,
                    'receta_detalle_id' => null,
                    'cantidad' => $cantidadLote,

                    // Precio histórico normal utilizado en esta venta.
                    'precio_unitario' =>
                        $precioNormalCentavos / 100,

                    'descuento_pct' => $descuentoPct,
                    'subtotal' =>
                        $subtotalConBeneficioCentavos / 100,
                    'impuesto' =>
                        $impuestoCentavos / 100,
                    'total' =>
                        $totalLineaCentavos / 100,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $stockActualizado[$stock->id] = [
                    'id' => $stock->id,
                    'producto_id' => $stock->producto_id,
                    'lote_id' => $stock->lote_id,
                    'ubicacion_id' => $stock->ubicacion_id,
                    'cantidad' =>
                        (int) $stock->cantidad - $cantidadLote,
                    'created_at' => $stock->created_at,
                    'updated_at' => $ahora,
                ];

                $movimientosStock[] = [
                    'stock_id' => $stock->id,
                    'producto_id' => $productoId,
                    'lote_id' => $stock->lote_id,
                    'ubicacion_id' => $ubicacionVenta->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad_movida' => $cantidadLote,
                    'usuario_id' => auth()->id(),
                    'referencia_documento' => $venta->numero_venta,
                    'motivo' => "Venta #{$venta->numero_venta}",
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $subtotalVentaCentavos +=
                    $subtotalNormalCentavos;

                $descuentoVentaCentavos +=
                    $descuentoMontoCentavos;

                $impuestoVentaCentavos +=
                    $impuestoCentavos;

                $totalVentaCentavos +=
                    $totalLineaCentavos;

                $cantidadRestante -= $cantidadLote;
            }
        }

        return [
            'detalles' => $detallesVenta,
            'movimientos' => $movimientosStock,
            'stock' => $stockActualizado,

            'subtotal' => $subtotalVentaCentavos,
            'descuento' => $descuentoVentaCentavos,
            'impuesto' => $impuestoVentaCentavos,
            'total' => $totalVentaCentavos,
        ];
    }


    private function guardarResultadoVenta(
        Venta $venta,
        array $resultado
    ): void {
        Stock::upsert(
            array_values($resultado['stock']),
            ['id'],
            ['cantidad', 'updated_at']
        );

        VentaDetalle::insert($resultado['detalles']);
        MovimientoStock::insert($resultado['movimientos']);

        $venta->update([
            'subtotal' => $resultado['subtotal'] / 100,
            'descuento' => $resultado['descuento'] / 100,
            'impuesto' => $resultado['impuesto'] / 100,
            'total' => $resultado['total'] / 100,
            'estado' => 'completada',
        ]);
    }


    private function registrarPagoCajaYDocumento(
        Venta $venta,
        AperturaCaja $aperturaCaja,
        array $validated
    ): void {
        Pago::create([
            'venta_id' => $venta->id,
            'metodo_pago' => $validated['metodo_pago'],
            'monto' => $venta->total,
            'referencia_transaccion' =>
                $validated['referencia_transaccion'] ?? null,
        ]);

        MovimientoCaja::create([
            'apertura_caja_id' => $aperturaCaja->id,
            'tipo' => 'venta',
            'monto' => $venta->total,
            'referencia_documento' => $venta->numero_venta,
            'concepto' => "Venta #{$venta->numero_venta}",
            'usuario_id' => auth()->id(),
        ]);

        DocumentoTributario::create([
            'venta_id' => $venta->id,
            'tipo' => 'boleta_electronica',
            'folio' => $venta->id,
            'fecha_emision' => now(),
            'pdf_path' => null,
            'estado_sii' => 'simulado',
            'track_id' => 'SIM-' . $venta->numero_venta,
        ]);
    }


    // ===================== CONVENIOS =====================

    private function calcularBeneficioConvenio(
        ?Convenio $convenio,
        int $productoId,
        int $precioNormalCentavos
    ): array {
        if (!$convenio || $precioNormalCentavos <= 0) {
            return [
                'precio_final_centavos' => $precioNormalCentavos,
                'descuento_pct' => 0,
            ];
        }

        $cobertura = $convenio->coberturas
            ->firstWhere('producto_id', $productoId);

        if ($cobertura) {

            /*
             * Se mantiene exactamente la regla que ya tenías:
             * copago fijo solo cuando es mayor a cero.
             */
            if (
                $cobertura->copago_fijo !== null
                && (float) $cobertura->copago_fijo > 0
            ) {
                $copagoCentavos =
                    (int) round((float) $cobertura->copago_fijo * 100);

                $precioFinalCentavos = min(
                    $precioNormalCentavos,
                    $copagoCentavos
                );

                $descuentoPct = (
                    ($precioNormalCentavos - $precioFinalCentavos)
                    / $precioNormalCentavos
                ) * 100;

                return [
                    'precio_final_centavos' =>
                        $precioFinalCentavos,

                    'descuento_pct' =>
                        round($descuentoPct, 2),
                ];
            }

            if ($cobertura->copago_pct !== null) {
                $copagoPct = max(
                    0,
                    min(
                        100,
                        (float) $cobertura->copago_pct
                    )
                );

                $precioFinalCentavos = (int) round(
                    $precioNormalCentavos
                    * ($copagoPct / 100)
                );

                return [
                    'precio_final_centavos' =>
                        $precioFinalCentavos,

                    'descuento_pct' =>
                        round(100 - $copagoPct, 2),
                ];
            }
        }

        $descuentoPct = max(
            0,
            min(
                100,
                (float) $convenio->descuento_pct
            )
        );

        $precioFinalCentavos = (int) round(
            $precioNormalCentavos
            * (1 - ($descuentoPct / 100))
        );

        return [
            'precio_final_centavos' =>
                $precioFinalCentavos,

            'descuento_pct' =>
                round($descuentoPct, 2),
        ];
    }
}