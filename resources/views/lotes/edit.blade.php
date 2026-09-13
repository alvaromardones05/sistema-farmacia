@extends('layouts.app')

@section('title', 'Editar lote')

@section('content')
<div class="container-fluid px-0"><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Editar lote</h1><a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary">Volver</a></div>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('lotes.update', $lote) }}" method="POST" class="card border-0 shadow-sm"><div class="card-body p-4"><div class="row g-3">
    @csrf @method('PUT')
    <div class="col-md-8"><label for="producto_id" class="form-label">Producto *</label><select id="producto_id" name="producto_id" class="form-select" required>@foreach ($productos as $producto)<option value="{{ $producto->id }}" @selected(old('producto_id', $lote->producto_id) == $producto->id)>{{ $producto->nombre }} ({{ $producto->codigo_interno }})</option>@endforeach</select></div>
    <div class="col-md-4"><label for="numero_lote" class="form-label">Número de lote *</label><input id="numero_lote" name="numero_lote" value="{{ old('numero_lote', $lote->numero_lote) }}" class="form-control" required></div>
    <div class="col-md-4"><label for="fecha_fabricacion" class="form-label">Fecha de fabricación</label><input id="fecha_fabricacion" type="date" name="fecha_fabricacion" value="{{ old('fecha_fabricacion', $lote->fecha_fabricacion?->format('Y-m-d')) }}" class="form-control"></div>
    <div class="col-md-4"><label for="fecha_vencimiento" class="form-label">Fecha de vencimiento *</label><input id="fecha_vencimiento" type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $lote->fecha_vencimiento?->format('Y-m-d')) }}" class="form-control" required></div>
    <div class="col-md-2"><label for="cantidad_inicial" class="form-label">Cantidad inicial *</label><input id="cantidad_inicial" type="number" min="0" name="cantidad_inicial" value="{{ old('cantidad_inicial', $lote->cantidad_inicial) }}" class="form-control" required></div>
    <div class="col-md-2"><label for="estado" class="form-label">Estado *</label><select id="estado" name="estado" class="form-select" required>@foreach (['activo', 'agotado', 'vencido'] as $estado)<option value="{{ $estado }}" @selected(old('estado', $lote->estado) === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
    <div class="col-12"><label for="observaciones" class="form-label">Observaciones</label><textarea id="observaciones" name="observaciones" rows="3" class="form-control">{{ old('observaciones', $lote->observaciones) }}</textarea></div>
</div></div><div class="card-footer bg-white border-0 p-4 pt-0"><button class="btn btn-primary">Actualizar lote</button><a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a></div></form></div>
@endsection