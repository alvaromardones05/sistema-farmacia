@extends('layouts.app')

@section('title', 'Lotes')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-uppercase text-primary small fw-semibold mb-1">Inventario</p><h1 class="h3 mb-0">Lotes</h1></div><a href="{{ route('lotes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo lote</a></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Producto</th><th>Lote</th><th>Vencimiento</th><th>Cantidad inicial</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
    @forelse ($lotes as $lote)
        <tr><td>{{ $lote->producto?->nombre ?? 'Producto no disponible' }}</td><td>{{ $lote->numero_lote }}</td><td>{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td><td>{{ $lote->cantidad_inicial }}</td><td><span class="badge text-bg-{{ $lote->estado === 'activo' ? 'success' : ($lote->estado === 'vencido' ? 'danger' : 'secondary') }}">{{ ucfirst($lote->estado) }}</span></td><td class="text-end"><a href="{{ route('lotes.edit', $lote) }}" class="btn btn-sm btn-outline-primary">Editar</a><form action="{{ route('lotes.destroy', $lote) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este lote?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td></tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No hay lotes registrados.</td></tr>
    @endforelse
    </tbody></table></div><div class="card-footer bg-white">{{ $lotes->links() }}</div></div>
</div>
@endsection
