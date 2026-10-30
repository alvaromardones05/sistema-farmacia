@extends('layouts.app')
@section('title', 'Detalle de receta')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4"><div><h1 class="h3">Receta {{ $receta->numero_receta }}</h1><span class="badge text-bg-{{ $receta->estaVigente() ? 'success' : 'secondary' }}">{{ $receta->estado_visible }}</span></div><a href="{{ route('recetas.index') }}" class="btn btn-outline-secondary">Volver</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@hasanyrole('Químico Farmacéutico|Técnico Farmacéutico')
    <div class="mb-3">
        <a href="{{ route('ventas.create', ['paciente_id' => $receta->paciente_id]) }}" class="btn btn-outline-primary">Ir a Nueva Venta</a>
        @if($receta->estado === 'pendiente')
            <p class="form-text mb-0">La receta queda pendiente de validación por el Químico Farmacéutico y todavía no autoriza una venta.</p>
        @endif
    </div>
@endhasanyrole
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><dl class="row mb-0">
    <dt class="col-md-3">Paciente</dt><dd class="col-md-9">{{ $receta->paciente?->nombreCompleto() ?? 'No disponible' }} · {{ $receta->paciente?->rut }}</dd>
    <dt class="col-md-3">Médico</dt><dd class="col-md-9">{{ $receta->medico?->nombreCompleto() ?? 'No disponible' }} · {{ $receta->medico?->registro_profesional }}</dd>
    <dt class="col-md-3">Emisión / vencimiento</dt><dd class="col-md-9">{{ $receta->fecha_emision->format('d/m/Y') }} / {{ $receta->fecha_vencimiento->format('d/m/Y') }}</dd>
    <dt class="col-md-3">Tipo</dt><dd class="col-md-9">{{ ucfirst($receta->tipo) }}</dd>
    <dt class="col-md-3">Archivo</dt><dd class="col-md-9">@if($receta->archivo_path)<a href="{{ route('recetas.archivo', $receta) }}" target="_blank" rel="noopener">Abrir imagen o PDF</a>@else Sin respaldo adjunto (registro histórico). @endif</dd>
    <dt class="col-md-3">Observaciones y revisión</dt><dd class="col-md-9 mb-0" style="white-space: pre-wrap">{{ $receta->observaciones ?: 'Sin observaciones.' }}</dd>
</dl></div></div>
<div class="card border-0 shadow-sm mb-4"><div class="card-header"><h2 class="h5 mb-0">Medicamentos</h2></div><div class="table-responsive"><table class="table align-middle">
    <thead><tr><th>Producto</th><th>Prescrito</th><th>Dispensado</th><th>Disponible</th><th>Indicaciones</th></tr></thead>
    <tbody>@foreach($receta->detalles as $detalle)<tr><td>{{ $detalle->producto?->nombre ?? 'No disponible' }}</td><td>{{ $detalle->cantidad_prescrita }} {{ $detalle->unidad_medida }}</td><td>{{ $detalle->cantidad_dispensada }}</td><td>{{ $detalle->estado === 'cancelado' ? 0 : max(0, $detalle->cantidad_prescrita - $detalle->cantidad_dispensada) }}</td><td>{{ $detalle->instrucciones ?: '—' }}</td></tr>@endforeach</tbody>
</table></div></div>
@if($receta->estado === 'pendiente')
    @role('Químico Farmacéutico')
        @can('validar_recetas')
            <div class="card border-0 shadow-sm"><div class="card-body">
                <h2 class="h5">Revisión farmacéutica</h2>
                <p class="text-muted">Aprobar valida la receta; el stock se descontará únicamente al confirmar una venta.</p>
                <form action="{{ route('recetas.aprobar', $receta) }}" method="POST" class="mb-3">@csrf<button class="btn btn-success" @disabled($receta->estaVencida())>Aprobar receta</button></form>
                <form action="{{ route('recetas.rechazar', $receta) }}" method="POST">@csrf<label for="motivo" class="form-label">Motivo de rechazo *</label><textarea id="motivo" name="motivo" class="form-control mb-2" maxlength="1000" required>{{ old('motivo') }}</textarea><button class="btn btn-outline-danger">Rechazar receta</button></form>
            </div></div>
        @endcan
    @endrole
@else
    <p class="text-muted">Esta receta conserva su historial y no admite modificaciones.</p>
@endif
@endsection
