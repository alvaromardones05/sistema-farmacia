@extends('layouts.app')
@section('title', 'Recetas')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
    <div><h1 class="h3">Recetas</h1><p class="text-muted mb-0">Registro y revisión de recetas médicas.</p></div>
    <a href="{{ route('recetas.create') }}" class="btn btn-primary">Registrar receta</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="GET" action="{{ route('recetas.index') }}" class="row g-2 mb-4">
    <div class="col-md-4"><label for="paciente" class="form-label">Paciente: RUT o nombre</label><input id="paciente" name="paciente" value="{{ request('paciente') }}" class="form-control"></div>
    <div class="col-md-3"><label for="estado" class="form-label">Estado</label><select id="estado" name="estado" class="form-select">
        <option value="">Todos</option>
        @foreach(['pendiente' => 'Pendiente', 'vigente' => 'Aprobada', 'rechazada' => 'Rechazada', 'utilizada' => 'Utilizada', 'vencida' => 'Vencida', 'anulada' => 'Anulada'] as $valor => $texto)
            <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $texto }}</option>
        @endforeach
    </select></div>
    <div class="col-md-3"><label for="fecha" class="form-label">Fecha de emisión</label><input id="fecha" name="fecha" type="date" value="{{ request('fecha') }}" class="form-control"></div>
    <div class="col-md-2 d-flex align-items-end gap-2"><button class="btn btn-outline-primary">Filtrar</button><a href="{{ route('recetas.index') }}" class="btn btn-outline-secondary">Limpiar</a></div>
</form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>Número</th><th>Paciente</th><th>Médico</th><th>Emisión</th><th>Vencimiento</th><th>Tipo</th><th>Estado</th><th></th></tr></thead>
    <tbody>@forelse($recetas as $receta)
        <tr><td>{{ $receta->numero_receta }}</td><td>{{ $receta->paciente?->nombreCompleto() ?? 'Paciente no disponible' }}</td><td>{{ $receta->medico?->nombreCompleto() ?? 'Médico no disponible' }}</td>
            <td>{{ $receta->fecha_emision->format('d/m/Y') }}</td><td>{{ $receta->fecha_vencimiento->format('d/m/Y') }}</td><td>{{ ucfirst($receta->tipo) }}</td>
            <td><span class="badge text-bg-{{ $receta->estaVigente() ? 'success' : ($receta->estado === 'pendiente' && !$receta->estaVencida() ? 'warning' : 'secondary') }}">{{ $receta->estado_visible }}</span></td>
            <td><a href="{{ route('recetas.show', $receta) }}" class="btn btn-sm btn-outline-primary">Ver</a></td></tr>
    @empty<tr><td colspan="8" class="text-center text-muted py-4">No hay recetas para los filtros seleccionados.</td></tr>@endforelse</tbody>
</table></div><div class="card-footer">{{ $recetas->links() }}</div></div>
@endsection
