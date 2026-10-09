@extends('layouts.app')

@section('title', 'Caja')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Caja</h1>
            <p class="text-muted mb-0">
                Control de apertura, ventas y arqueo diario
            </p>
        </div>

        <a href="{{ route('caja.historial') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>
            Historial
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
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

    @if($apertura)

        {{-- ================= ESTADO ================= --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge text-bg-success">
                            Caja abierta
                        </span>

                        <strong>
                            {{ $apertura->caja?->descripcion }}
                        </strong>
                    </div>

                    <div class="text-muted small">
                        Caja {{ $apertura->caja?->numero_caja }}
                        · Abierta por {{ $apertura->usuario?->name }}
                        · {{ $apertura->fecha_apertura->format('d/m/Y H:i') }}
                    </div>
                </div>

                <div class="text-end">
                    <small class="text-muted d-block">
                        Tiempo abierta
                    </small>

                    <strong>
                        {{ $apertura->fecha_apertura->diffForHumans(null, true) }}
                    </strong>
                </div>

            </div>
        </div>

        {{-- ================= RESUMEN ================= --}}
        <div class="row g-3 mb-4">

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Saldo inicial
                        </div>

                        <div class="h4 mb-0">
                            ${{ number_format($apertura->saldo_inicial, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Ventas del turno
                        </div>

                        <div class="h4 mb-0">
                            ${{ number_format($resumen['total_vendido'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Ventas realizadas
                        </div>

                        <div class="h4 mb-0">
                            {{ $resumen['cantidad_ventas'] }}
                        </div>

                        @if($resumen['cantidad_anuladas'] > 0)
                            <small class="text-muted">
                                {{ $resumen['cantidad_anuladas'] }} anulada(s)
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Efectivo esperado
                        </div>

                        <div class="h4 text-success mb-0">
                            ${{ number_format($resumen['saldo_esperado'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4">

            {{-- ================= ACTIVIDAD ================= --}}
            <div class="col-xl-7">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-0">Medios de pago</h5>
                    </div>

                    <div class="card-body pt-2">

                        <div class="row g-3">

                            @php
                                $metodos = [
                                    'efectivo' => 'Efectivo',
                                    'debito' => 'Débito',
                                    'credito' => 'Crédito',
                                    'transferencia' => 'Transferencia',
                                    'cheque' => 'Cheque',
                                ];
                            @endphp

                            @foreach($metodos as $clave => $nombre)
                                <div class="col-6 col-md-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted">
                                            {{ $nombre }}
                                        </small>

                                        <div class="fw-semibold mt-1">
                                            ${{ number_format($resumen['pagos'][$clave], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        @if($resumen['sin_medio_registrado'] > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>

                                Hay
                                <strong>
                                    ${{ number_format($resumen['sin_medio_registrado'], 0, ',', '.') }}
                                </strong>
                                en ventas cuyo medio de pago todavía no está registrado.

                                Para este arqueo se consideran provisionalmente como efectivo.
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ÚLTIMAS VENTAS --}}
                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Últimas ventas</h5>

                        <a href="{{ route('ventas.index') }}"
                           class="btn btn-sm btn-outline-secondary">
                            Ver ventas
                        </a>
                    </div>

                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead>
                                <tr>
                                    <th>Venta</th>
                                    <th>Hora</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($resumen['ventas_recientes'] as $venta)
                                    <tr>

                                        <td>
                                            <a href="{{ route('ventas.show', $venta) }}"
                                               class="text-decoration-none fw-semibold">
                                                #{{ $venta->numero_venta }}
                                            </a>
                                        </td>

                                        <td class="text-muted">
                                            {{ $venta->fecha_venta?->format('H:i') }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            ${{ number_format($venta->total, 0, ',', '.') }}
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="text-center text-muted py-4">
                                            Aún no hay ventas en esta apertura.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            {{-- ================= ARQUEO ================= --}}
            <div class="col-xl-5">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-1">Cerrar caja</h5>
                        <small class="text-muted">
                            Realiza el arqueo antes de finalizar el turno
                        </small>
                    </div>

                    <div class="card-body">

                        <div class="bg-light rounded-3 p-3 mb-4">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    Saldo inicial
                                </span>

                                <strong>
                                    ${{ number_format($apertura->saldo_inicial, 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    Ventas en efectivo
                                </span>

                                <strong>
                                    ${{ number_format($resumen['efectivo_ventas'], 0, ',', '.') }}
                                </strong>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">
                                    Efectivo esperado
                                </span>

                                <strong class="text-success">
                                    ${{ number_format($resumen['saldo_esperado'], 0, ',', '.') }}
                                </strong>
                            </div>

                        </div>

                        <form action="{{ route('caja.cerrar', $apertura) }}"
                              method="POST"
                              onsubmit="return confirm('¿Confirmas el cierre y arqueo de esta caja?')">

                            @csrf

                            <div class="mb-3">
                                <label class="form-label">
                                    Efectivo contado *
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text">$</span>

                                    <input type="number"
                                           name="saldo_contado"
                                           class="form-control"
                                           min="0"
                                           step="1"
                                           value="{{ old('saldo_contado') }}"
                                           required>
                                </div>

                                <small class="text-muted">
                                    Ingresa el efectivo real contado físicamente.
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Observaciones
                                </label>

                                <textarea name="observaciones"
                                          class="form-control"
                                          rows="3"
                                          maxlength="1000"
                                          placeholder="Opcional. Ej: diferencia justificada, billetes faltantes...">{{ old('observaciones') }}</textarea>
                            </div>

                            <button type="submit"
                                    class="btn btn-danger w-100">
                                <i class="bi bi-lock me-1"></i>
                                Cerrar caja
                            </button>

                        </form>

                    </div>
                </div>

            </div>

        </div>

    @else

        {{-- ================= ABRIR CAJA ================= --}}
        <div class="row justify-content-center">

            <div class="col-xl-7">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <div class="d-flex align-items-center gap-3 mb-4">

                            <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px">
                                <i class="bi bi-lock fs-5 text-secondary"></i>
                            </div>

                            <div>
                                <h5 class="mb-1">No hay caja abierta</h5>

                                <p class="text-muted mb-0">
                                    Abre una caja para comenzar a registrar ventas.
                                </p>
                            </div>

                        </div>

                        <form action="{{ route('caja.abrir') }}"
                              method="POST">

                            @csrf

                            <div class="row g-3">

                                <div class="col-md-7">

                                    <label class="form-label">
                                        Caja *
                                    </label>

                                    <select name="caja_id"
                                            class="form-select"
                                            required>

                                        <option value="">
                                            Selecciona una caja
                                        </option>

                                        @foreach($cajas as $caja)
                                            <option value="{{ $caja->id }}"
                                                @selected(old('caja_id') == $caja->id)>

                                                {{ $caja->descripcion }}
                                                ({{ $caja->numero_caja }})

                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <div class="col-md-5">

                                    <label class="form-label">
                                        Saldo inicial *
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text">$</span>

                                        <input type="number"
                                               name="saldo_inicial"
                                               class="form-control"
                                               min="0"
                                               step="1"
                                               value="{{ old('saldo_inicial', 0) }}"
                                               required>
                                    </div>

                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">

                                <button type="submit"
                                        class="btn btn-primary">
                                    <i class="bi bi-unlock me-1"></i>
                                    Abrir caja
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>
@endsection