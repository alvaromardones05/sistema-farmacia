@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Panel de control</h1>
            <p class="text-muted mb-0">Resumen de la operación · {{ today()->format('d/m/Y') }}</p>
        </div>
        @role('Administrador')
            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm">Gestionar usuarios</a>
        @endrole
    </div>

    <div class="row g-3 mb-4">
        @if ($puedeVerVentas)
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted"><i class="bi bi-receipt me-2" aria-hidden="true"></i>Ventas de hoy</h2>
                        <div class="fs-3 fw-semibold">${{ number_format($metricas['total_ventas'], 0, ',', '.') }}</div>
                        <p class="text-muted small mb-3">{{ $metricas['cantidad_ventas'] }} ventas completadas</p>
                        @if ($puedeAccederVentas)
                            <a href="{{ route('ventas.index') }}" class="small">Ver ventas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($puedeVerInventario)
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted"><i class="bi bi-box-seam me-2" aria-hidden="true"></i>Stock crítico</h2>
                        <div class="fs-3 fw-semibold {{ $metricas['stock_critico'] > 0 ? 'text-danger' : '' }}">{{ $metricas['stock_critico'] }}</div>
                        <p class="text-muted small mb-3">Productos activos · stock total</p>
                        <a href="{{ route('inventario.index') }}" class="small">Ver inventario <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted"><i class="bi bi-calendar-event me-2" aria-hidden="true"></i>Próximos a vencer</h2>
                        <div class="fs-3 fw-semibold">{{ $metricas['lotes_por_vencer'] }}</div>
                        <p class="text-muted small mb-3">Lotes activos · hoy a 30 días</p>
                        <a href="{{ route('inventario.proximos-vencer', ['dias' => 30]) }}" class="small">Ver vencimientos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        @endif

        @if ($puedeRevisarDescuadres)
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted"><i class="bi bi-clipboard-check me-2" aria-hidden="true"></i>Descuadres controlados</h2>
                        <div class="fs-3 fw-semibold">{{ $metricas['descuadres_pendientes'] }}</div>
                        <p class="text-muted small mb-3">Pendientes de revisión</p>
                        <a href="{{ route('descuadres-controlados.index') }}" class="small">Revisar <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($puedeVerInventario)
        <div class="row g-4">
            <div class="col-lg-6">
                <section class="card border-0 shadow-sm h-100" aria-labelledby="stock-atencion">
                    <div class="card-header bg-white border-0 p-4 pb-2">
                        <h2 id="stock-atencion" class="h5 mb-1">Stock que requiere atención</h2>
                        <p class="text-muted small mb-0">Hasta 5 productos críticos, comenzando por el menor stock total.</p>
                    </div>
                    <ul class="list-group list-group-flush flex-grow-1">
                        @forelse ($productosCriticos as $producto)
                            <li class="list-group-item px-4 py-3">
                                <div class="fw-semibold">{{ $producto->nombre }}</div>
                                <div class="small text-muted">Stock actual: <strong class="text-danger">{{ (int) $producto->stock_total }}</strong> · Crítico: {{ $producto->stock_critico }}</div>
                            </li>
                        @empty
                            <li class="list-group-item px-4 py-3 text-muted">No hay productos activos en stock crítico.</li>
                        @endforelse
                    </ul>
                    <div class="card-footer bg-white border-0 p-4">
                        <a href="{{ route('inventario.index') }}" class="small">Ver inventario <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="card border-0 shadow-sm h-100" aria-labelledby="vencimientos-atencion">
                    <div class="card-header bg-white border-0 p-4 pb-2">
                        <h2 id="vencimientos-atencion" class="h5 mb-1">Próximos vencimientos</h2>
                        <p class="text-muted small mb-0">Hasta 5 lotes activos que vencen entre hoy y los próximos 30 días.</p>
                    </div>
                    <ul class="list-group list-group-flush flex-grow-1">
                        @forelse ($lotesPorVencer as $lote)
                            <li class="list-group-item px-4 py-3">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <span class="fw-semibold">{{ $lote->producto?->nombre ?? 'Producto no disponible' }}</span>
                                    <time datetime="{{ $lote->fecha_vencimiento->format('Y-m-d') }}" class="small">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</time>
                                </div>
                                <div class="small text-muted">Lote {{ $lote->numero_lote }} · Stock total: {{ (int) $lote->stock_total }} un.</div>
                            </li>
                        @empty
                            <li class="list-group-item px-4 py-3 text-muted">No hay lotes activos por vencer en los próximos 30 días.</li>
                        @endforelse
                    </ul>
                    <div class="card-footer bg-white border-0 p-4">
                        <a href="{{ route('inventario.index') }}" class="small">Ver inventario <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </section>
            </div>
        </div>
    @elseif (! $puedeVerVentas && ! $puedeRevisarDescuadres)
        <div class="alert alert-light border">Tu usuario no tiene acceso a los indicadores de ventas, inventario o revisión de descuadres.</div>
    @endif
</div>
@endsection
