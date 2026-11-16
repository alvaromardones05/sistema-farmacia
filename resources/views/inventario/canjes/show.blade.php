@extends('layouts.app')

@section('title', 'Detalle de canje')

@section('content')
@php
    $enviado = (int) $canje->cantidad_enviada;
    $aceptado = (int) $canje->cantidad_aceptada;
    $rechazado = (int) $canje->cantidad_rechazada;
    $devuelto = (int) $canje->cantidad_rechazada_devuelta;
    $rechazoPendiente = (int) $canje->cantidad_rechazada_pendiente;
    $repuesto = (int) $canje->cantidad_repuesta;
    $pendienteAceptacion = max(0, (int) $canje->cantidad_solicitada - $aceptado);
    $pendienteRespuesta = (int) $canje->cantidad_pendiente_respuesta;
    $pendienteEnvio = max(0, $pendienteAceptacion - $pendienteRespuesta);
    $pendienteReposicion = max(0, $aceptado - $repuesto);
    $maxEnvio = min($pendienteEnvio, (int) $canje->stock_en_canje);
@endphp
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario · Canjes</p>
            <h1 class="h3 mb-0">Canje #{{ $canje->id }}</h1>
        </div>
        <a href="{{ route('inventario.canjes.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-start"><h2 class="h5">Solicitud</h2><span class="badge text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $canje->estado)) }}</span></div>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Producto</dt><dd class="col-sm-8">{{ $canje->producto->nombre }}</dd>
                    <dt class="col-sm-4">Lote original</dt><dd class="col-sm-8">{{ $canje->loteOriginal->numero_lote }}</dd>
                    <dt class="col-sm-4">Vencimiento</dt><dd class="col-sm-8">{{ $canje->loteOriginal->fecha_vencimiento->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Origen</dt><dd class="col-sm-8">{{ $canje->ubicacionOrigen->nombre }}</dd>
                    <dt class="col-sm-4">Cantidad solicitada</dt><dd class="col-sm-8">{{ $canje->cantidad_solicitada }}</dd>
                    <dt class="col-sm-4">Solicitante</dt><dd class="col-sm-8">{{ $canje->solicitante->name }} · {{ $canje->fecha_solicitud->format('d/m/Y H:i') }}</dd>
                    <dt class="col-sm-4">Motivo</dt><dd class="col-sm-8">{{ $canje->motivo_solicitud }}</dd>
                    @if ($canje->decisor)
                        <dt class="col-sm-4">Decisión</dt><dd class="col-sm-8">{{ $canje->decisor->name }} · {{ $canje->fecha_decision?->format('d/m/Y H:i') }} · {{ $canje->motivo_decision }}</dd>
                    @endif
                    @if ($canje->cerrador)
                        <dt class="col-sm-4">Cierre</dt><dd class="col-sm-8">{{ $canje->cerrador->name }} · {{ $canje->fecha_cierre?->format('d/m/Y H:i') }}</dd>
                    @endif
                </dl>
            </div></section>
        </div>
        <div class="col-lg-5">
            <section class="card border-0 shadow-sm h-100"><div class="card-body">
                <h2 class="h5">Seguimiento</h2>
                <dl class="row mb-0">
                    <dt class="col-7">Aprobada</dt><dd class="col-5 text-end">{{ $canje->estado === 'pendiente' || $canje->estado === 'rechazado' ? 0 : $canje->cantidad_solicitada }}</dd>
                    <dt class="col-7">En ubicación Canje</dt><dd class="col-5 text-end">{{ (int) $canje->stock_en_canje }}</dd>
                    <dt class="col-7">Enviada al distribuidor</dt><dd class="col-5 text-end">{{ $enviado }}</dd>
                    <dt class="col-7">Aceptada</dt><dd class="col-5 text-end">{{ $aceptado }}</dd>
                    <dt class="col-7">Rechazada</dt><dd class="col-5 text-end">{{ $rechazado }}</dd>
                    <dt class="col-7">Rechazada pendiente de retorno</dt><dd class="col-5 text-end">{{ $rechazoPendiente }}</dd>
                    <dt class="col-7">Rechazada devuelta a Canje</dt><dd class="col-5 text-end">{{ $devuelto }}</dd>
                    <dt class="col-7">Enviada pendiente de respuesta</dt><dd class="col-5 text-end">{{ $pendienteRespuesta }}</dd>
                    <dt class="col-7">Repuesta</dt><dd class="col-5 text-end">{{ $repuesto }}</dd>
                    <dt class="col-7">Pendiente de aceptación</dt><dd class="col-5 text-end">{{ $pendienteAceptacion }}</dd>
                    <dt class="col-7">Pendiente de reposición</dt><dd class="col-5 text-end">{{ $pendienteReposicion }}</dd>
                </dl>
                @if ($canje->estado === 'pendiente')
                    <p class="small text-muted mt-3 mb-0">La solicitud no reserva ni descuenta stock hasta que sea aprobada.</p>
                @endif
            </div></section>
        </div>
    </div>

    @role('Químico Farmacéutico')
        @if ($canje->estado === 'pendiente')
            <section class="card border-0 shadow-sm mb-4"><div class="card-body">
                <h2 class="h5">Revisión farmacéutica</h2>
                <div class="d-flex flex-wrap gap-3 align-items-start">
                    <form method="POST" action="{{ route('inventario.canjes.aprobar', $canje) }}" class="flex-grow-1">@csrf<div class="mb-2"><label for="motivo_aprobacion" class="form-label">Motivo de aprobación</label><input id="motivo_aprobacion" name="motivo_decision" class="form-control" maxlength="2000" required></div><button class="btn btn-success">Aprobar y trasladar a Canje</button></form>
                    <form method="POST" action="{{ route('inventario.canjes.rechazar', $canje) }}" class="flex-grow-1">@csrf<div class="row g-2"><div class="col-md-9"><label for="motivo_decision" class="form-label">Motivo del rechazo</label><input id="motivo_decision" name="motivo_decision" class="form-control" maxlength="2000" required></div><div class="col-md-3 d-flex align-items-end"><button class="btn btn-outline-danger">Rechazar</button></div></div></form>
                </div>
            </div></section>
        @endif
    @endrole

    @if ($canjeDisponible && $puedeOperar)
        <div class="row g-4 mb-4">
            @if ($maxEnvio > 0)
                <div class="col-lg-6">
                    <section class="card border-0 shadow-sm h-100"><div class="card-body">
                        <h2 class="h5">Salida al distribuidor</h2>
                        <p class="small text-muted">Registra la salida física. La aceptación/rechazo se registra después, cuando responda el distribuidor. Disponible para enviar: {{ $maxEnvio }}.</p>
                        <form method="POST" action="{{ route('inventario.canjes.entregas.store', $canje) }}">@csrf
                            <input type="hidden" name="idempotency_token" value="{{ $tokenEntrega }}">
                            <div class="mb-3"><label class="form-label" for="cantidad_enviada">Cantidad enviada</label><input class="form-control" type="number" id="cantidad_enviada" name="cantidad_enviada" min="1" max="{{ $maxEnvio }}" required></div>
                            <div class="mb-3"><label class="form-label" for="proveedor_entrega">Distribuidor</label><select class="form-select" id="proveedor_entrega" name="proveedor_id"><option value="">Sin proveedor registrado</option>@foreach ($proveedores as $proveedor)<option value="{{ $proveedor->id }}">{{ $proveedor->nombre_razon_social }}</option>@endforeach</select></div>
                            <div class="mb-3"><label class="form-label" for="documento_entrega">Folio/documento de entrega</label><input class="form-control" id="documento_entrega" name="numero_documento" maxlength="100" required></div>
                            <button class="btn btn-primary">Registrar salida</button>
                        </form>
                    </div></section>
                </div>
            @endif

            @if ($pendienteReposicion > 0)
                <div class="col-lg-6">
                    <section class="card border-0 shadow-sm h-100"><div class="card-body">
                        <h2 class="h5">Reposición recibida</h2>
                        <p class="small text-muted">Pendiente de reponer: {{ $pendienteReposicion }} unidades. Se ingresará a {{ $canje->producto->condicion_almacenamiento === 'refrigerado' ? 'Cámara Frigorífica' : 'Bodega' }} con un lote distinto del original.</p>
                        <form method="POST" action="{{ route('inventario.canjes.reposiciones.store', $canje) }}">@csrf
                            <input type="hidden" name="idempotency_token" value="{{ $tokenReposicion }}">
                            <div class="mb-3"><label class="form-label" for="cantidad_reposicion">Cantidad recibida</label><input class="form-control" type="number" id="cantidad_reposicion" name="cantidad" min="1" max="{{ $pendienteReposicion }}" required></div>
                            <div class="mb-3"><label class="form-label" for="numero_lote">Nuevo número de lote</label><input class="form-control" id="numero_lote" name="numero_lote" maxlength="100" required></div>
                            <div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label" for="fecha_fabricacion">Fabricación</label><input class="form-control" type="date" id="fecha_fabricacion" name="fecha_fabricacion" max="{{ today()->toDateString() }}" required></div><div class="col-md-6"><label class="form-label" for="fecha_vencimiento">Vencimiento</label><input class="form-control" type="date" id="fecha_vencimiento" name="fecha_vencimiento" min="{{ today()->addDay()->toDateString() }}" required></div></div>
                            <div class="mb-3"><label class="form-label" for="proveedor_reposicion">Distribuidor</label><select class="form-select" id="proveedor_reposicion" name="proveedor_id"><option value="">Sin proveedor registrado</option>@foreach ($proveedores as $proveedor)<option value="{{ $proveedor->id }}">{{ $proveedor->nombre_razon_social }}</option>@endforeach</select></div>
                            <div class="mb-3"><label class="form-label" for="documento_reposicion">Folio/documento de reposición</label><input class="form-control" id="documento_reposicion" name="numero_documento" maxlength="100" required></div>
                            <button class="btn btn-primary">Registrar reposición</button>
                        </form>
                    </div></section>
                </div>
            @endif
        </div>
    @endif

    @if ($canje->estado === 'en_proceso' && $aceptado === (int) $canje->cantidad_solicitada && $rechazoPendiente === 0 && $repuesto === $aceptado && (int) $canje->stock_en_canje === 0)
        @can('gestionar_stock')
            <form method="POST" action="{{ route('inventario.canjes.cerrar', $canje) }}" class="mb-4">@csrf<button class="btn btn-success">Cerrar canje completo</button></form>
        @elsecan('autorizar_libro_isp')
            <form method="POST" action="{{ route('inventario.canjes.cerrar', $canje) }}" class="mb-4">@csrf<button class="btn btn-success">Cerrar canje completo</button></form>
        @endcan
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Historial</h2></div>
        <div class="list-group list-group-flush">
            <div class="list-group-item"><strong>Solicitud</strong> · {{ $canje->solicitante->name }} · {{ $canje->fecha_solicitud->format('d/m/Y H:i') }} · {{ $canje->motivo_solicitud }}</div>
            @if ($canje->decisor)<div class="list-group-item"><strong>{{ $canje->estado === 'rechazado' ? 'Rechazo' : 'Autorización' }}</strong> · {{ $canje->decisor->name }} · {{ $canje->fecha_decision?->format('d/m/Y H:i') }} · {{ $canje->motivo_decision }}</div>@endif
            @if ($canje->movimientoSalida && $canje->movimientoEntrada)
                <div class="list-group-item"><strong>Traslado a Canje</strong> · {{ $canje->movimientoSalida->created_at->format('d/m/Y H:i') }} · {{ $canje->movimientoSalida->cantidad_movida }} un. · {{ $canje->movimientoSalida->ubicacion->nombre }} → {{ $canje->movimientoEntrada->ubicacion->nombre }} · {{ $canje->movimientoSalida->usuario->name }}</div>
            @endif
            @foreach ($canje->entregas->sortBy('fecha_entrega') as $entrega)
                @php $devueltoEntrega = $entrega->devoluciones->sum('cantidad'); @endphp
                @php $pendienteRespuestaEntrega = $entrega->cantidad_enviada - $entrega->cantidad_aceptada - $entrega->cantidad_rechazada; @endphp
                <div class="list-group-item"><strong>Envío al distribuidor</strong> · {{ $entrega->fecha_entrega->format('d/m/Y H:i') }} · Enviado: {{ $entrega->cantidad_enviada }} · Aceptado: {{ $entrega->cantidad_aceptada }} · Rechazado: {{ $entrega->cantidad_rechazada }} · Pendiente respuesta: {{ $pendienteRespuestaEntrega }} · {{ $entrega->usuario->name }} · {{ $entrega->movimiento->ubicacion->nombre }} · {{ $entrega->proveedor?->nombre_razon_social ?? 'Sin proveedor' }} · Folio {{ $entrega->numero_documento }}</div>
                @foreach ($entrega->respuestas->sortBy('fecha_respuesta') as $respuesta)
                    <div class="list-group-item ps-5"><strong>Respuesta del distribuidor</strong> · {{ $respuesta->fecha_respuesta->format('d/m/Y H:i') }} · Aceptado: {{ $respuesta->cantidad_aceptada }} · Rechazado: {{ $respuesta->cantidad_rechazada }} · {{ $respuesta->usuario->name }} · {{ $respuesta->proveedor?->nombre_razon_social ?? 'Sin proveedor' }} · Folio {{ $respuesta->numero_documento }}</div>
                @endforeach
                @if ($canjeDisponible && $puedeOperar && $pendienteRespuestaEntrega > 0)
                    <div class="list-group-item ps-5">
                        <form method="POST" action="{{ route('inventario.canjes.respuestas.store', [$canje, $entrega]) }}" class="row g-2 align-items-end">@csrf
                            <input type="hidden" name="idempotency_token" value="{{ $tokenDevoluciones[$entrega->id] }}">
                            <div class="col-sm-3"><label class="form-label" for="respuesta_aceptada_{{ $entrega->id }}">Aceptada</label><input class="form-control" type="number" id="respuesta_aceptada_{{ $entrega->id }}" name="cantidad_aceptada" min="0" max="{{ $pendienteRespuestaEntrega }}" value="0" required></div>
                            <div class="col-sm-3"><label class="form-label" for="respuesta_rechazada_{{ $entrega->id }}">Rechazada</label><input class="form-control" type="number" id="respuesta_rechazada_{{ $entrega->id }}" name="cantidad_rechazada" min="0" max="{{ $pendienteRespuestaEntrega }}" value="0" required></div>
                            <div class="col-sm-2"><label class="form-label" for="proveedor_respuesta_{{ $entrega->id }}">Distribuidor</label><select class="form-select" id="proveedor_respuesta_{{ $entrega->id }}" name="proveedor_id"><option value="">—</option>@foreach ($proveedores as $proveedor)<option value="{{ $proveedor->id }}">{{ $proveedor->nombre_razon_social }}</option>@endforeach</select></div>
                            <div class="col-sm-3"><label class="form-label" for="folio_respuesta_{{ $entrega->id }}">Folio de respuesta</label><input class="form-control" id="folio_respuesta_{{ $entrega->id }}" name="numero_documento" maxlength="100" required></div>
                            <div class="col-sm-1"><button class="btn btn-outline-primary">Registrar</button></div>
                        </form>
                    </div>
                @endif
                @foreach ($entrega->devoluciones->sortBy('fecha_devolucion') as $devolucion)
                    <div class="list-group-item ps-5"><strong>Retorno físico a Canje</strong> · {{ $devolucion->fecha_devolucion->format('d/m/Y H:i') }} · {{ $devolucion->cantidad }} un. · {{ $devolucion->usuario->name }} · Folio {{ $devolucion->numero_documento }}</div>
                @endforeach
                @if ($canjeDisponible && $puedeOperar && $devueltoEntrega < $entrega->cantidad_rechazada)
                    <div class="list-group-item ps-5">
                        <div class="small text-muted mb-2">Pendiente de retorno físico: {{ $entrega->cantidad_rechazada - $devueltoEntrega }} un. No están contabilizadas en stock de Canje.</div>
                        <form method="POST" action="{{ route('inventario.canjes.devoluciones.store', [$canje, $entrega]) }}" class="row g-2 align-items-end">@csrf
                            <input type="hidden" name="idempotency_token" value="{{ $tokenDevoluciones[$entrega->id] }}">
                            <div class="col-sm-3"><label class="form-label" for="cantidad_devolucion_{{ $entrega->id }}">Retorno físico</label><input class="form-control" type="number" id="cantidad_devolucion_{{ $entrega->id }}" name="cantidad" min="1" max="{{ $entrega->cantidad_rechazada - $devueltoEntrega }}" required></div>
                            <div class="col-sm-3"><label class="form-label" for="proveedor_devolucion_{{ $entrega->id }}">Distribuidor</label><select class="form-select" id="proveedor_devolucion_{{ $entrega->id }}" name="proveedor_id"><option value="">Sin proveedor registrado</option>@foreach ($proveedores as $proveedor)<option value="{{ $proveedor->id }}">{{ $proveedor->nombre_razon_social }}</option>@endforeach</select></div>
                            <div class="col-sm-4"><label class="form-label" for="documento_devolucion_{{ $entrega->id }}">Folio/documento de devolución</label><input class="form-control" id="documento_devolucion_{{ $entrega->id }}" name="numero_documento" maxlength="100" required></div>
                            <div class="col-sm-2"><button class="btn btn-outline-primary">Registrar retorno</button></div>
                        </form>
                    </div>
                @endif
            @endforeach
            @foreach ($canje->reposiciones->sortBy('fecha_recepcion') as $reposicion)
                <div class="list-group-item"><strong>Reposición</strong> · {{ $reposicion->fecha_recepcion->format('d/m/Y H:i') }} · {{ $reposicion->cantidad }} un. · Lote {{ $reposicion->lote->numero_lote }} vence {{ $reposicion->lote->fecha_vencimiento->format('d/m/Y') }} · {{ $reposicion->usuario->name }} · {{ $reposicion->movimiento->ubicacion->nombre }} · {{ $reposicion->proveedor?->nombre_razon_social ?? 'Sin proveedor' }} · {{ $reposicion->numero_documento ?? 'Sin documento' }}</div>
            @endforeach
            @if ($canje->cerrador)<div class="list-group-item"><strong>Cierre</strong> · {{ $canje->cerrador->name }} · {{ $canje->fecha_cierre?->format('d/m/Y H:i') }}</div>@endif
        </div>
    </section>
</div>
@endsection