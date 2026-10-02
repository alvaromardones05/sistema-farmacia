@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="container-fluid px-0">

    {{-- Encabezado --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Catálogo</p>
            <h1 class="h3 mb-0">Productos</h1>
        </div>
        <div class="d-flex gap-2">
            
            <a href="{{ route('productos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nuevo producto
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('productos.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           class="form-control" placeholder="Nombre, código o principio activo...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Categoría</label>
                    <select name="categoria_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                        <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                        <option value="stock_critico" @selected(request('estado') === 'stock_critico')>Stock crítico</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Categoría</th>
                        <th>Laboratorio</th>
                        <th>Precio</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $producto->nombre }}</div>
                            <div class="small text-muted">
                                {{ $producto->principio_activo ?: 'Sin principio activo' }}
                            </div>
                        </td>
                        <td><code class="text-body">{{ $producto->codigo_interno }}</code></td>
                        <td>
                            @if ($producto->categoria)
                                <span class="badge text-bg-light border">{{ $producto->categoria->nombre }}</span>
                            @else
                                <span class="text-muted small">Sin categoría</span>
                            @endif
                        </td>
                        <td>{{ $producto->laboratorio?->nombre ?? '—' }}</td>

                        <td>
                            ${{ number_format($producto->precio_base, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @php $stock = $producto->getStockTotal(); @endphp
                            <span class="badge text-bg-{{ $producto->estaEnStockCritico() ? 'danger' : 'success' }}">
                                {{ $stock }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-{{ $producto->activo ? 'success' : 'secondary' }}">
                                {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('productos.show', $producto) }}"
                               class="btn btn-sm btn-outline-secondary" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('productos.edit', $producto) }}"
                               class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('productos.destroy', $producto) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('¿Eliminar {{ $producto->nombre }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No se encontraron productos.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($productos->hasPages())
            <div class="card-footer bg-white">
                {{ $productos->links() }}
            </div>
        @endif
    </div>

    {{-- Resumen --}}
    <div class="text-muted small mt-2">
        Mostrando {{ $productos->firstItem() ?? 0 }}–{{ $productos->lastItem() ?? 0 }}
        de {{ $productos->total() }} productos
    </div>
</div>
@endsection