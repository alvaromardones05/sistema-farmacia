@extends('layouts.app')

@section('title', 'Orden de compra')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
            <h1 class="h3 mb-1">Orden {{ $orden->numero_orden }}</h1>
            <p class="text-muted mb-0">
                Creada el {{ $orden->fecha_orden->format('d/m/Y H:i') }}
            </p>
        </div>

        <div class="d-flex gap-2">
            @if(in_array($orden->estado, ['pendiente', 'confirmada', 'parcial']))
                <a
                    href="{{ route('compras.recepciones.create', $orden) }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-box-arrow-in-down me-1"></i>
                    Recibir mercadería
                </a>
            @endif

            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Volver
            </a>

            @if($errors->has('recepcion'))
                <div class="alert alert-danger">
                    {{ $errors->first('recepcion') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Productos solicitados</h5>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3 mb-4">

        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="text-muted small">Proveedor</div>
                            <div class="fw-semibold">
                                {{ $orden->proveedor->nombre_razon_social }}
                            </div>
                            <div class="small">
                                {{ $orden->proveedor->rut }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="text-muted small">Entrega esperada</div>
                            <div class="fw-semibold">
                                {{ $orden->fecha_entrega_esperada?->format('d/m/Y') ?? 'Sin definir' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="text-muted small">Responsable</div>
                            <div class="fw-semibold">
                                {{ $orden->usuario->name }}
                            </div>
                        </div>

                        @if($orden->observaciones)
                            <div class="col-12">
                                <div class="text-muted small">Observaciones</div>
                                <div>{{ $orden->observaciones }}</div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="text-muted small mb-2">Estado de la orden</div>

                    @switch($orden->estado)

                        @case('pendiente')
                            <span class="badge bg-warning text-dark fs-6">Pendiente</span>
                            @break

                        @case('confirmada')
                            <span class="badge bg-info text-dark fs-6">Confirmada</span>
                            @break

                        @case('parcial')
                            <span class="badge bg-primary fs-6">Recepción parcial</span>
                            @break

                        @case('recibida')
                            <span class="badge bg-success fs-6">Recibida</span>
                            @break

                        @case('anulada')
                            <span class="badge bg-danger fs-6">Anulada</span>
                            @break

                    @endswitch

                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Productos solicitados</h5>
        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Solicitado</th>
                        <th class="text-end">Recibido</th>
                        <th class="text-end">Pendiente</th>
                        <th class="text-end">Precio unitario</th>
                        <th class="text-end">Subtotal</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    @php
                        $total = 0;
                    @endphp

                    @foreach($orden->detalles as $detalle)

                        @php
                            $subtotal = $detalle->cantidad_solicitada * $detalle->precio_unitario;
                            $pendiente = $detalle->cantidad_solicitada - $detalle->cantidad_recibida;
                            $total += $subtotal;
                        @endphp

                        <tr>

                            <td>
                                <div class="fw-semibold">
                                    {{ $detalle->producto->nombre }}
                                </div>

                                <div class="small text-muted">
                                    {{ $detalle->producto->codigo_interno }}
                                </div>
                            </td>

                            <td class="text-end">
                                {{ $detalle->cantidad_solicitada }}
                            </td>

                            <td class="text-end">
                                {{ $detalle->cantidad_recibida }}
                            </td>

                            <td class="text-end">
                                {{ $pendiente }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold">
                                ${{ number_format($subtotal, 0, ',', '.') }}
                            </td>

                            <td>
                                @if($detalle->estado_linea === 'recibida')
                                    <span class="badge bg-success">Recibida</span>

                                @elseif($detalle->estado_linea === 'parcial')
                                    <span class="badge bg-primary">Parcial</span>

                                @elseif($detalle->estado_linea === 'cancelada')
                                    <span class="badge bg-danger">Cancelada</span>

                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>

                        </tr>

                    @endforeach

                </tbody>

                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">
                            Total orden
                        </th>

                        <th class="text-end">
                            ${{ number_format($total, 0, ',', '.') }}
                        </th>

                        <th></th>
                    </tr>
                </tfoot>

            </table>

        </div>
    </div>

</div>
@endsection