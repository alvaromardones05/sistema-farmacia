@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Ventas</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('ventas.reporte-diario') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i>Reporte del día
            </a>
            <a href="{{ route('ventas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nueva venta
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° venta</th>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ventas as $venta)
                        <tr>
                            <td>{{ $venta->numero_venta }}</td>
                            <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                            <td>{{ $venta->paciente?->nombreCompleto() }}</td>
                            <td>{{ $venta->usuario?->name }}</td>
                            <td>${{ number_format($venta->total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge text-bg-{{ $venta->estado === 'completada' ? 'success' : ($venta->estado === 'anulada' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($venta->estado) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay ventas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $ventas->links() }}</div>
    </div>

</div>
@endsection