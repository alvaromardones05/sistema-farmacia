@extends('layouts.app')

@section('title', 'Historial de movimientos')

@section('content')
@include('inventario._styles')
<div class="container-fluid px-0 inventario-modulo">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario</p>
            <h1 class="h3 mb-0">Historial de movimientos (Kardex)</h1>
        </div>
        <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Producto</label>
                    <select name="producto_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}" @selected(request('producto_id') == $producto->id)>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Ubicación</label>
                    <select name="ubicacion_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(request('ubicacion_id') == $ubicacion->id)>
                                {{ $ubicacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_movimiento" class="form-select">
                        <option value="">Todos</option>
                        @foreach (['entrada', 'salida', 'ajuste', 'transferencia', 'devolucion'] as $tipo)
                            <option value="{{ $tipo }}" @selected(request('tipo_movimiento') == $tipo)>
                                {{ ucfirst($tipo) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control">
                </div>

            </div>
        </div>
        <div class="card-footer bg-white border-0">
            <button class="btn btn-primary btn-sm">Filtrar</button>
            <a href="{{ route('inventario.historial') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Ubicación</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Motivo / Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimientos as $mov)
                        <tr>
                            <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $mov->producto?->nombre }}</td>
                            <td>{{ $mov->lote?->numero_lote }}</td>
                            <td>{{ $mov->ubicacion?->nombre }}</td>
                            <td>
                                <span class="badge text-bg-{{ $mov->tipo_movimiento === 'entrada' ? 'success' : ($mov->tipo_movimiento === 'salida' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($mov->tipo_movimiento) }}
                                </span>
                            </td>
                            <td>{{ $mov->cantidad_movida }}</td>
                            <td>{{ $mov->usuario?->name }}</td>
                            <td>{{ $mov->referencia_documento ?? $mov->motivo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay movimientos con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $movimientos->links() }}</div>
    </div>

</div>
@endsection