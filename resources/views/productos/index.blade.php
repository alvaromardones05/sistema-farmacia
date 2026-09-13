@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-uppercase text-primary small fw-semibold mb-1">Catálogo</p><h1 class="h3 mb-0">Productos</h1></div><a href="{{ route('productos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nuevo producto</a></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Producto</th><th>Código</th><th>Categoría</th><th>Laboratorio</th><th>Stock</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
    @forelse ($productos as $producto)
        <tr><td><div class="fw-semibold">{{ $producto->nombre }}</div><div class="small text-muted">{{ $producto->principio_activo ?: 'Sin principio activo informado' }}</div></td><td>{{ $producto->codigo_interno }}</td><td>{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</td><td>{{ $producto->laboratorio?->nombre ?? 'Sin laboratorio' }}</td><td><span class="{{ $producto->estaEnStockCritico() ? 'text-danger fw-semibold' : '' }}">{{ $producto->getStockTotal() }}</span></td><td><span class="badge text-bg-{{ $producto->activo ? 'success' : 'secondary' }}">{{ $producto->activo ? 'Activo' : 'Inactivo' }}</span></td><td class="text-end"><a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-outline-secondary">Ver</a> <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-outline-primary">Editar</a><form action="{{ route('productos.destroy', $producto) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este producto?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No hay productos activos registrados.</td></tr>
    @endforelse
    </tbody></table></div><div class="card-footer bg-white">{{ $productos->links() }}</div></div>
</div>
@endsection
