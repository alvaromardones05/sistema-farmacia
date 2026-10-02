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
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>

    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">
                    <div class="text-muted small">Orden de compra</div>

                    <a
                        href="{{ route('compras.show', $recepcion->ordenCompra) }}"
                        class="fw-semibold text-decoration-none"
                    >
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
                    <div class="text-muted small">Responsable</div>

                    <div class="fw-semibold">
                        {{ $recepcion->usuario->name }}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">Comprobante</div>

                    <div>
                        {{ $recepcion->numero_comprobante ?? 'Sin comprobante' }}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">Estado</div>

                    @if($recepcion->estado === 'completa')
                        <span class="badge bg-success">Completa</span>
                    @else
                        <span class="badge bg-primary">Parcial</span>
                    @endif
                </div>

                @if($recepcion->observaciones)
                    <div class="col-12">
                        <div class="text-muted small">Observaciones</div>
                        <div>{{ $recepcion->observaciones }}</div>
                    </div>
                @endif

            </div>

        </div>

    </div>

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Productos recibidos</h5>
        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
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
                                </div>

                                <div class="small text-muted">
                                    {{ $detalle->producto->codigo_interno }}
                                </div>
                            </td>

                            <td>
                                {{ $detalle->lote->numero_lote }}
                            </td>

                            <td>
                                {{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}
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

</div>
@endsection