@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="container-fluid px-0"><div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-uppercase text-primary small fw-semibold mb-1">Caja</p><h1 class="h3 mb-0">Ventas</h1></div><a href="{{ route('ventas.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nueva venta</a></div>
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Número</th><th>Fecha</th><th>Paciente</th><th>Usuario</th><th>Total</th><th>Estado</th><th class="text-end">Acción</th></tr></thead><tbody>
@forelse ($ventas as $venta)<tr><td class="fw-semibold">{{ $venta->numero_venta }}</td><td>{{ $venta->fecha_venta?->format('d/m/Y H:i') }}</td><td>{{ $venta->paciente?->nombres }} {{ $venta->paciente?->apellidos }}</td><td>{{ $venta->usuario?->name ?? 'No disponible' }}</td><td>$ {{ number_format((float) $venta->total, 0, ',', '.') }}</td><td><span class="badge text-bg-{{ $venta->estado === 'completada' ? 'success' : 'secondary' }}">{{ ucfirst($venta->estado) }}</span></td><td class="text-end"><a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-primary">Ver detalle</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">No hay ventas registradas.</td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $ventas->links() }}</div></div></div>
@endsection
