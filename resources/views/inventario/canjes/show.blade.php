@extends('layouts.app')

@section('content')
@php
    // Si tus columnas se llaman distinto, cámbialas solo aquí.
    $nombreProducto  = $canje->producto->nombre;
    $nombreUsuario   = fn($usuario) => $usuario?->name;
    $nombreUbicacion = fn($ubicacion) => $ubicacion?->nombre;

    $r         = $canje->resumen();
    $acciones  = $canje->accionesDisponibles();
    $esQuimico = auth()->user()->hasRole('Químico Farmacéutico');
    $esPropia  = (int) $canje->solicitado_por === (int) auth()->id();
    $puedeDecidir = $canje->estado === 'pendiente' && $esQuimico && !$esPropia;
    $fmt = fn($fecha) => $fecha?->format('d-m-Y H:i');

    $titulosAccion = [
        'respuesta'  => 'Registrar respuesta del distribuidor',
        'devolucion' => 'Registrar retorno de unidades rechazadas',
        'entrega'    => 'Entregar unidades al distribuidor',
        'reposicion' => 'Recibir reposición',
        'cerrar'     => 'Cerrar el canje',
    ];

    // Línea de tiempo con todo lo registrado
    $eventos = collect();
    $eventos->push([
        'fecha'   => $canje->fecha_solicitud,
        'titulo'  => 'Solicitud creada',
        'detalle' => $nombreUsuario($canje->solicitante) . ' pidió canjear ' . $canje->cantidad_solicitada . ' unidades. Motivo: ' . $canje->motivo_solicitud,
    ]);
    if ($canje->fecha_decision) {
        $eventos->push([
            'fecha'   => $canje->fecha_decision,
            'titulo'  => $canje->estado === 'rechazado' ? 'Solicitud rechazada' : 'Solicitud aprobada',
            'detalle' => $nombreUsuario($canje->decisor) . ': ' . $canje->motivo_decision
                . ($canje->estado === 'rechazado' ? '' : ' El stock pasó a la zona de Canje.'),
        ]);
    }

    if ($canje->movimientoSaldo) {
        $eventos->push([
            'fecha'   => $canje->movimientoSaldo->created_at,
            'titulo'  => 'Saldo sacado de la zona de Canje',
            'detalle' => ($canje->cantidad_original - $canje->cantidad_solicitada) . ' unidades fueron a '
                . $nombreUbicacion($canje->movimientoSaldo->ubicacion)
                . '. Solicitud original: ' . $canje->cantidad_original . ' unidades.',
        ]);
    }

    foreach ($canje->entregas as $entrega) {
        $distribuidor = $entrega->proveedor?->nombre_razon_social;
        $eventos->push([
            'fecha'   => $entrega->fecha_entrega,
            'titulo'  => 'Entrega al distribuidor',
            'detalle' => $entrega->cantidad_enviada . ' unidades' . ($distribuidor ? ' a ' . $distribuidor : '')
                . ', documento ' . $entrega->numero_documento . '. Registró ' . $nombreUsuario($entrega->usuario) . '.',
        ]);
        foreach ($entrega->respuestas as $respuesta) {
            $eventos->push([
                'fecha'   => $respuesta->fecha_respuesta,
                'titulo'  => 'Respuesta del distribuidor',
                'detalle' => $respuesta->cantidad_aceptada . ' aceptadas y ' . $respuesta->cantidad_rechazada
                    . ' rechazadas, documento ' . $respuesta->numero_documento . '. Registró ' . $nombreUsuario($respuesta->usuario) . '.',
            ]);
        }
        foreach ($entrega->devoluciones as $devolucion) {
            $eventos->push([
                'fecha'   => $devolucion->fecha_devolucion,
                'titulo'  => 'Retorno de unidades rechazadas',
                'detalle' => $devolucion->cantidad . ' unidades volvieron a la zona de Canje, documento '
                    . $devolucion->numero_documento . '. Registró ' . $nombreUsuario($devolucion->usuario) . '.',
            ]);
        }
    }
    foreach ($canje->reposiciones as $reposicion) {
        $eventos->push([
            'fecha'   => $reposicion->fecha_recepcion,
            'titulo'  => 'Reposición recibida',
            'detalle' => $reposicion->cantidad . ' unidades del lote ' . $reposicion->lote?->numero_lote
                . ', documento ' . $reposicion->numero_documento . '. Registró ' . $nombreUsuario($reposicion->usuario) . '.',
        ]);
    }
    if ($canje->fecha_cierre) {
        $eventos->push([
            'fecha'   => $canje->fecha_cierre,
            'titulo'  => 'Canje cerrado',
            'detalle' => 'Cerrado por ' . $nombreUsuario($canje->cerrador) . '.',
        ]);
    }
    $eventos = $eventos->sortBy(fn($evento) => $evento['fecha']->timestamp)->values();
@endphp

<div class="container-fluid">
    <a href="{{ route('inventario.canjes.index') }}" class="text-decoration-none">Volver a canjes</a>

    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mb-3">
        <h1 class="h3 mb-0">Canje #{{ $canje->id }}</h1>
        @include('inventario.canjes.partials.estado', ['estado' => $canje->estado])
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        {{-- Columna principal --}}
        <div class="col-lg-8">

            {{-- Qué sigue --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">Qué sigue</div>
                <div class="card-body">

                    @if($canje->estado === 'pendiente')
                        @if($puedeDecidir)
                            <p>
                                Al aprobar, {{ $r['solicitada'] }} unidades saldrán de {{ $nombreUbicacion($canje->ubicacionOrigen) }}
                                y pasarán a la zona de Canje. Desde ese momento no se podrán vender.
                            </p>
                            <form method="POST" action="{{ route('inventario.canjes.aprobar', $canje) }}">
                                @csrf
                                <label class="form-label">Motivo de la decisión</label>
                                <textarea name="motivo_decision" rows="2" maxlength="2000" required
                                          class="form-control mb-3">{{ old('motivo_decision') }}</textarea>
                                <button class="btn btn-success">Aprobar y mover a Canje</button>
                                <button type="submit" formaction="{{ route('inventario.canjes.rechazar', $canje) }}"
                                        class="btn btn-outline-danger">Rechazar solicitud</button>
                            </form>
                        @elseif($esPropia)
                            <p class="mb-0">
                                Esta solicitud espera que otro Químico Farmacéutico la autorice.
                                No puedes autorizar solicitudes creadas por ti. El stock no se mueve hasta entonces.
                            </p>
                        @else
                            <p class="mb-0">
                                Esta solicitud espera la autorización de un Químico Farmacéutico.
                                El stock no se mueve hasta entonces.
                            </p>
                        @endif

                    @elseif($canje->estado === 'rechazado')
                        <p class="mb-0">
                            Solicitud rechazada el {{ $fmt($canje->fecha_decision) }}. Motivo: {{ $canje->motivo_decision }}
                            El stock no fue modificado.
                        </p>

                    @elseif($canje->estado === 'completado')
                        <p class="mb-0">
                            Canje completado el {{ $fmt($canje->fecha_cierre) }}. No quedan operaciones pendientes.
                        </p>

                    @elseif(!$puedeOperar)
                        <p class="mb-0">
                            @if($canje->producto->es_controlado && !$esQuimico)
                                Este producto es controlado: solo un Químico Farmacéutico puede registrar operaciones del canje.
                            @else
                                Tu rol no permite registrar operaciones en este canje.
                            @endif
                        </p>

                    @else
                        <h2 class="h6 mb-3">{{ $titulosAccion[$acciones[0]] ?? '' }}</h2>
                        @include('inventario.canjes.partials.accion', ['accion' => $acciones[0]])
                    @endif
                </div>
            </div>

            {{-- Otras operaciones posibles al mismo tiempo --}}
            @if($puedeOperar && in_array($canje->estado, ['aprobado', 'en_proceso'], true) && count($acciones) > 1)
                <details class="card mb-3">
                    <summary class="card-header">
                        Otras operaciones disponibles ({{ count($acciones) - 1 }})
                    </summary>
                    <div class="card-body">
                        @foreach(array_slice($acciones, 1) as $otra)
                            <h2 class="h6 mb-3">{{ $titulosAccion[$otra] ?? '' }}</h2>
                            @include('inventario.canjes.partials.accion', ['accion' => $otra])
                            @if(!$loop->last)<hr>@endif
                        @endforeach
                    </div>
                </details>
            @endif

            {{-- Historial --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">Historial</div>
                <ul class="list-group list-group-flush">
                    @foreach($eventos as $evento)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between flex-wrap">
                                <span class="fw-semibold">{{ $evento['titulo'] }}</span>
                                <span class="text-muted small">{{ $fmt($evento['fecha']) }}</span>
                            </div>
                            <div class="text-muted">{{ $evento['detalle'] }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Columna lateral --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Datos del canje</div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Producto</dt>
                        <dd>{{ $nombreProducto }}</dd>
                        <dt>Lote original</dt>
                        <dd>
                            {{ $canje->loteOriginal->numero_lote }}
                            <div class="small text-muted">
                                Vence el {{ $canje->loteOriginal->fecha_vencimiento->format('d-m-Y') }}
                            </div>
                        </dd>
                        <dt>Ubicación de origen</dt>
                        <dd>{{ $nombreUbicacion($canje->ubicacionOrigen) }}</dd>
                        <dt>Solicitado por</dt>
                        <dd>{{ $nombreUsuario($canje->solicitante) }}</dd>
                        <dt>Motivo</dt>
                        <dd class="mb-0">{{ $canje->motivo_solicitud }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Unidades</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Solicitadas</span><strong>{{ $r['solicitada'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Entregadas al distribuidor</span><strong>{{ $r['enviada'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Aceptadas</span><strong>{{ $r['aceptada'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Rechazadas</span><strong>{{ $r['rechazada'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Repuestas</span><strong>{{ $r['repuesta'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>En zona de Canje ahora</span><strong>{{ (int) $canje->stock_en_canje }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
