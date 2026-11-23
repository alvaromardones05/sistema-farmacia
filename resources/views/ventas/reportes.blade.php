@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Reporte de Ventas</h1>
            <p class="text-muted mb-0">Resumen de transacciones completadas</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ventas.reportes.exportar', ['periodo' => $periodo]) }}" class="btn btn-primary">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
            </a>
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <!-- Filtros de Periodo -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2">
            <div class="nav nav-pills gap-2">
                <a href="{{ route('ventas.reportes', ['periodo' => 'dia']) }}"
                   class="nav-link {{ $periodo === 'dia' ? 'active' : '' }}">Hoy (Día)</a>
                <a href="{{ route('ventas.reportes', ['periodo' => 'semana']) }}"
                   class="nav-link {{ $periodo === 'semana' ? 'active' : '' }}">Esta Semana</a>
                <a href="{{ route('ventas.reportes', ['periodo' => 'mes']) }}"
                   class="nav-link {{ $periodo === 'mes' ? 'active' : '' }}">Este Mes</a>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold mb-2">Total Vendido</h6>
                    <h2 class="mb-0 text-primary">${{ number_format($totalVendido, 0, ',', '.') }}</h2>
                    <small class="text-muted">{{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold mb-2">Cantidad de Ventas</h6>
                    <h2 class="mb-0">{{ $cantidadVentas }}</h2>
                    <small class="text-muted">Transacciones completadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold mb-2">Ticket Promedio</h6>
                    <h2 class="mb-0 text-success">${{ number_format($ticketPromedio, 0, ',', '.') }}</h2>
                    <small class="text-muted">Monto promedio por venta</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Venta</th>
                        <th>Fecha y Hora</th>
                        <th>Cliente</th>
                        <th>Doc.</th>
                        <th>Método Pago</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                        <tr>
                            <td class="fw-bold">{{ $venta->numero_venta }}</td>
                            <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($venta->tipo_documento === 'factura')
                                    <span class="badge bg-info text-dark">Factura</span> {{ $venta->razon_social }}
                                @else
                                    {{ $venta->paciente ? $venta->paciente->nombreCompleto() : 'Consumidor Final' }}
                                @endif
                            </td>
                            <td>{{ ucfirst($venta->tipo_documento) }}</td>
                            <td>{{ ucfirst($venta->pagos->first()->metodo_pago ?? 'N/A') }}</td>
                            <td class="text-end fw-bold">${{ number_format($venta->total, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No hay ventas registradas en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
