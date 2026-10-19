@extends('layouts.app')

@section('title', 'Recepción de mercadería')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
            <h1 class="h3 mb-1">
                Recepción REC-{{ str_pad($recepcion->id, 6, '0', STR_PAD_LEFT) }}
            </h1>
            <p class="text-muted mb-0">
                {{ $recepcion->fecha_recepcion->format('d/m/Y H:i') }}
            </p>
        </div>

        <a href="{{ route('compras.recepciones.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('recepcion'))
        <div class="alert alert-danger">{{ $errors->first('recepcion') }}</div>
    @endif

    @if($tieneControlados && $recepcion->estado === 'pendiente')
        <div class="alert alert-warning">
            <i class="bi bi-shield-exclamation me-2"></i>
            Esta recepción contiene medicamentos controlados y requiere revisión de un
            <strong>Químico Farmacéutico</strong>.
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <div class="text-muted small">Orden de compra</div>
                    <a href="{{ route('compras.show', $recepcion->ordenCompra) }}"
                       class="fw-semibold text-decoration-none">
                        {{ $recepcion->ordenCompra->numero_orden }}
                    </a>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">Proveedor</div>
                    <div class="fw-semibold">
                        {{ $recepcion->ordenCompra->proveedor->nombre_razon_social }}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">Registrado por</div>
                    <div class="fw-semibold">{{ $recepcion->usuario->name }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">Comprobante</div>
                    <div>{{ $recepcion->numero_comprobante ?? 'Sin comprobante' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">Estado</div>

                    @if($recepcion->estado === 'pendiente')
                        <span class="badge bg-warning text-dark">Pendiente de revisión</span>
                    @elseif($recepcion->estado === 'aprobada')
                        <span class="badge bg-success">Aprobada</span>
                    @else
                        <span class="badge bg-danger">Rechazada</span>
                    @endif
                </div>

                @if($recepcion->tipo_recepcion)
                    <div class="col-md-4">
                        <div class="text-muted small">Tipo de recepción</div>
                        <div class="fw-semibold">
                            {{ ucfirst($recepcion->tipo_recepcion) }}
                        </div>
                    </div>
                @endif

                @if($recepcion->revisadoPor)
                    <div class="col-md-4">
                        <div class="text-muted small">Revisado por</div>
                        <div class="fw-semibold">{{ $recepcion->revisadoPor->name }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Fecha revisión</div>
                        <div>
                            {{ $recepcion->fecha_revision?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @endif

                @if($recepcion->observaciones)
                    <div class="col-12">
                        <div class="text-muted small">Observaciones</div>
                        <div>{{ $recepcion->observaciones }}</div>
                    </div>
                @endif

                @if($recepcion->estado === 'rechazada')
                    <div class="col-12">
                        <div class="text-muted small">Motivo del rechazo</div>
                        <div class="text-danger fw-semibold">
                            {{ $recepcion->motivo_rechazo }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Productos recibidos</h5>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Lote informado</th>
                        <th>Fabricación</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio unitario</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($recepcion->detalles as $detalle)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $detalle->producto->nombre }}

                                    @if($detalle->producto->es_controlado)
                                        <span class="badge bg-danger ms-1">Controlado</span>
                                    @endif
                                </div>

                                <div class="small text-muted">
                                    {{ $detalle->producto->codigo_interno }}
                                </div>
                            </td>

                            <td>{{ $detalle->numero_lote }}</td>

                            <td>
                                {{ $detalle->fecha_fabricacion?->format('d/m/Y') ?? 'No informada' }}
                            </td>

                            <td>
                                {{ $detalle->fecha_vencimiento->format('d/m/Y') }}
                            </td>

                            <td class="text-end">
                                {{ $detalle->cantidad_recibida }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold">
                                ${{ number_format(
                                    $detalle->cantidad_recibida * $detalle->precio_unitario,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($recepcion->estado === 'pendiente')

        @if($puedeRevisar)

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Revisión de recepción</h5>
                </div>

                <div class="card-body">

                    <p class="text-muted">
                        Verifica físicamente cantidades, lotes, vencimientos y comprobante antes de aprobar.
                        Al aprobar, cada producto ingresará a bodega o a una ubicación frigorífica según su condición de almacenamiento.
                    </p>

                    <div class="d-flex gap-2 mb-4">

                        <form method="POST"
                              action="{{ route('compras.recepciones.aprobar', $recepcion) }}">
                            @csrf

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Aprobar recepción
                            </button>
                        </form>

                    </div>

                    <hr>

                    <form method="POST"
                          action="{{ route('compras.recepciones.rechazar', $recepcion) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">
                                Motivo del rechazo *
                            </label>

                            <textarea
                                name="motivo_rechazo"
                                class="form-control @error('motivo_rechazo') is-invalid @enderror"
                                rows="3"
                                required
                            >{{ old('motivo_rechazo') }}</textarea>

                            @error('motivo_rechazo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i>
                            Rechazar recepción
                        </button>
                    </form>

                </div>
            </div>

        @elseif($recepcion->usuario_id === auth()->id())

            <div class="alert alert-secondary">
                <i class="bi bi-info-circle me-2"></i>
                Registraste esta recepción. Por seguridad, debe ser revisada por otro usuario autorizado.
            </div>

        @else

            <div class="alert alert-secondary">
                <i class="bi bi-lock me-2"></i>
                No tienes permisos para revisar esta recepción.
            </div>

        @endif

    @endif

</div>
@endsection
