@extends('layouts.app')

@section('title', 'Compras / Recepciones')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Abastecimiento</p>
            <h1 class="h3 mb-1">Compras / Recepciones</h1>
            <p class="text-muted mb-0">Gestiona órdenes de compra, recepción de mercadería y proveedores.</p>
        </div>

        <a href="{{ route('compras.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nueva compra
        </a>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Pendientes</div>
                    <div class="fs-3 fw-semibold">{{ $pendientes }}</div>
                    <div class="small text-muted">Órdenes por recibir</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Recepción parcial</div>
                    <div class="fs-3 fw-semibold">{{ $parciales }}</div>
                    <div class="small text-muted">Órdenes incompletas</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Recibidas</div>
                    <div class="fs-3 fw-semibold">{{ $recibidas }}</div>
                    <div class="small text-muted">Órdenes completadas</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Proveedores</div>
                    <div class="fs-3 fw-semibold">{{ $proveedoresActivos }}</div>
                    <div class="small text-muted">Proveedores activos</div>
                </div>
            </div>
        </div>

    </div>

    <div class="d-flex gap-2 mb-3">
        <button class="btn btn-primary">
            <i class="bi bi-file-earmark-text me-1"></i>
            Órdenes de compra
        </button>

        <a
            href="{{ route('compras.recepciones.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-box-arrow-in-down me-1"></i>
            Recepciones
        </a>

        <a
            href="{{ route('compras.proveedores.index') }}"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-building me-1"></i>
            Proveedores
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Órdenes de compra</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>N° orden</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Entrega esperada</th>
                    <th>Estado</th>
                    <th>Usuario</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>

                <tbody>
                    @forelse($ordenes as $orden)
                        <tr>
                            <td class="fw-semibold">{{ $orden->numero_orden }}</td>

                            <td>
                                {{ $orden->proveedor->nombre_razon_social }}
                            </td>

                            <td>
                                {{ $orden->fecha_orden->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $orden->fecha_entrega_esperada?->format('d/m/Y') ?? 'Sin definir' }}
                            </td>

                            <td>
                                @switch($orden->estado)
                                    @case('pendiente')
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                        @break

                                    @case('confirmada')
                                        <span class="badge bg-info text-dark">Confirmada</span>
                                        @break

                                    @case('parcial')
                                        <span class="badge bg-primary">Parcial</span>
                                        @break

                                    @case('recibida')
                                        <span class="badge bg-success">Recibida</span>
                                        @break

                                    @case('anulada')
                                        <span class="badge bg-danger">Anulada</span>
                                        @break
                                @endswitch
                            </td>

                            <td>
                                {{ $orden->usuario->name }}
                            </td>

                            <td class="text-end">
                                <a
                                    href="{{ route('compras.show', $orden) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                                Todavía no existen órdenes de compra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ordenes->hasPages())
            <div class="card-footer bg-white">
                {{ $ordenes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection