<?php

namespace App\Http\Controllers;

use App\Models\AperturaCaja;
use App\Models\Convenio;
use App\Models\DocumentoTributario;
use App\Models\MovimientoCaja;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    private const UBICACIONES_VENTA = [
        'ambiente' => 'vitrina',
        'refrigerado' => 'vitrina_refrigerada',
    ];

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
        $ubicacionesVenta = $this->obtenerUbicacionesVenta();

        $pacientes = Paciente::activos()
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        $conveniosParaJs = $this->obtenerConveniosParaJs();

        $productos = $this->obtenerProductosDisponibles($ubicacionesVenta);

        $recetasParaJs = RecetaDetalle::query()
            ->where('estado', 'pendiente')
            ->whereColumn('cantidad_dispensada', '<', 'cantidad_prescrita')
            ->whereHas('receta', fn ($q) => $q->vigentes()->whereHas('paciente', fn ($q) => $q->activos()))
            ->with('receta:id,numero_receta,paciente_id,tipo,fecha_emision,fecha_vencimiento')
            ->get()
            ->map(fn ($detalle) => [
                'id' => $detalle->id, 'receta_id' => $detalle->receta_id,
                'producto_id' => $detalle->producto_id,
                'paciente_id' => $detalle->receta->paciente_id,
                'numero' => $detalle->receta->numero_receta, 'tipo' => $detalle->receta->tipo,
                'cantidad_prescrita' => (int) $detalle->cantidad_prescrita,
                'cantidad_dispensada' => (int) $detalle->cantidad_dispensada,
                'disponible' => max(0, $detalle->cantidad_prescrita - $detalle->cantidad_dispensada),
                'emision' => $detalle->receta->fecha_emision->format('d/m/Y'),
                'vencimiento' => $detalle->receta->fecha_vencimiento->format('d/m/Y'),
            ])->values();

        $recetasConSaldo = $recetasParaJs->pluck('receta_id')->flip();
        $recetasPacienteParaJs = auth()->user()->can('gestionar_recetas')
            ? Receta::query()
                ->whereHas('paciente', fn ($q) => $q->activos())
                ->with('medico:id,nombres,apellidos')
                ->latest('fecha_emision')
                ->get()
                ->map(fn ($receta) => [
                    'paciente_id' => $receta->paciente_id,
                    'numero' => $receta->numero_receta,
                    'estado' => $receta->estado_visible,
                    'disponible' => $recetasConSaldo->has($receta->id),
                    'emision' => $receta->fecha_emision->format('d/m/Y'),
                    'vencimiento' => $receta->fecha_vencimiento->format('d/m/Y'),
                    'medico' => $receta->medico?->nombreCompleto() ?? 'Médico no disponible',
                    'url' => route('recetas.show', $receta),
                ])->values()
            : collect();

        return view('ventas.create', compact(
            'pacientes',
            'productos',
            'aperturaCaja',
            'ubicacionesVenta',
            'conveniosParaJs',
            'recetasParaJs',
            'recetasPacienteParaJs'
        ));
    }


    // ===================== GUARDAR =====================

    public function store(Request $request)
    {
        $validated = $this->validarVenta($request);
        try {
            $venta = DB::transaction(function () use ($validated) {

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

                $ubicacionesVenta = $this->obtenerUbicacionesVenta();
                $ubicacionesPorProducto = $productos->map(
                    fn (Producto $producto) => $this->obtenerUbicacionVenta($producto, $ubicacionesVenta)
                );
                $recetasDispensadas = $this->validarYDispensarRecetas($detallesSolicitud, $productos, $paciente);

                $stocksPorProducto = $this->obtenerStockFefo(
                    $productoIds,
                    $ubicacionesPorProducto
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
                    $ubicacionesPorProducto,
                    $convenio,
                    $recetasDispensadas
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

        DB::transaction(function () use ($venta, $request) {

            $venta = Venta::whereKey($venta->id)->lockForUpdate()->firstOrFail();
            if ($venta->estado !== 'completada') {
                throw ValidationException::withMessages(['venta' => 'Esta venta ya fue anulada o no puede anularse.']);
            }
            $venta->load('detalles.producto', 'detalles.lote');
            $ubicacionesVenta = $this->obtenerUbicacionesVenta();

            $cantidadesReceta = $venta->detalles->whereNotNull('receta_detalle_id')
                ->groupBy('receta_detalle_id')->map(fn ($lineas) => (int) $lineas->sum('cantidad'));
            if ($cantidadesReceta->isNotEmpty()) {
                // Mismo orden de bloqueo que en la dispensación: receta y luego detalle.
                Receta::whereIn('id', RecetaDetalle::select('receta_id')->whereIn('id', $cantidadesReceta->keys()))
                    ->orderBy('id')->lockForUpdate()->get();
                $detallesReceta = RecetaDetalle::whereIn('id', $cantidadesReceta->keys())
                    ->orderBy('id')->lockForUpdate()->get();
                if ($detallesReceta->count() !== $cantidadesReceta->count()) {
                    throw ValidationException::withMessages(['venta' => 'No se encontraron los detalles de receta para restituir el saldo.']);
                }
                foreach ($detallesReceta as $detalleReceta) {
                    $cantidad = $cantidadesReceta->get($detalleReceta->id);
                    if ($detalleReceta->cantidad_dispensada < $cantidad) {
                        throw ValidationException::withMessages(['venta' => 'El saldo dispensado de la receta es inconsistente.']);
                    }
                    $detalleReceta->cantidad_dispensada -= $cantidad;
                    if ($detalleReceta->estado !== 'cancelado') {
                        $detalleReceta->estado = $detalleReceta->cantidad_dispensada >= $detalleReceta->cantidad_prescrita
                            ? 'dispensado' : 'pendiente';
                    }
                    $detalleReceta->updated_at = now();
                }
                RecetaDetalle::upsert($detallesReceta->map(fn ($d) => $d->getAttributes())->all(), ['id'],
                    ['cantidad_dispensada', 'estado', 'updated_at']);
            }

            foreach ($venta->detalles as $detalle) {
                Stock::registrarEntrada(
                    $detalle->producto,
                    $detalle->lote,
                    $this->obtenerUbicacionVenta($detalle->producto, $ubicacionesVenta),
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
        }, 3);

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

            'detalles.*.receta_detalle_id' => ['nullable', 'integer', 'exists:receta_detalle,id'],

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

            'detalles.*.receta_detalle_id.exists' =>
                'El detalle de receta seleccionado no existe.',
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


    private function obtenerUbicacionesVenta()
    {
        return UbicacionAlmacen::whereIn('tipo', array_values(self::UBICACIONES_VENTA))
            ->where('activo', true)
            ->orderByDesc('id')
            ->get()->keyBy('tipo');
    }

    private function obtenerUbicacionVenta(Producto $producto, $ubicacionesVenta): UbicacionAlmacen
    {
        $tipo = self::UBICACIONES_VENTA[$producto->condicion_almacenamiento] ?? null;
        if (!$tipo) {
            throw ValidationException::withMessages([
                'ubicacion' => "La condición de almacenamiento de {$producto->nombre} no permite determinar su ubicación de venta.",
            ]);
        }
        $ubicacion = $ubicacionesVenta->get($tipo);
        if (!$ubicacion) {
            $nombre = $tipo === 'vitrina' ? 'Vitrina' : 'Vitrina Refrigerada';
            throw ValidationException::withMessages(['ubicacion' => "No existe una {$nombre} activa configurada."]);
        }
        return $ubicacion;
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


    private function obtenerProductosDisponibles($ubicacionesVenta)
    {
        if ($ubicacionesVenta->isEmpty()) return collect();

        $stockVendible = function ($query) use ($ubicacionesVenta) {
            $query->where('cantidad', '>', 0)
                ->whereHas('lote', fn ($lote) => $lote->where('estado', 'activo')
                    ->whereDate('fecha_vencimiento', '>=', today()))
                ->where(function ($q) use ($ubicacionesVenta) {
                    foreach (self::UBICACIONES_VENTA as $condicion => $tipo) {
                        if ($ubicacion = $ubicacionesVenta->get($tipo)) {
                            $q->orWhere(fn ($sub) => $sub->where('ubicacion_id', $ubicacion->id)
                                ->where('productos.condicion_almacenamiento', $condicion));
                        }
                    }
                });
        };

        return Producto::activos()
            ->whereHas('stock', $stockVendible)
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
                'stock as disponible' => $stockVendible,
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
        $ubicacionesPorProducto
    ) {
        return Stock::query()
            ->select('stock.*')
            ->with('lote')
            ->join('lotes', 'lotes.id', '=', 'stock.lote_id')
            ->whereIn('stock.producto_id', $productoIds)
            ->where(function ($query) use ($ubicacionesPorProducto) {
                foreach ($ubicacionesPorProducto as $productoId => $ubicacion) {
                    $query->orWhere(fn ($q) => $q->where('stock.producto_id', $productoId)
                        ->where('stock.ubicacion_id', $ubicacion->id));
                }
            })
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


    private function validarYDispensarRecetas($detallesSolicitud, $productos, ?Paciente $paciente)
    {
        foreach ($detallesSolicitud as $solicitud) {
            if (!$productos->get($solicitud['producto_id'])->requiere_receta && !empty($solicitud['receta_detalle_id'])) {
                throw ValidationException::withMessages(['detalles' => 'No corresponde asociar una receta a un producto de venta libre.']);
            }
        }
        $solicitudes = $detallesSolicitud->filter(fn ($d) => $productos->get($d['producto_id'])->requiere_receta);
        if ($solicitudes->isEmpty()) {
            return collect();
        }
        if (!$paciente) {
            throw ValidationException::withMessages(['paciente_id' => 'Selecciona un paciente para dispensar productos con receta.']);
        }
        $ids = $solicitudes->pluck('receta_detalle_id')->filter()->unique()->values();
        $recetas = Receta::whereIn('id', RecetaDetalle::select('receta_id')->whereIn('id', $ids))
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $detalles = RecetaDetalle::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

        foreach ($solicitudes as $solicitud) {
            $producto = $productos->get($solicitud['producto_id']);
            $detalle = $detalles->get($solicitud['receta_detalle_id'] ?? null);
            $receta = $detalle ? $recetas->get($detalle->receta_id) : null;
            $error = null;
            if (!$detalle || !$receta) {
                $error = "El producto {$producto->nombre} requiere una receta válida.";
            } elseif ((int) $receta->paciente_id !== (int) $paciente->id) {
                $error = 'La receta seleccionada pertenece a otro paciente.';
            } elseif ($receta->estaVencida()) {
                $error = 'La receta seleccionada está vencida.';
            } elseif ($receta->estado !== 'vigente') {
                $error = 'La receta seleccionada no está aprobada.';
            } elseif (!$receta->esValida()) {
                $error = 'La receta seleccionada todavía no está vigente.';
            } elseif ((int) $detalle->producto_id !== (int) $producto->id) {
                $error = 'La receta seleccionada no corresponde a este medicamento.';
            } elseif ($producto->tipo_receta !== 'ninguna' && $receta->tipo !== $producto->tipo_receta) {
                $error = "El tipo de receta no es compatible con {$producto->nombre}.";
            } elseif ($detalle->estado === 'cancelado') {
                $error = 'El medicamento está cancelado en la receta seleccionada.';
            } else {
                $saldo = max(0, (int) $detalle->cantidad_prescrita - (int) $detalle->cantidad_dispensada);
                if ((int) $solicitud['cantidad'] > $saldo) {
                    $error = "La receta solo permite dispensar {$saldo} unidades adicionales de {$producto->nombre}.";
                } elseif ($detalle->estado !== 'pendiente') {
                    $error = 'El detalle de receta seleccionado no está habilitado para dispensación.';
                }
            }
            if ($error) {
                throw ValidationException::withMessages(['detalles' => $error]);
            }
            $detalle->cantidad_dispensada += (int) $solicitud['cantidad'];
            $detalle->estado = $detalle->cantidad_dispensada >= $detalle->cantidad_prescrita ? 'dispensado' : 'pendiente';
            $detalle->updated_at = now();
        }
        RecetaDetalle::upsert($detalles->map(fn ($d) => $d->getAttributes())->values()->all(), ['id'],
            ['cantidad_dispensada', 'estado', 'updated_at']);

        return $solicitudes->mapWithKeys(fn ($d) => [(int) $d['producto_id'] => (int) $d['receta_detalle_id']]);
    }

    private function procesarDetalles(
        Venta $venta,
        $detallesSolicitud,
        $productos,
        $stocksPorProducto,
        $ubicacionesPorProducto,
        ?Convenio $convenio,
        $recetasDispensadas
    ): array {
        $ahora = now();

        $detallesVenta = [];

        $subtotalVentaCentavos = 0;
        $descuentoVentaCentavos = 0;
        $impuestoVentaCentavos = 0;
        $totalVentaCentavos = 0;

        foreach ($detallesSolicitud as $detalle) {

            $productoId = (int) $detalle['producto_id'];
            $cantidadSolicitada = (int) $detalle['cantidad'];
            $producto = $productos->get($productoId);
            $ubicacionVenta = $ubicacionesPorProducto->get($productoId);

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
                        "Stock insuficiente de {$producto->nombre} en {$ubicacionVenta->nombre}.",
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
                    'receta_detalle_id' => $recetasDispensadas->get($productoId),
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

                Stock::registrarSalida(
                    $producto,
                    $stock->lote,
                    $ubicacionVenta,
                    $cantidadLote,
                    auth()->id(),
                    'salida',
                    $venta->numero_venta,
                    "Venta #{$venta->numero_venta}"
                );

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
        VentaDetalle::insert($resultado['detalles']);

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
