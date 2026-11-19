<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\CanjeDevolucion;
use App\Models\CanjeEntrega;
use App\Models\CanjeReposicion;
use App\Models\CanjeRespuestaEntrega;
use App\Models\LibroControlEstupefaciente;
use App\Models\Lote;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Stock;
use App\Models\UbicacionAlmacen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CanjeController extends Controller
{
    private const ESTADOS_ABIERTOS = ['pendiente', 'aprobado', 'en_proceso'];

    public function index(Request $request)
    {
        $filtro = $request->query('estado', 'activos');
        $grupos = [
            'activos'    => ['aprobado', 'en_proceso'],
            'pendiente'  => ['pendiente'],
            'completado' => ['completado'],
            'rechazado'  => ['rechazado'],
        ];

        $canjes = Canje::with(['producto', 'loteOriginal', 'solicitante', 'entregas', 'devoluciones', 'reposiciones'])
            ->when(isset($grupos[$filtro]), fn($query) => $query->whereIn('estado', $grupos[$filtro]))
            ->latest('fecha_solicitud')
            ->paginate(15)
            ->withQueryString();

        $porEstado = Canje::selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $conteos = collect($grupos)->map(
            fn($estados) => collect($estados)->sum(fn($estado) => (int) ($porEstado[$estado] ?? 0))
        );

        return view('inventario.canjes.index', compact('canjes', 'filtro', 'conteos'));
    }

    public function create(Request $request, Lote $lote)
    {
        $producto = $lote->producto;
        $dias = (int) $request->query('dias', 60);

        abort_unless(
            $producto
                && $producto->activo
                && $lote->estado === 'activo'
                && in_array($dias, [15, 30, 60, 90], true)
                && $lote->fecha_vencimiento->between(today(), today()->addDays($dias)),
            404
        );

        $tiposOrigen = $this->tiposCompatibles($producto);
        $stocks = Stock::with('ubicacion')
            ->where('producto_id', $producto->id)
            ->where('lote_id', $lote->id)
            ->where('cantidad', '>', 0)
            ->whereHas('ubicacion', fn($query) => $query
                ->where('activo', true)
                ->whereIn('tipo', $tiposOrigen))
            ->orderBy('ubicacion_id')
            ->get();

        abort_if($stocks->isEmpty(), 404);

        return view('inventario.canjes.solicitar', [
            'lote' => $lote,
            'producto' => $producto,
            'stocks' => $stocks,
            'dias' => $dias,
            'idempotencyTokens' => $stocks->mapWithKeys(fn($stock) => [
                $stock->ubicacion_id => (string) Str::uuid(),
            ]),
        ]);
    }

    public function store(Request $request, Lote $lote)
    {
        $validated = $request->validate([
            'ubicacion_origen_id' => 'required|integer|exists:ubicaciones_almacen,id',
            'cantidad_solicitada' => 'required|integer|min:1',
            'motivo_solicitud' => 'required|string|max:2000',
            'dias' => 'required|integer|in:15,30,60,90',
            'idempotency_token' => 'required|uuid',
        ]);

        try {
            $canje = DB::transaction(function () use ($validated, $lote) {
                $existente = Canje::where('idempotency_token', $validated['idempotency_token'])->first();
                if ($existente) {
                    return $existente;
                }

                $lote = Lote::whereKey($lote->id)->lockForUpdate()->firstOrFail();
                $producto = Producto::whereKey($lote->producto_id)->firstOrFail();
                $ubicacion = UbicacionAlmacen::whereKey($validated['ubicacion_origen_id'])
                    ->where('activo', true)
                    ->firstOrFail();

                if (
                    !$producto->activo
                    || $lote->estado !== 'activo'
                    || !$lote->fecha_vencimiento->between(today(), today()->addDays((int) $validated['dias']))
                    || !in_array($ubicacion->tipo, $this->tiposCompatibles($producto), true)
                ) {
                    throw ValidationException::withMessages([
                        'lote' => 'El lote, producto o ubicación ya no cumple las condiciones para solicitar el canje.',
                    ]);
                }

                $stock = Stock::where('producto_id', $producto->id)
                    ->where('lote_id', $lote->id)
                    ->where('ubicacion_id', $ubicacion->id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->cantidad < $validated['cantidad_solicitada']) {
                    throw ValidationException::withMessages([
                        'cantidad_solicitada' => 'La cantidad solicitada supera el stock disponible en esa ubicación.',
                    ]);
                }

                $duplicado = Canje::where('lote_original_id', $lote->id)
                    ->where('ubicacion_origen_id', $ubicacion->id)
                    ->whereIn('estado', self::ESTADOS_ABIERTOS)
                    ->exists();

                if ($duplicado) {
                    throw ValidationException::withMessages([
                        'lote' => 'Ya existe una solicitud abierta para este lote y ubicación.',
                    ]);
                }

                return Canje::create([
                    'producto_id' => $producto->id,
                    'lote_original_id' => $lote->id,
                    'ubicacion_origen_id' => $ubicacion->id,
                    'cantidad_solicitada' => $validated['cantidad_solicitada'],
                    'solicitado_por' => auth()->id(),
                    'fecha_solicitud' => now(),
                    'motivo_solicitud' => $validated['motivo_solicitud'],
                    'idempotency_token' => $validated['idempotency_token'],
                    'estado' => 'pendiente',
                ]);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['canje' => 'No se pudo registrar la solicitud. No se modificó el stock.']);
        }

        return redirect()->route('inventario.canjes.show', $canje)
            ->with('success', 'Solicitud de canje registrada. El stock no se descontará hasta su aprobación.');
    }

    public function show(Canje $canje)
    {
        $canje->load([
            'producto', 'loteOriginal', 'ubicacionOrigen', 'solicitante', 'decisor', 'cerrador',
            'movimientoSalida.ubicacion', 'movimientoSalida.usuario',
            'movimientoEntrada.ubicacion',
            'entregas.usuario', 'entregas.proveedor', 'entregas.movimiento.ubicacion',
            'entregas.respuestas.usuario', 'entregas.respuestas.proveedor', 'entregas.devoluciones.usuario',
            'entregas.devoluciones.proveedor', 'entregas.devoluciones.movimiento.ubicacion',
            'reposiciones.lote', 'reposiciones.usuario', 'reposiciones.proveedor', 'reposiciones.movimiento.ubicacion','movimientoSaldo.ubicacion'
        ]);

        $canje->cantidad_enviada = $canje->entregas->sum('cantidad_enviada');
        $canje->cantidad_aceptada = $canje->entregas->sum('cantidad_aceptada');
        $canje->cantidad_rechazada = $canje->entregas->sum('cantidad_rechazada');
        $canje->cantidad_pendiente_respuesta = $canje->entregas
            ->sum(fn($entrega) => $entrega->cantidad_enviada - $entrega->cantidad_aceptada - $entrega->cantidad_rechazada);
        $canje->cantidad_rechazada_devuelta = $canje->entregas
            ->sum(fn($entrega) => $entrega->devoluciones->sum('cantidad'));
        $canje->cantidad_rechazada_pendiente = $canje->cantidad_rechazada - $canje->cantidad_rechazada_devuelta;
        $canje->cantidad_repuesta = $canje->reposiciones->sum('cantidad');
        $tokenDevoluciones = $canje->entregas->mapWithKeys(fn($entrega) => [
            $entrega->id => (string) Str::uuid(),
        ]);
        $canje->stock_en_canje = Stock::query()
            ->where('producto_id', $canje->producto_id)
            ->where('lote_id', $canje->lote_original_id)
            ->whereHas('ubicacion', fn($query) => $query->where('tipo', 'canje'))
            ->sum('cantidad');

        return view('inventario.canjes.show', [
            'canje' => $canje,
            'proveedores' => Proveedor::activos()->orderBy('nombre_razon_social')->get(),
            'tokenDevoluciones' => $tokenDevoluciones,
            'canjeDisponible' => $canje->estado === 'aprobado' || $canje->estado === 'en_proceso',
            'puedeOperar' => (auth()->user()->can('gestionar_stock') || auth()->user()->can('autorizar_libro_isp'))
                && (!$canje->producto->es_controlado || auth()->user()->hasRole('Químico Farmacéutico')),
            'tokenEntrega' => (string) Str::uuid(),
            'tokenReposicion' => (string) Str::uuid(),
        ]);
    }

    public function aprobar(Request $request, Canje $canje)
    {
        $usuario = auth()->user();
        abort_unless($usuario->hasRole('Químico Farmacéutico'), 403);
        $validated = $request->validate([
            'motivo_decision' => 'required|string|max:2000',
        ]);

        try {
            DB::transaction(function () use ($canje, $usuario, $validated) {
                $canje = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
                if ($canje->estado !== 'pendiente') {
                    throw ValidationException::withMessages(['canje' => 'La solicitud ya fue revisada.']);
                }
                if ((int) $canje->solicitado_por === (int) $usuario->id) {
                    throw ValidationException::withMessages(['canje' => 'No puedes autorizar una solicitud creada por ti.']);
                }

                $producto = Producto::findOrFail($canje->producto_id);
                $lote = Lote::whereKey($canje->lote_original_id)->lockForUpdate()->firstOrFail();
                $origen = UbicacionAlmacen::whereKey($canje->ubicacion_origen_id)->firstOrFail();
                $destino = $this->ubicacionCanje();
                $stockOrigen = Stock::where('producto_id', $producto->id)
                    ->where('lote_id', $lote->id)
                    ->where('ubicacion_id', $origen->id)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$stockOrigen
                    || $stockOrigen->cantidad < $canje->cantidad_solicitada
                    || !$origen->activo
                    || $lote->estado !== 'activo'
                    || $lote->fecha_vencimiento->lt(today())
                    || !in_array($origen->tipo, $this->tiposCompatibles($producto), true)
                ) {
                    throw ValidationException::withMessages([
                        'canje' => 'El stock disponible o la ubicación de origen ya no permiten aprobar la solicitud.',
                    ]);
                }

                $salida = Stock::registrarSalida(
                    $producto,
                    $lote,
                    $origen,
                    $canje->cantidad_solicitada,
                    $usuario->id,
                    'transferencia',
                    "Canje #{$canje->id}",
                    'Traslado aprobado a ubicación Canje'
                );
                $this->registrarLibroControl($producto, $lote, $salida, 'salida', $usuario);

                $entrada = Stock::registrarEntrada(
                    $producto,
                    $lote,
                    $destino,
                    $canje->cantidad_solicitada,
                    $usuario->id,
                    "Canje #{$canje->id}",
                    'Ingreso aprobado a ubicación Canje'
                );
                $this->registrarLibroControl($producto, $lote, $entrada, 'entrada', $usuario);

                $canje->update([
                    'estado' => 'aprobado',
                    'movimiento_salida_id' => $salida->id,
                    'movimiento_entrada_id' => $entrada->id,
                    'decidido_por' => $usuario->id,
                    'fecha_decision' => now(),
                    'motivo_decision' => $validated['motivo_decision'],
                ]);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['canje' => 'No se pudo aprobar el canje. No se confirmó ningún movimiento.']);
        }

        return back()->with('success', 'Solicitud aprobada y trasladada a la ubicación Canje.');
    }

    public function rechazar(Request $request, Canje $canje)
    {
        abort_unless(auth()->user()->hasRole('Químico Farmacéutico'), 403);
        $validated = $request->validate(['motivo_decision' => 'required|string|max:2000']);

        DB::transaction(function () use ($canje, $validated) {
            $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
            if ($actual->estado !== 'pendiente') {
                throw ValidationException::withMessages(['canje' => 'La solicitud ya fue revisada.']);
            }
            if ((int) $actual->solicitado_por === (int) auth()->id()) {
                throw ValidationException::withMessages(['canje' => 'No puedes rechazar una solicitud creada por ti.']);
            }

            $actual->update([
                'estado' => 'rechazado',
                'decidido_por' => auth()->id(),
                'fecha_decision' => now(),
                'motivo_decision' => $validated['motivo_decision'],
            ]);
        });

        return back()->with('success', 'Solicitud rechazada. El stock no fue modificado.');
    }

    public function registrarEntrega(Request $request, Canje $canje)
    {
        $this->autorizarOperacionControlada($canje);
        $validated = $request->validate([
            'cantidad_enviada' => 'required|integer|min:1',
            'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('activo', true)],
            'numero_documento' => 'required|string|max:100',
            'idempotency_token' => 'required|uuid',
        ]);

        try {
            DB::transaction(function () use ($canje, $validated) {
                $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
                if (CanjeEntrega::where('canje_id', $actual->id)
                    ->where('idempotency_token', $validated['idempotency_token'])
                    ->exists()) {
                    return;
                }
                $this->validarAbierto($actual);
                $proveedorId = $validated['proveedor_id'] ?? null;
                $documentoKey = $this->documentoKey($validated['numero_documento'], $proveedorId);
                if ($this->documentoDuplicado(CanjeEntrega::class, $actual->id, $validated['numero_documento'], $proveedorId)) {
                    throw ValidationException::withMessages(['numero_documento' => 'Ese documento ya fue registrado en este canje.']);
                }

                $usuario = auth()->user();
                $producto = Producto::findOrFail($actual->producto_id);
                $lote = Lote::whereKey($actual->lote_original_id)->lockForUpdate()->firstOrFail();
                $canjeUbicacion = $this->ubicacionCanje();
                $aceptado = (int) $actual->entregas()->sum('cantidad_aceptada');
                $pendienteRespuesta = (int) $actual->entregas()
                    ->selectRaw('COALESCE(SUM(cantidad_enviada - cantidad_aceptada - cantidad_rechazada), 0) as cantidad_pendiente')
                    ->value('cantidad_pendiente');
                $disponibleParaEnviar = (int) $actual->cantidad_solicitada - $aceptado - $pendienteRespuesta;
                if ($validated['cantidad_enviada'] > $disponibleParaEnviar) {
                    throw ValidationException::withMessages(['cantidad_enviada' => 'El envío supera la cantidad aprobada disponible considerando las respuestas pendientes.']);
                }

                $stock = Stock::where('producto_id', $producto->id)
                    ->where('lote_id', $lote->id)
                    ->where('ubicacion_id', $canjeUbicacion->id)
                    ->lockForUpdate()
                    ->first();
                if (!$stock || $stock->cantidad < $validated['cantidad_enviada']) {
                    throw ValidationException::withMessages(['cantidad_enviada' => 'No hay stock suficiente en la ubicación Canje.']);
                }

                $salida = Stock::registrarSalida(
                    $producto,
                    $lote,
                    $canjeUbicacion,
                    $validated['cantidad_enviada'],
                    $usuario->id,
                    'salida',
                    $this->referenciaDocumento($actual),
                    'Entrega efectiva al distribuidor'
                );
                $this->registrarLibroControl($producto, $lote, $salida, 'salida', $usuario);

                CanjeEntrega::create([
                    'canje_id' => $actual->id,
                    'movimiento_stock_id' => $salida->id,
                    'cantidad' => $validated['cantidad_enviada'],
                    'cantidad_enviada' => $validated['cantidad_enviada'],
                    'cantidad_aceptada' => 0,
                    'cantidad_rechazada' => 0,
                    'fecha_entrega' => now(),
                    'usuario_id' => $usuario->id,
                    'proveedor_id' => $validated['proveedor_id'] ?? null,
                    'numero_documento' => trim($validated['numero_documento']),
                    'documento_key' => $documentoKey,
                    'idempotency_token' => $validated['idempotency_token'],
                ]);
                $actual->update(['estado' => 'en_proceso']);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['entrega' => 'No se pudo registrar la entrega. No se confirmó el movimiento.']);
        }

        return back()->with('success', 'Entrega registrada sin duplicar operaciones.');
    }

    public function registrarRespuestaEntrega(Request $request, Canje $canje, CanjeEntrega $entrega)
    {
        $this->autorizarOperacionControlada($canje);
        $validated = $request->validate([
            'cantidad_aceptada' => 'required|integer|min:0',
            'cantidad_rechazada' => 'required|integer|min:0',
            'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('activo', true)],
            'numero_documento' => 'required|string|max:100',
            'idempotency_token' => 'required|uuid',
        ]);

        if ((int) $validated['cantidad_aceptada'] + (int) $validated['cantidad_rechazada'] < 1) {
            throw ValidationException::withMessages(['cantidad_aceptada' => 'Registra al menos una unidad aceptada o rechazada.']);
        }

        try {
            DB::transaction(function () use ($canje, $entrega, $validated) {
                $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
                $entrega = CanjeEntrega::whereKey($entrega->id)
                    ->where('canje_id', $actual->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (CanjeRespuestaEntrega::where('canje_entrega_id', $entrega->id)
                    ->where('idempotency_token', $validated['idempotency_token'])
                    ->exists()) {
                    return;
                }
                $this->validarAbierto($actual);

                $proveedorId = $validated['proveedor_id'] ?? null;
                $documentoKey = $this->documentoKey($validated['numero_documento'], $proveedorId);
                if ($this->documentoDuplicado(
                    CanjeRespuestaEntrega::class,
                    $actual->id,
                    $validated['numero_documento'],
                    $proveedorId,
                    'canje_entrega_id',
                    $entrega->id
                )) {
                    throw ValidationException::withMessages(['numero_documento' => 'Ese documento de respuesta ya fue registrado para este envío.']);
                }

                $pendiente = (int) $entrega->cantidad_enviada
                    - (int) $entrega->cantidad_aceptada
                    - (int) $entrega->cantidad_rechazada;
                $resuelto = (int) $validated['cantidad_aceptada'] + (int) $validated['cantidad_rechazada'];
                if ($resuelto > $pendiente) {
                    throw ValidationException::withMessages(['cantidad_aceptada' => 'La respuesta supera las unidades de este envío pendientes de clasificación.']);
                }

                $aceptadoTotal = (int) $actual->entregas()->sum('cantidad_aceptada');
                if ($aceptadoTotal + (int) $validated['cantidad_aceptada'] > (int) $actual->cantidad_solicitada) {
                    throw ValidationException::withMessages(['cantidad_aceptada' => 'La aceptación supera la cantidad aprobada del canje.']);
                }

                $usuario = auth()->user();
                CanjeRespuestaEntrega::create([
                    'canje_id' => $actual->id,
                    'canje_entrega_id' => $entrega->id,
                    'cantidad_aceptada' => $validated['cantidad_aceptada'],
                    'cantidad_rechazada' => $validated['cantidad_rechazada'],
                    'fecha_respuesta' => now(),
                    'usuario_id' => $usuario->id,
                    'proveedor_id' => $proveedorId,
                    'numero_documento' => trim($validated['numero_documento']),
                    'documento_key' => $documentoKey,
                    'idempotency_token' => $validated['idempotency_token'],
                ]);

                $entrega->increment('cantidad_aceptada', (int) $validated['cantidad_aceptada']);
                $entrega->increment('cantidad_rechazada', (int) $validated['cantidad_rechazada']);
                $actual->update(['estado' => 'en_proceso']);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['respuesta' => 'No se pudo registrar la respuesta. No se modificaron sus cantidades.']);
        }

        return back()->with('success', 'Respuesta del distribuidor registrada.');
    }

    public function registrarDevolucion(Request $request, Canje $canje, CanjeEntrega $entrega)
    {
        $this->autorizarOperacionControlada($canje);
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1',
            'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('activo', true)],
            'numero_documento' => 'required|string|max:100',
            'idempotency_token' => 'required|uuid',
        ]);

        try {
            DB::transaction(function () use ($canje, $entrega, $validated) {
                $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
                $entrega = CanjeEntrega::whereKey($entrega->id)
                    ->where('canje_id', $actual->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (CanjeDevolucion::where('canje_entrega_id', $entrega->id)
                    ->where('idempotency_token', $validated['idempotency_token'])
                    ->exists()) {
                    return;
                }
                $this->validarAbierto($actual);

                $proveedorId = $validated['proveedor_id'] ?? null;
                $documentoKey = $this->documentoKey($validated['numero_documento'], $proveedorId);
                if ($this->documentoDuplicado(
                    CanjeDevolucion::class,
                    $actual->id,
                    $validated['numero_documento'],
                    $proveedorId,
                    'canje_entrega_id',
                    $entrega->id
                )) {
                    throw ValidationException::withMessages(['numero_documento' => 'Ese documento ya fue registrado en este canje.']);
                }

                $rechazado = (int) $entrega->cantidad_rechazada;
                $devuelto = (int) $entrega->devoluciones()->sum('cantidad');
                if ($validated['cantidad'] > $rechazado - $devuelto) {
                    throw ValidationException::withMessages(['cantidad' => 'La cantidad devuelta supera el rechazo pendiente de retorno físico.']);
                }

                $producto = Producto::findOrFail($actual->producto_id);
                $lote = Lote::whereKey($actual->lote_original_id)->lockForUpdate()->firstOrFail();
                $ubicacionCanje = $this->ubicacionCanje();
                $usuario = auth()->user();

                $entrada = Stock::registrarEntrada(
                    $producto,
                    $lote,
                    $ubicacionCanje,
                    $validated['cantidad'],
                    $usuario->id,
                    "Canje #{$actual->id} - {$validated['numero_documento']}",
                    'Retorno físico de unidades rechazadas por el distribuidor'
                );
                $this->registrarLibroControl($producto, $lote, $entrada, 'entrada', $usuario);

                CanjeDevolucion::create([
                    'canje_id' => $actual->id,
                    'canje_entrega_id' => $entrega->id,
                    'movimiento_stock_id' => $entrada->id,
                    'cantidad' => $validated['cantidad'],
                    'fecha_devolucion' => now(),
                    'usuario_id' => $usuario->id,
                    'proveedor_id' => $validated['proveedor_id'] ?? null,
                    'numero_documento' => trim($validated['numero_documento']),
                    'documento_key' => $documentoKey,
                    'idempotency_token' => $validated['idempotency_token'],
                ]);
                $actual->update(['estado' => 'en_proceso']);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['devolucion' => 'No se pudo registrar el retorno físico. No se confirmó el movimiento.']);
        }

        return back()->with('success', 'Retorno físico registrado en la ubicación Canje.');
    }

    public function registrarReposicion(Request $request, Canje $canje)
    {
        $this->autorizarOperacionControlada($canje);
        $validated = $request->validate([
            'lotes' => 'required|array|min:1|max:20',
            'lotes.*.numero_lote' => 'required|string|max:100|distinct',
            'lotes.*.cantidad' => 'required|integer|min:1',
            'lotes.*.fecha_fabricacion' => 'required|date|before_or_equal:today|before:lotes.*.fecha_vencimiento',
            'lotes.*.fecha_vencimiento' => 'required|date|after:today',
            'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('activo', true)],
            'numero_documento' => 'required|string|max:100',
            'idempotency_token' => 'required|uuid',
        ]);

        try {
            DB::transaction(function () use ($canje, $validated) {
                $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
                $lineas = array_values($validated['lotes']);
                // Cada línea necesita su propio token (la columna es única): se derivan del token del formulario
                $tokenLinea = fn(int $i) => \Ramsey\Uuid\Uuid::uuid5($validated['idempotency_token'], "linea-{$i}")->toString();

                if (CanjeReposicion::where('canje_id', $actual->id)
                    ->where('idempotency_token', $tokenLinea(0))
                    ->exists()) {
                    return;
                }
                $this->validarAbierto($actual);

                $proveedorId = $validated['proveedor_id'] ?? null;
                $documentoKey = $this->documentoKey($validated['numero_documento'], $proveedorId);
                $usuario = auth()->user();
                $producto = Producto::findOrFail($actual->producto_id);

                $entregado = (int) $actual->entregas()->sum('cantidad_aceptada');
                $repuesto = (int) $actual->reposiciones()->sum('cantidad');
                $totalNuevo = (int) array_sum(array_column($lineas, 'cantidad'));
                if ($entregado <= $repuesto || $repuesto + $totalNuevo > $entregado) {
                    throw ValidationException::withMessages(['lotes' => 'La reposición debe corresponder a unidades entregadas y no puede superar el saldo pendiente.']);
                }

                $loteOriginal = Lote::whereKey($actual->lote_original_id)->firstOrFail();

                $tipoUbicacion = $producto->condicion_almacenamiento === 'refrigerado' ? 'frigorifica' : 'bodega';
                $ubicacion = UbicacionAlmacen::where('tipo', $tipoUbicacion)
                    ->where('activo', true)
                    ->orderBy('id')
                    ->first();
                if (!$ubicacion) {
                    throw ValidationException::withMessages([
                        'lotes' => $tipoUbicacion === 'frigorifica'
                            ? 'No existe una ubicación frigorífica activa para almacenar la reposición.'
                            : 'No existe una bodega activa para almacenar la reposición.',
                    ]);
                }

                foreach ($lineas as $i => $linea) {
                    $numeroLote = trim($linea['numero_lote']);
                    $cantidad = (int) $linea['cantidad'];

                    if ($numeroLote === $loteOriginal->numero_lote) {
                        throw ValidationException::withMessages(["lotes.{$i}.numero_lote" => "El lote {$numeroLote} es el lote original: la reposición debe usar un lote distinto."]);
                    }

                    $lote = Lote::where('numero_lote', $numeroLote)->lockForUpdate()->first();
                    if ($lote) {
                        if ($this->documentoDuplicado(
                            CanjeReposicion::class,
                            $actual->id,
                            $validated['numero_documento'],
                            $proveedorId,
                            'lote_id',
                            $lote->id
                        )) {
                            throw ValidationException::withMessages(["lotes.{$i}.numero_documento" => "Ese documento ya fue registrado para el lote {$numeroLote} dentro del canje."]);
                        }

                        if (
                            (int) $lote->producto_id !== (int) $producto->id
                            || $lote->fecha_vencimiento->format('Y-m-d') !== $linea['fecha_vencimiento']
                            || ($lote->fecha_fabricacion && $lote->fecha_fabricacion->format('Y-m-d') !== $linea['fecha_fabricacion'])
                        ) {
                            throw ValidationException::withMessages(["lotes.{$i}.numero_lote" => "El lote {$numeroLote} ya existe con datos incompatibles."]);
                        }
                        $lote->increment('cantidad_inicial', $cantidad);
                        $lote->update([
                            'estado' => 'activo',
                            'fecha_fabricacion' => $lote->fecha_fabricacion ?? $linea['fecha_fabricacion'],
                        ]);
                    } else {
                        $lote = Lote::create([
                            'producto_id' => $producto->id,
                            'numero_lote' => $numeroLote,
                            'fecha_fabricacion' => $linea['fecha_fabricacion'],
                            'fecha_vencimiento' => $linea['fecha_vencimiento'],
                            'cantidad_inicial' => $cantidad,
                            'estado' => 'activo',
                            'observaciones' => "Reposición del canje #{$actual->id}.",
                        ]);
                    }

                    $entrada = Stock::registrarEntrada(
                        $producto,
                        $lote,
                        $ubicacion,
                        $cantidad,
                        $usuario->id,
                        $this->referenciaDocumento($actual),
                        'Reposición recibida por canje'
                    );
                    $this->registrarLibroControl($producto, $lote, $entrada, 'entrada', $usuario);

                    CanjeReposicion::create([
                        'canje_id' => $actual->id,
                        'movimiento_stock_id' => $entrada->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $cantidad,
                        'fecha_recepcion' => now(),
                        'usuario_id' => $usuario->id,
                        'proveedor_id' => $proveedorId,
                        'numero_documento' => trim($validated['numero_documento']),
                        'documento_key' => $documentoKey,
                        'idempotency_token' => $tokenLinea($i),
                    ]);
                }

                $actual->update(['estado' => 'en_proceso']);
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['reposicion' => 'No se pudo registrar la reposición. No se confirmó ningún movimiento.']);
        }

        return back()->with('success', 'Reposición registrada con ' . count($validated['lotes']) . ' lote(s) y sus movimientos de entrada.');
    }

    public function cerrar(Canje $canje)
    {
        $this->autorizarOperacionControlada($canje);
        DB::transaction(function () use ($canje) {
            $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
            $aceptado = (int) $actual->entregas()->sum('cantidad_aceptada');
            $rechazado = (int) $actual->entregas()->sum('cantidad_rechazada');
            $devuelto = (int) CanjeDevolucion::where('canje_id', $actual->id)->sum('cantidad');
            $repuesto = (int) $actual->reposiciones()->sum('cantidad');
            $stockEnCanje = Stock::query()
                ->where('producto_id', $actual->producto_id)
                ->where('lote_id', $actual->lote_original_id)
                ->whereHas('ubicacion', fn($query) => $query->where('tipo', 'canje'))
                ->sum('cantidad');

            $pendientes = [];
            $pendienteRespuesta = (int) $actual->entregas()
                ->selectRaw('COALESCE(SUM(cantidad_enviada - cantidad_aceptada - cantidad_rechazada), 0) as cantidad_pendiente')
                ->value('cantidad_pendiente');
            if ($pendienteRespuesta > 0) {
                $pendientes[] = "faltan {$pendienteRespuesta} unidades enviadas por clasificar como aceptadas o rechazadas";
            }
            if ($aceptado < (int) $actual->cantidad_solicitada) {
                $pendientes[] = 'faltan ' . ((int) $actual->cantidad_solicitada - $aceptado) . ' unidades aceptadas';
            }
            if ($rechazado > $devuelto) {
                $pendientes[] = 'faltan ' . ($rechazado - $devuelto) . ' unidades rechazadas por regresar físicamente';
            }
            if ($repuesto < $aceptado) {
                $pendientes[] = 'faltan ' . ($aceptado - $repuesto) . ' unidades de reposición';
            }
            if ($stockEnCanje > 0) {
                $pendientes[] = "quedan {$stockEnCanje} unidades del lote original en Canje por reenviar";
            }

            if (
                $actual->estado !== 'en_proceso'
                || $aceptado !== (int) $actual->cantidad_solicitada
                || $pendienteRespuesta !== 0
                || $rechazado !== $devuelto
                || $repuesto !== $aceptado
                || $stockEnCanje !== 0
            ) {
                throw ValidationException::withMessages([
                    'canje' => 'No se puede cerrar: ' . ($pendientes ? implode('; ', $pendientes) : 'el canje no está en un estado cerrable.'),
                ]);
            }

            $actual->update([
                'estado' => 'completado',
                'cerrado_por' => auth()->id(),
                'fecha_cierre' => now(),
            ]);
        });

        return back()->with('success', 'Canje cerrado.');
    }

    private function tiposCompatibles(Producto $producto): array
    {
        return match ($producto->condicion_almacenamiento) {
            'ambiente' => ['bodega', 'vitrina'],
            'refrigerado' => ['frigorifica', 'vitrina_refrigerada'],
            default => [],
        };
    }

    private function ubicacionCanje(): UbicacionAlmacen
    {
        return UbicacionAlmacen::where('tipo', 'canje')
            ->where('activo', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    private function validarAbierto(Canje $canje): void
    {
        if (!in_array($canje->estado, ['aprobado', 'en_proceso'], true)) {
            throw ValidationException::withMessages(['canje' => 'El canje no admite nuevas operaciones en su estado actual.']);
        }
    }

    private function documentoKey(string $numeroDocumento, ?int $proveedorId): string
    {
        return hash('sha256', ($proveedorId ?? 'sin-proveedor') . '|' . mb_strtolower(trim($numeroDocumento)));
    }

    private function documentoDuplicado(
        string $modelo,
        int $canjeId,
        string $numeroDocumento,
        ?int $proveedorId,
        ?string $scopeColumn = null,
        ?int $scopeId = null
    ): bool
    {
        $query = $modelo::query()
            ->where('canje_id', $canjeId)
            ->whereRaw('LOWER(TRIM(numero_documento)) = ?', [mb_strtolower(trim($numeroDocumento))]);

        if ($scopeColumn !== null) {
            $query->where($scopeColumn, $scopeId);
        }

        return $query
            ->when($proveedorId === null, fn($builder) => $builder->whereNull('proveedor_id'))
            ->when($proveedorId !== null, fn($builder) => $builder->where('proveedor_id', $proveedorId))
            ->exists();
    }

    private function referenciaDocumento(Canje $canje): string
    {
        return "Canje #{$canje->id}";
    }

    private function registrarLibroControl(
        Producto $producto,
        Lote $lote,
        MovimientoStock $movimiento,
        string $tipo,
        User $usuario
    ): void {
        if (!$producto->es_controlado) {
            return;
        }

        abort_unless($usuario->hasRole('Químico Farmacéutico'), 403);

        $observaciones = "{$movimiento->referencia_documento}: {$movimiento->motivo}";
        if ($tipo === 'entrada') {
            LibroControlEstupefaciente::registrarEntrada($producto, $lote, $movimiento, $usuario->id, $observaciones);
            return;
        }

        LibroControlEstupefaciente::registrarSalida($producto, $lote, $movimiento, $usuario->id, $observaciones);
    }

    private function autorizarOperacionControlada(Canje $canje): void
    {
        $producto = $canje->producto;
        abort_unless($producto, 404);

        if ($producto->es_controlado) {
            abort_unless(auth()->user()->hasRole('Químico Farmacéutico'), 403);
        }
    }


    public function cerrarSaldo(Canje $canje)
    {
        $this->autorizarOperacionControlada($canje);
        $resultado = null;

        try {
            DB::transaction(function () use ($canje, &$resultado) {
                $actual = Canje::whereKey($canje->id)->lockForUpdate()->firstOrFail();
                if ($actual->movimiento_saldo_id) {
                    return;
                }
                $this->validarAbierto($actual);

                $aceptado = (int) $actual->entregas()->sum('cantidad_aceptada');
                $rechazado = (int) $actual->entregas()->sum('cantidad_rechazada');
                $devuelto = (int) CanjeDevolucion::where('canje_id', $actual->id)->sum('cantidad');
                $pendienteRespuesta = (int) $actual->entregas()
                    ->selectRaw('COALESCE(SUM(cantidad_enviada - cantidad_aceptada - cantidad_rechazada), 0) as cantidad_pendiente')
                    ->value('cantidad_pendiente');
                $saldo = (int) $actual->cantidad_solicitada - $aceptado;

                if ($pendienteRespuesta > 0 || $rechazado > $devuelto) {
                    throw ValidationException::withMessages(['canje' => 'Primero registra la respuesta y el retorno de todos los envíos.']);
                }
                if ($saldo < 1) {
                    throw ValidationException::withMessages(['canje' => 'No quedan unidades por sacar de la zona de Canje.']);
                }

                $usuario = auth()->user();
                $producto = Producto::findOrFail($actual->producto_id);
                $lote = Lote::whereKey($actual->lote_original_id)->lockForUpdate()->firstOrFail();
                $canjeUbicacion = $this->ubicacionCanje();
                $stock = Stock::where('producto_id', $producto->id)
                    ->where('lote_id', $lote->id)
                    ->where('ubicacion_id', $canjeUbicacion->id)
                    ->lockForUpdate()
                    ->first();
                if (!$stock || $stock->cantidad < $saldo) {
                    throw ValidationException::withMessages(['canje' => 'No hay stock suficiente en la zona de Canje para mover el saldo.']);
                }

                $origen = UbicacionAlmacen::find($actual->ubicacion_origen_id);
                $vuelveAlOrigen = $lote->estado === 'activo'
                    && $lote->fecha_vencimiento->gt(today()->addDays(Canje::DIAS_MINIMOS_PARA_REINGRESO))
                    && $origen && $origen->activo
                    && in_array($origen->tipo, $this->tiposCompatibles($producto), true);
                $destino = $vuelveAlOrigen ? $origen : $this->ubicacionCuarentena();
                $motivo = $vuelveAlOrigen
                    ? 'Saldo de canje que vuelve a su ubicación de origen'
                    : 'Saldo de canje no aceptado, pasa a Cuarentena como no apto';

                $salida = Stock::registrarSalida(
                    $producto, $lote, $canjeUbicacion, $saldo, $usuario->id,
                    'transferencia', "Canje #{$actual->id}", $motivo
                );
                $this->registrarLibroControl($producto, $lote, $salida, 'salida', $usuario);

                $entrada = Stock::registrarEntrada(
                    $producto, $lote, $destino, $saldo, $usuario->id,
                    "Canje #{$actual->id}", $motivo
                );
                $this->registrarLibroControl($producto, $lote, $entrada, 'entrada', $usuario);

                $actual->update([
                    'cantidad_original' => $actual->cantidad_solicitada,
                    'cantidad_solicitada' => $aceptado,
                    'movimiento_saldo_id' => $entrada->id,
                    'estado' => 'en_proceso',
                ]);

                $resultado = $vuelveAlOrigen
                    ? "{$saldo} unidades volvieron a su ubicación de origen."
                    : "{$saldo} unidades pasaron a Cuarentena como no aptas.";
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['canje' => 'No se pudo mover el saldo. No se confirmó ningún movimiento.']);
        }

        return back()->with('success', $resultado ?? 'El saldo ya había sido registrado.');
    }

    private function ubicacionCuarentena(): UbicacionAlmacen
    {
        return UbicacionAlmacen::where('tipo', 'cuarentena')
            ->where('activo', true)
            ->orderBy('id')
            ->firstOr(function () {
                throw ValidationException::withMessages(['canje' => 'No existe una ubicación de Cuarentena activa.']);
            });
    }
}
