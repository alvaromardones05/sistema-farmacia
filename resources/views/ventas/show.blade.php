@extends('layouts.app')

@section('title', 'Detalle de venta')

@section('content')

@php
    $pago = $venta->pagos->first();

    $metodosPago = [
        'efectivo' => 'Efectivo',
        'debito' => 'Débito',
        'credito' => 'Crédito',
        'transferencia' => 'Transferencia',
        'cheque' => 'Cheque',
    ];
@endphp

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="h3 mb-0">
            Venta {{ $venta->numero_venta }}
        </h1>

        <div class="d-flex gap-2">

            @if ($venta->documentoTributario)
                <a
                    href="{{ route('ventas.boleta', $venta) }}"
                    class="btn btn-primary"
                    target="_blank"
                >
                    Ver boleta
                </a>
            @endif

            <a
                href="{{ route('ventas.index') }}"
                class="btn btn-outline-secondary"
            >
                Volver
            </a>

        </div>

    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-3 mb-4">

        <div class="col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted text-uppercase small mb-3">
                        Datos de la venta
                    </h6>

                    <p class="mb-1">
                        <strong>Fecha:</strong>
                        {{ $venta->fecha_venta->format('d/m/Y H:i') }}
                    </p>

                    <p class="mb-1">
                        <strong>Paciente:</strong>

                        @if ($venta->paciente)
                            {{ $venta->paciente->nombreCompleto() }}
                        @else
                            Venta libre / consumidor final
                        @endif
                    </p>

                    <p class="mb-1">
                        <strong>Vendedor:</strong>
                        {{ $venta->usuario?->name }} {{ $venta->usuario?->apellidos }}
                    </p>

                    <p class="mb-1">
                        <strong>Convenio:</strong>
                        {{ $venta->convenio?->nombre ?? 'Particular' }}
                    </p>

                    <p class="mb-0">
                        <strong>Estado:</strong>

                        <span class="badge text-bg-{{
                            $venta->estado === 'completada'
                                ? 'success'
                                : ($venta->estado === 'anulada'
                                    ? 'danger'
                                    : 'secondary')
                        }}">
                            {{ ucfirst($venta->estado) }}
                        </span>
                    </p>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted text-uppercase small mb-3">
                        Pago y boleta
                    </h6>

                    <p class="mb-1">
                        <strong>Método:</strong>
                        {{ $metodosPago[$pago?->metodo_pago] ?? 'No registrado' }}
                    </p>

                    @if ($pago?->referencia_transaccion)
                        <p class="mb-1">
                            <strong>Referencia:</strong>
                            {{ $pago->referencia_transaccion }}
                        </p>
                    @endif

                    @if ($venta->documentoTributario)

                        <p class="mb-1">
                            <strong>Boleta:</strong>
                            Folio {{ $venta->documentoTributario->folio }}
                        </p>

                        <p class="mb-0">
                            <strong>Estado SII:</strong>
                            Simulado
                        </p>

                    @endif

                </div>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white">
            <strong>Totales</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-3">
                    <strong>Subtotal</strong>
                    <div>
                        ${{ number_format($venta->subtotal, 0, ',', '.') }}
                    </div>
                </div>

                <div class="col-md-3">
                    <strong>Descuento</strong>
                    <div>
                        ${{ number_format($venta->descuento, 0, ',', '.') }}
                    </div>
                </div>

                <div class="col-md-3">
                    <strong>IVA</strong>
                    <div>
                        ${{ number_format($venta->impuesto, 0, ',', '.') }}
                    </div>
                </div>

                <div class="col-md-3">
                    <strong>Total</strong>
                    <div class="fs-5">
                        ${{ number_format($venta->total, 0, ',', '.') }}
                    </div>
                </div>

            </div>

        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white">
            <strong>Detalle</strong>
        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Cantidad</th>
                        <th>Precio unit.</th>
                        <th>Desc. %</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($venta->detalles as $detalle)

                        <tr>

                            <td>
                                {{ $detalle->producto?->nombre }}
                            </td>

                            <td>
                                {{ $detalle->lote?->numero_lote }}
                            </td>

                            <td>
                                {{ $detalle->cantidad }}
                            </td>

                            <td>
                                ${{ number_format($detalle->precio_unitario, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ $detalle->descuento_pct }}%
                            </td>

                            <td>
                                ${{ number_format($detalle->total, 0, ',', '.') }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

    @if ($venta->estado === 'completada')

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <h6 class="text-muted text-uppercase small mb-3">
                    Anular venta
                </h6>

                <form
                    action="{{ route('ventas.anular', $venta) }}"
                    method="POST"
                    onsubmit="return confirm('¿Confirmas anular esta venta? El stock será devuelto.')"
                >

                    @csrf

                    <div class="row g-3">

                        <div class="col-md-8">

                            <input
                                type="text"
                                name="motivo"
                                class="form-control"
                                placeholder="Motivo de anulación"
                                required
                            >

                        </div>

                        <div class="col-md-4">

                            <button class="btn btn-danger w-100">
                                Anular venta
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    @endif

</div>

@endsection