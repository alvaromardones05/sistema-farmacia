@extends('layouts.app')

@section('title', 'Recibir mercadería')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
            <h1 class="h3 mb-1">Recibir mercadería</h1>
            <p class="text-muted mb-0">
                Orden {{ $orden->numero_orden }} · {{ $orden->proveedor->nombre_razon_social }}
            </p>
        </div>

        <a href="{{ route('compras.show', $orden) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    @if($errors->has('recepcion'))
        <div class="alert alert-danger">
            {{ $errors->first('recepcion') }}
        </div>
    @endif

    <div class="alert alert-light border mb-4">
        <i class="bi bi-box-arrow-in-down me-2"></i>
        El stock recibido ingresará automáticamente a
        <strong>{{ $bodega->nombre }}</strong>.
    </div>

    <form method="POST" action="{{ route('compras.recepciones.store', $orden) }}">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Datos de recepción</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">N° comprobante</label>
                        <input
                            type="text"
                            name="numero_comprobante"
                            class="form-control"
                            value="{{ old('numero_comprobante') }}"
                            placeholder="Factura, guía u otro documento"
                        >
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Observaciones</label>
                        <input
                            type="text"
                            name="observaciones"
                            class="form-control"
                            value="{{ old('observaciones') }}"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Productos pendientes</h5>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Solicitado</th>
                            <th>Recibido</th>
                            <th>Pendiente</th>
                            <th>Cantidad recibida</th>
                            <th>N° lote</th>
                            <th>Fabricación</th>
                            <th>Vencimiento</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($orden->detalles as $detalle)

                            @php
                                $pendiente = $detalle->cantidad_solicitada - $detalle->cantidad_recibida;
                            @endphp

                            @if($pendiente > 0)

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
                                        {{ $detalle->cantidad_solicitada }}
                                    </td>

                                    <td>
                                        {{ $detalle->cantidad_recibida }}
                                    </td>

                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            {{ $pendiente }}
                                        </span>
                                    </td>

                                    <td style="min-width:130px;">
                                        <input
                                            type="number"
                                            name="detalles[{{ $detalle->id }}][cantidad_recibida]"
                                            class="form-control"
                                            min="0"
                                            max="{{ $pendiente }}"
                                            value="{{ old("detalles.{$detalle->id}.cantidad_recibida", 0) }}"
                                        >
                                    </td>

                                    <td style="min-width:150px;">
                                        <input
                                            type="text"
                                            name="detalles[{{ $detalle->id }}][numero_lote]"
                                            class="form-control"
                                            value="{{ old("detalles.{$detalle->id}.numero_lote") }}"
                                        >
                                    </td>

                                    <td style="min-width:160px;">
                                        <input
                                            type="date"
                                            name="detalles[{{ $detalle->id }}][fecha_fabricacion]"
                                            class="form-control"
                                            value="{{ old("detalles.{$detalle->id}.fecha_fabricacion") }}"
                                        >
                                    </td>

                                    <td style="min-width:160px;">
                                        <input
                                            type="date"
                                            name="detalles[{{ $detalle->id }}][fecha_vencimiento]"
                                            class="form-control"
                                            value="{{ old("detalles.{$detalle->id}.fecha_vencimiento") }}"
                                        >
                                    </td>

                                </tr>

                            @endif

                        @endforeach

                    </tbody>

                </table>
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2">

            <a href="{{ route('compras.show', $orden) }}" class="btn btn-outline-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-down me-1"></i>
                Confirmar recepción
            </button>

        </div>

    </form>

</div>
@endsection