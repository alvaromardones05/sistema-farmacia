@extends('layouts.app')

@section('title', 'Lotes')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario</p>
            <h1 class="h3 mb-0">Lotes</h1>
        </div>
        <a href="{{ route('lotes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nuevo lote
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Número Lote</th>
                        <th>Fecha Vencimiento</th>
                        <th>Cantidad</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lotes as $lote)
                        <tr>
                            <td>{{ $lote->id }}</td>
                            <td>{{ $lote->producto->nombre ?? 'N/A' }}</td>
                            <td>{{ $lote->numero_lote }}</td>
                            <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>{{ $lote->cantidad }}</td>
                            <td>{{ $lote->ubicacion_fisica ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $lote->disponible ? 'badge-success' : 'badge-danger' }}">
                                    {{ $lote->disponible ? 'Disponible' : 'Agotado' }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('lotes.edit', $lote->id) }}" class="btn-sm btn-warning">Editar</a>
                                <form action="{{ route('lotes.destroy', $lote->id) }}" method="POST" class="form-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger" onclick="return confirm('¿Está seguro?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $lotes->links() }}</div>
    </div>
</div>
@endsection