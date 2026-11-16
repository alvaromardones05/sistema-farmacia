@extends('layouts.app')

@section('title', 'Canjes')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario</p>
            <h1 class="h3 mb-0">Canjes</h1>
        </div>
        <a href="{{ route('inventario.proximos-vencer') }}" class="btn btn-outline-primary">Próximos a vencer</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-sm-4 col-md-3">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select">
                <option value="">Todos</option>
                @foreach ($estados as $opcion)
                    <option value="{{ $opcion }}" @selected($estado === $opcion)>{{ ucfirst(str_replace('_', ' ', $opcion)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-primary">Filtrar</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Solicitada</th><th>Producto / lote</th><th>Origen</th><th>Cantidad aprobada</th><th>Enviada</th><th>Aceptada</th><th>Repuesta</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse ($canjes as $canje)
                        <tr>
                            <td>{{ $canje->fecha_solicitud->format('d/m/Y H:i') }}</td>
                            <td>{{ $canje->producto->nombre }}<div class="small text-muted">{{ $canje->loteOriginal->numero_lote }} · vence {{ $canje->loteOriginal->fecha_vencimiento->format('d/m/Y') }}</div></td>
                            <td>{{ $canje->ubicacionOrigen->nombre }}</td>
                            <td>{{ $canje->cantidad_solicitada }}</td>
                            <td>{{ (int) $canje->cantidad_enviada }}</td>
                            <td>{{ (int) $canje->cantidad_aceptada }}</td>
                            <td>{{ (int) $canje->cantidad_repuesta }}</td>
                            <td><span class="badge text-bg-{{ $canje->estado === 'rechazado' ? 'danger' : ($canje->estado === 'completado' ? 'success' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $canje->estado)) }}</span></td>
                            <td class="text-end"><a href="{{ route('inventario.canjes.show', $canje) }}" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No hay solicitudes de canje para este filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $canjes->links() }}</div>
    </div>
</div>
@endsection