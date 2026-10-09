@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
            <h1 class="h3 mb-1">Proveedores</h1>
            <p class="text-muted mb-0">Administración de proveedores de la farmacia.</p>
        </div>

        <a href="{{ route('compras.proveedores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo proveedor
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver a Compras
        </a>
        
        <a href="{{ route('compras.proveedores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo proveedor
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('compras.proveedores.index') }}">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input
                            type="text"
                            name="buscar"
                            class="form-control"
                            placeholder="Buscar por nombre, RUT o contacto..."
                            value="{{ request('buscar') }}"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-search me-1"></i>
                            Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Proveedor</th>
                        <th>RUT</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($proveedores as $proveedor)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $proveedor->nombre_razon_social }}
                                </div>

                                @if($proveedor->email)
                                    <div class="small text-muted">
                                        {{ $proveedor->email }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $proveedor->rut }}</td>

                            <td>
                                {{ $proveedor->contacto_nombre ?? 'Sin contacto' }}
                            </td>

                            <td>
                                {{ $proveedor->telefono ?? '—' }}
                            </td>

                            <td>
                                {{ $proveedor->ciudad ?? '—' }}
                            </td>

                            <td>
                                @if($proveedor->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">

                                    <a
                                        href="{{ route('compras.proveedores.edit', $proveedor) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('compras.proveedores.estado', $proveedor) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            class="btn btn-sm {{ $proveedor->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        >
                                            <i class="bi {{ $proveedor->activo ? 'bi-pause-circle' : 'bi-check-circle' }}"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-building fs-2 d-block mb-2"></i>
                                No existen proveedores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if($proveedores->hasPages())
            <div class="card-footer bg-white">
                {{ $proveedores->links() }}
            </div>
        @endif
    </div>

</div>
@endsection