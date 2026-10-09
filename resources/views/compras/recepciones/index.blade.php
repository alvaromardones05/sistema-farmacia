@extends('layouts.app')

@section('title', 'Recepciones')

@section('content')
<div class="container-fluid px-0">

    <div class="mb-4">
        <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
        <h1 class="h3 mb-1">Recepciones de mercadería</h1>
        <p class="text-muted mb-3">Historial de ingresos de productos provenientes de compras.</p>

        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    <div class="card border-0 shadow-sm">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Recepción</th>
                        <th>Orden</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($recepciones as $recepcion)

                        <tr>

                            <td class="fw-semibold">
                                REC-{{ str_pad($recepcion->id, 6, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                {{ $recepcion->ordenCompra?->numero_orden ?? 'Sin orden' }}
                            </td>

                            <td>
                                {{ $recepcion->ordenCompra?->proveedor?->nombre_razon_social ?? '—' }}
                            </td>

                            <td>
                                {{ $recepcion->fecha_recepcion->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                {{ $recepcion->detalles_count }}
                            </td>

                            <td>
                                @if($recepcion->estado === 'completa')
                                    <span class="badge bg-success">Completa</span>
                                @elseif($recepcion->estado === 'parcial')
                                    <span class="badge bg-primary">Parcial</span>
                                @else
                                    <span class="badge bg-danger">Devuelta</span>
                                @endif
                            </td>

                            <td>
                                {{ $recepcion->usuario->name }}
                            </td>

                            <td class="text-end">
                                <a
                                    href="{{ route('compras.recepciones.show', $recepcion) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    <i class="bi bi-eye me-1"></i>
                                    Ver
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No existen recepciones registradas.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($recepciones->hasPages())
            <div class="card-footer bg-white">
                {{ $recepciones->links() }}
            </div>
        @endif

    </div>

</div>
@endsection