@extends('layouts.app')

@section('title', 'Detalle de recepción')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">
                Compras / Recepciones
            </p>

            <h1 class="h3 mb-1">
                Recepción #{{ $recepcion->id }}
            </h1>

            <p class="text-muted mb-0">
                Orden
                <strong>
                    {{ $recepcion->ordenCompra->numero_orden ?? '—' }}
                </strong>

                @if ($recepcion->ordenCompra?->proveedor)
                    · {{ $recepcion->ordenCompra->proveedor->nombre }}
                @endif
            </p>
        </div>

        <a
            href="{{ route('compras.recepciones.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ESTADO --}}
    @php
        $estadoClase = match($recepcion->estado) {
            'aprobada' => 'success',
            'rechazada' => 'danger',
            default => 'warning',
        };
    @endphp

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-3">
                    <small class="text-muted d-block">
                        Estado
                    </small>

                    <span class="badge text-bg-{{ $estadoClase }}">
                        {{ ucfirst($recepcion->estado) }}
                    </span>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">
                        Registrado por
                    </small>

                    <strong>
                        {{ $recepcion->usuario->name ?? '—' }}
                    </strong>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">
                        Fecha recepción
                    </small>

                    <strong>
                        {{ $recepcion->fecha_recepcion?->format('d/m/Y H:i') ?? '—' }}
                    </strong>
                </div>

                <div class="col-md-3">
                    <small class="text-muted d-block">
                        Comprobante
                    </small>

                    <strong>
                        {{ $recepcion->numero_comprobante ?: 'No informado' }}
                    </strong>
                </div>

            </div>

            @if ($recepcion->observaciones)
                <hr>

                <small class="text-muted d-block">
                    Observaciones generales
                </small>

                <div>
                    {{ $recepcion->observaciones }}
                </div>
            @endif

        </div>
    </div>

    @if ($recepcion->estado === 'pendiente')
        <div class="alert alert-info border d-flex gap-2">
            <i class="bi bi-info-circle fs-5"></i>

            <div>
                Revisa los lotes, cantidades aceptadas y rechazadas.
                El inventario todavía no ha sido modificado.
            </div>
        </div>
    @endif


    @foreach (
        $recepcion->detalles->groupBy('orden_compra_detalle_id')
        as $detallesProducto
    )

        @php
            $primero = $detallesProducto->first();
            $producto = $primero->producto;

            $totalRecibido =
                $detallesProducto->sum('cantidad_recibida');

            $totalAceptado =
                $detallesProducto->sum('cantidad_aceptada');

            $totalRechazado =
                $detallesProducto->sum('cantidad_rechazada');

            $refrigerado =
                $producto->condicion_almacenamiento
                === 'refrigerado';
        @endphp

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                    <div>
                        <h6 class="mb-0">
                            {{ $producto->nombre }}
                        </h6>

                        <small class="text-muted">
                            {{ $detallesProducto->count() }}
                            {{ $detallesProducto->count() === 1 ? 'lote' : 'lotes' }}
                            registrado(s)
                        </small>
                    </div>

                    <span class="badge text-bg-{{ $refrigerado ? 'info' : 'light' }} border">

                        <i class="bi {{ $refrigerado ? 'bi-snow' : 'bi-box-seam' }} me-1"></i>

                        {{ $refrigerado
                            ? 'Cámara Frigorífica'
                            : 'Bodega Central' }}
                    </span>

                </div>
            </div>

            <div class="table-responsive">

                <table class="table table-sm align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Lote / Serie</th>
                            <th>Recibida</th>
                            <th>Aceptada</th>
                            <th>Rechazada</th>
                            <th>Fabricación</th>
                            <th>Vencimiento</th>
                            <th>Observación</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($detallesProducto as $detalle)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $detalle->numero_lote }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $detalle->cantidad_recibida }}
                                </td>

                                <td class="text-success fw-semibold">
                                    {{ $detalle->cantidad_aceptada }}
                                </td>

                                <td>
                                    @if ($detalle->cantidad_rechazada > 0)
                                        <span class="text-danger fw-semibold">
                                            {{ $detalle->cantidad_rechazada }}
                                        </span>
                                    @else
                                        0
                                    @endif
                                </td>

                                <td>
                                    {{ $detalle->fecha_fabricacion?->format('d/m/Y') ?? '—' }}
                                </td>

                                <td>
                                    {{ $detalle->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                </td>

                                <td>
                                    {{ $detalle->observacion ?: '—' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot class="table-light">
                        <tr class="fw-semibold">
                            <td>Totales</td>
                            <td>{{ $totalRecibido }}</td>
                            <td class="text-success">
                                {{ $totalAceptado }}
                            </td>
                            <td class="{{ $totalRechazado > 0 ? 'text-danger' : '' }}">
                                {{ $totalRechazado }}
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>

                </table>
            </div>

        </div>

    @endforeach


    {{-- INFORMACIÓN DE REVISIÓN --}}
    @if ($recepcion->estado !== 'pendiente')

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">
                <h5 class="mb-0">
                    Resultado de revisión
                </h5>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <small class="text-muted d-block">
                            Revisado por
                        </small>

                        <strong>
                            {{ $recepcion->revisadoPor->name ?? '—' }}
                        </strong>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block">
                            Fecha de revisión
                        </small>

                        <strong>
                            {{ $recepcion->fecha_revision?->format('d/m/Y H:i') ?? '—' }}
                        </strong>
                    </div>

                </div>

                @if (
                    $recepcion->estado === 'rechazada'
                    && $recepcion->motivo_rechazo
                )

                    <hr>

                    <div>
                        <small class="text-muted d-block">
                            Motivo del rechazo
                        </small>

                        <div class="text-danger">
                            {{ $recepcion->motivo_rechazo }}
                        </div>
                    </div>

                @endif

            </div>
        </div>

    @endif


    {{-- REVISIÓN --}}
    @if ($puedeRevisar)

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">
                <h5 class="mb-0">
                    Revisar recepción
                </h5>
            </div>

            <div class="card-body">

                @if ($tieneControlados)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Esta recepción contiene medicamentos controlados.
                    </div>
                @endif

                <div class="row g-4">

                    {{-- APROBAR --}}
                    <div class="col-md-5">

                        <h6>Aprobar recepción</h6>

                        <p class="text-muted small">
                            Solo las cantidades aceptadas ingresarán al inventario.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('compras.recepciones.aprobar', $recepcion) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="btn btn-success"
                                onclick="return confirm('¿Confirmas la aprobación de esta recepción?')"
                            >
                                <i class="bi bi-check-lg me-1"></i>
                                Aprobar recepción
                            </button>
                        </form>

                    </div>

                    {{-- RECHAZAR --}}
                    <div class="col-md-7">

                        <form
                            method="POST"
                            action="{{ route('compras.recepciones.rechazar', $recepcion) }}"
                        >
                            @csrf

                            <label
                                for="motivo_rechazo"
                                class="form-label fw-semibold"
                            >
                                Rechazar recepción completa
                            </label>

                            <textarea
                                id="motivo_rechazo"
                                name="motivo_rechazo"
                                class="form-control mb-3"
                                rows="3"
                                maxlength="1000"
                                placeholder="Indica por qué debe realizarse nuevamente la recepción"
                                required
                            ></textarea>

                            <button
                                type="submit"
                                class="btn btn-outline-danger"
                            >
                                <i class="bi bi-x-lg me-1"></i>
                                Rechazar recepción
                            </button>

                        </form>

                    </div>

                </div>
            </div>
        </div>

    @endif

</div>
@endsection