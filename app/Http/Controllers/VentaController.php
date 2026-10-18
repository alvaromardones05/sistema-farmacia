<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\AperturaCaja;
use App\Models\UbicacionAlmacen;
use App\Models\Convenio;
use App\Models\Pago;
use App\Models\DocumentoTributario;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MovimientoStock;
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
        $aperturaCaja = AperturaCaja::abiertas()
            ->where('usuario_id', auth()->id())
            ->latest('fecha_apertura')
            ->first();

        $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        $pacientes = Paciente::activos()
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        $convenios = Convenio::where('activo', true)
            ->with([
                'coberturas:id,convenio_id,producto_id,copago_pct,copago_fijo',
            ])
            ->get();

        $conveniosParaJs = $convenios
            ->mapWithKeys(function (Convenio $convenio) {
                return [
                    $convenio->id => [
                        'nombre' => $convenio->nombre,
                        'descuento_pct' => (float) $convenio->descuento_pct,

                        'coberturas' => $convenio->coberturas
                            ->mapWithKeys(function ($cobertura) {
                                return [
                                    $cobertura->producto_id => [
                                        'copago_pct' => $cobertura->copago_pct !== null
                                            ? (float) $cobertura->copago_pct
                                            : null,

                                        'copago_fijo' => $cobertura->copago_fijo !== null
                                            ? (float) $cobertura->copago_fijo
                                            : null,
                                    ],
                                ];
                            })
                            ->all(),
                    ],
                ];
            })
            ->all();

        $productos = collect();

        if ($ubicacionVenta) {
            $productos = Producto::activos()

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
    $validated = $request->validate([
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

    $ubicacionVenta = UbicacionAlmacen::where('tipo', 'vitrina')
        ->where('activo', true)
        ->orderBy('id')
        ->first();

    if (!$ubicacionVenta) {
        return back()
            ->withInput()
            ->withErrors([
                'ubicacion' =>
                    'No existe una ubicación de tipo vitrina configurada.',
            ]);
    }

    try {
        $venta = DB::transaction(function () use (
            $validated,
            $ubicacionVenta
        ) {
            /*
             * La caja se comprueba otra vez al confirmar.
             * El lock evita que cambie mientras se procesa la venta.
             */
            $aperturaCaja = AperturaCaja::abiertas()
                ->where('usuario_id', auth()->id())
                ->latest('fecha_apertura')
                ->lockForUpdate()
                ->first();

            if (!$aperturaCaja) {
                throw ValidationException::withMessages([
                    'caja' =>
                        'No hay una caja abierta para el usuario actual.',
                ]);
            }

            $paciente = null;
            $convenio = null;

            if (!empty($validated['paciente_id'])) {
                $paciente = Paciente::query()
                    ->whereKey($validated['paciente_id'])
                    ->where('activo', true)
                    ->with([
                        'convenio' => function ($query) {
                            $query
                                ->where('activo', true)
                                ->with([
                                    'coberturas:id,convenio_id,producto_id,copago_pct,copago_fijo',
                                ]);
                        },
                    ])
                    ->first();

                if (!$paciente) {
                    throw ValidationException::withMessages([
                        'paciente_id' =>
                            'El paciente seleccionado ya no está disponible.',
                    ]);
                }

                $convenio = $paciente->convenio;
            }

            $detallesSolicitud = collect($validated['detalles']);

            $productoIds = $detallesSolicitud
                ->pluck('producto_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            /*
             * Todos los productos y todos sus precios vigentes
             * se cargan antes de recorrer la venta.
             */
            $productos = Producto::activos()
                ->whereIn('id', $productoIds)
                ->with([
                    'precios' => function ($query) {
                        $query
                            ->whereDate('vigente_desde', '<=', today())
                            ->where(function ($q) {
                                $q
                                    ->whereNull('vigente_hasta')
                                    ->orWhereDate(
                                        'vigente_hasta',
                                        '>=',
                                        today()
                                    );
                            })
                            ->orderByDesc('vigente_desde');
                    },
                ])
                ->get()
                ->keyBy('id');

            if ($productos->count() !== count($productoIds)) {
                throw ValidationException::withMessages([
                    'detalles' =>
                        'Uno o más productos ya no están disponibles.',
                ]);
            }

            /*
             * Se trae y bloquea todo el stock utilizable de la venta
             * en una sola consulta.
             *
             * El ORDER BY implementa FEFO.
             */
            $stocksPorProducto = Stock::query()
                ->select('stock.*')
                ->join(
                    'lotes',
                    'lotes.id',
                    '=',
                    'stock.lote_id'
                )
                ->whereIn(
                    'stock.producto_id',
                    $productoIds
                )
                ->where(
                    'stock.ubicacion_id',
                    $ubicacionVenta->id
                )
                ->where(
                    'stock.cantidad',
                    '>',
                    0
                )
                ->where(
                    'lotes.estado',
                    'activo'
                )
                ->whereDate(
                    'lotes.fecha_vencimiento',
                    '>=',
                    today()
                )
                ->orderBy('stock.producto_id')
                ->orderBy('lotes.fecha_vencimiento')
                ->orderBy('lotes.id')
                ->lockForUpdate()
                ->get()
                ->groupBy('producto_id');

            /*
             * Reduce el riesgo de que dos ventas generen
             * simultáneamente el mismo correlativo.
             */
            Venta::query()
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $venta = Venta::create([
                'numero_venta' =>
                    Venta::generarNumeroVenta(),

                'paciente_id' =>
                    $paciente?->id,

                'usuario_id' =>
                    auth()->id(),

                'apertura_caja_id' =>
                    $aperturaCaja->id,

                /*
                 * El convenio nunca viene desde el POST.
                 */
                'convenio_id' =>
                    $convenio?->id,

                'fecha_venta' =>
                    now(),

                'subtotal' => 0,
                'descuento' => 0,
                'impuesto' => 0,
                'total' => 0,

                'estado' =>
                    'pendiente',
            ]);

            $ahora = now();

            $detallesVenta = [];
            $movimientosStock = [];
            $stockActualizado = [];

            /*
             * Se calculan en centavos para minimizar errores
             * de punto flotante durante las operaciones.
             */
            $subtotalVentaCentavos = 0;
            $descuentoVentaCentavos = 0;
            $impuestoVentaCentavos = 0;
            $totalVentaCentavos = 0;

            foreach ($detallesSolicitud as $detalle) {
                $productoId =
                    (int) $detalle['producto_id'];

                $cantidadSolicitada =
                    (int) $detalle['cantidad'];

                $producto =
                    $productos->get($productoId);

                if ($producto->requiere_receta) {
                    throw ValidationException::withMessages([
                        'detalles' =>
                            "El producto {$producto->nombre} requiere receta y no puede venderse como venta libre.",
                    ]);
                }

                $precio =
                    $producto->precios->first();

                if (!$precio) {
                    throw ValidationException::withMessages([
                        'detalles' =>
                            "No existe un precio vigente para {$producto->nombre}.",
                    ]);
                }

                $precioNormalCentavos = (int) round(
                    (float) $precio->precio_neto * 100
                );

                $beneficio =
                    $this->calcularBeneficioConvenio(
                        $convenio,
                        $productoId,
                        $precioNormalCentavos
                    );

                $precioPacienteCentavos =
                    $beneficio['precio_final_centavos'];

                $descuentoPct =
                    $beneficio['descuento_pct'];

                $stocksProducto =
                    $stocksPorProducto->get(
                        $productoId,
                        collect()
                    );

                $stockDisponible =
                    (int) $stocksProducto->sum('cantidad');

                if (
                    $stockDisponible
                    < $cantidadSolicitada
                ) {
                    throw ValidationException::withMessages([
                        'detalles' =>
                            "Stock insuficiente de {$producto->nombre} en vitrina.",
                    ]);
                }

                $cantidadRestante =
                    $cantidadSolicitada;

                foreach ($stocksProducto as $stock) {
                    if ($cantidadRestante <= 0) {
                        break;
                    }

                    $cantidadLote = min(
                        $cantidadRestante,
                        (int) $stock->cantidad
                    );

                    $subtotalNormalCentavos =
                        $precioNormalCentavos
                        * $cantidadLote;

                    $subtotalConBeneficioCentavos =
                        $precioPacienteCentavos
                        * $cantidadLote;

                    $descuentoMontoCentavos =
                        $subtotalNormalCentavos
                        - $subtotalConBeneficioCentavos;

                    $impuestoCentavos =
                        (int) round(
                            $subtotalConBeneficioCentavos
                            * 0.19
                        );

                    $totalLineaCentavos =
                        $subtotalConBeneficioCentavos
                        + $impuestoCentavos;

                    $detallesVenta[] = [
                        'venta_id' =>
                            $venta->id,

                        'producto_id' =>
                            $productoId,

                        'lote_id' =>
                            $stock->lote_id,

                        'receta_detalle_id' =>
                            null,

                        'cantidad' =>
                            $cantidadLote,

                        'precio_unitario' =>
                            $precioNormalCentavos / 100,

                        'descuento_pct' =>
                            $descuentoPct,

                        /*
                         * Subtotal del detalle:
                         * valor neto después del beneficio.
                         */
                        'subtotal' =>
                            $subtotalConBeneficioCentavos / 100,

                        'impuesto' =>
                            $impuestoCentavos / 100,

                        'total' =>
                            $totalLineaCentavos / 100,

                        'created_at' =>
                            $ahora,

                        'updated_at' =>
                            $ahora,
                    ];

                    $stockActualizado[$stock->id] = [
                        'id' =>
                            $stock->id,

                        'producto_id' =>
                            $stock->producto_id,

                        'lote_id' =>
                            $stock->lote_id,

                        'ubicacion_id' =>
                            $stock->ubicacion_id,

                        'cantidad' =>
                            (int) $stock->cantidad
                            - $cantidadLote,

                        'created_at' =>
                            $stock->created_at,

                        'updated_at' =>
                            $ahora,
                    ];

                    $movimientosStock[] = [
                        'stock_id' =>
                            $stock->id,

                        'producto_id' =>
                            $productoId,

                        'lote_id' =>
                            $stock->lote_id,

                        'ubicacion_id' =>
                            $ubicacionVenta->id,

                        'tipo_movimiento' =>
                            'salida',

                        'cantidad_movida' =>
                            $cantidadLote,

                        'usuario_id' =>
                            auth()->id(),

                        'referencia_documento' =>
                            $venta->numero_venta,

                        'motivo' =>
                            "Venta #{$venta->numero_venta}",

                        'created_at' =>
                            $ahora,

                        'updated_at' =>
                            $ahora,
                    ];

                    $subtotalVentaCentavos +=
                        $subtotalNormalCentavos;

                    $descuentoVentaCentavos +=
                        $descuentoMontoCentavos;

                    $impuestoVentaCentavos +=
                        $impuestoCentavos;

                    $totalVentaCentavos +=
                        $totalLineaCentavos;

                    $cantidadRestante -=
                        $cantidadLote;
                }
            }

            /*
             * No hacemos UPDATE de stock ni INSERT de detalle
             * por cada iteración.
             */
            Stock::upsert(
                array_values($stockActualizado),
                ['id'],
                [
                    'cantidad',
                    'updated_at',
                ]
            );

            VentaDetalle::insert(
                $detallesVenta
            );

            MovimientoStock::insert(
                $movimientosStock
            );

            /*
             * Cabecera:
             *
             * subtotal = precio normal neto
             * descuento = monto cubierto/descontado
             * impuesto = IVA sobre lo que paga el paciente
             * total = monto final a pagar
             */
            $venta->update([
                'subtotal' =>
                    $subtotalVentaCentavos / 100,

                'descuento' =>
                    $descuentoVentaCentavos / 100,

                'impuesto' =>
                    $impuestoVentaCentavos / 100,

                'total' =>
                    $totalVentaCentavos / 100,

                'estado' =>
                    'completada',
            ]);

            Pago::create([
                'venta_id' =>
                    $venta->id,

                'metodo_pago' =>
                    $validated['metodo_pago'],

                'monto' =>
                    $venta->total,

                'referencia_transaccion' =>
                    $validated['referencia_transaccion']
                    ?? null,
            ]);

            MovimientoCaja::create([
                'apertura_caja_id' =>
                    $aperturaCaja->id,

                'tipo' =>
                    'venta',

                'monto' =>
                    $venta->total,

                'referencia_documento' =>
                    $venta->numero_venta,

                'concepto' =>
                    "Venta #{$venta->numero_venta}",

                'usuario_id' =>
                    auth()->id(),
            ]);

            DocumentoTributario::create([
                'venta_id' =>
                    $venta->id,

                'tipo' =>
                    'boleta_electronica',

                'folio' =>
                    $venta->id,

                'fecha_emision' =>
                    now(),

                'pdf_path' =>
                    null,

                'estado_sii' =>
                    'simulado',

                'track_id' =>
                    'SIM-' . $venta->numero_venta,
            ]);

            return $venta;
        }, 3);

    } catch (ValidationException $e) {
        throw $e;

    } catch (\Throwable $e) {
        report($e);

        return back()
            ->withInput()
            ->withErrors([
                'venta' =>
                    'No fue posible procesar la venta. Intenta nuevamente.',
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

        DB::transaction(function () use (
            $venta,
            $ubicacionVenta,
            $request
        ) {
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

    // ===================== CONVENIOS =====================

private function calcularBeneficioConvenio(
    ?Convenio $convenio,
    int $productoId,
    int $precioNormalCentavos
): array {
    if (!$convenio || $precioNormalCentavos <= 0) {
        return [
            'precio_final_centavos' =>
                $precioNormalCentavos,

            'descuento_pct' =>
                0,
        ];
    }

    $cobertura = $convenio->coberturas
        ->firstWhere(
            'producto_id',
            $productoId
        );

    if ($cobertura) {
        /*
         * Copago fijo tiene prioridad cuando es mayor a cero.
         *
         * copago_fijo = monto neto por unidad que paga el paciente.
         */
        if (
            $cobertura->copago_fijo !== null
            && (float) $cobertura->copago_fijo > 0
        ) {
            $copagoCentavos = (int) round(
                (float) $cobertura->copago_fijo
                * 100
            );

            $precioFinalCentavos = min(
                $precioNormalCentavos,
                $copagoCentavos
            );

            $descuentoPct =
                (
                    (
                        $precioNormalCentavos
                        - $precioFinalCentavos
                    )
                    / $precioNormalCentavos
                )
                * 100;

            return [
                'precio_final_centavos' =>
                    $precioFinalCentavos,

                'descuento_pct' =>
                    round($descuentoPct, 2),
            ];
        }

        /*
         * copago_pct representa lo que paga el paciente.
         *
         * Ejemplo:
         * copago_pct = 20
         * paciente paga 20%
         * cobertura/descuento equivalente = 80%
         */
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

    /*
     * Si el producto no tiene cobertura específica,
     * se aplica el descuento general del convenio.
     */
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