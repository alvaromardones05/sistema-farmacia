@extends('layouts.app')
@section('title', 'Registrar médico')
@section('content')
<div class="d-flex justify-content-between mb-4"><h1 class="h3">Registrar médico</h1><a href="{{ route('recetas.create') }}" class="btn btn-outline-secondary">Volver a receta</a></div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('medicos.store') }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    <div class="card-body"><div class="row g-3">
        <div class="col-md-4"><label for="rut" class="form-label">RUT *</label><input id="rut" name="rut" value="{{ old('rut') }}" maxlength="12" class="form-control" required></div>
        <div class="col-md-4"><label for="nombres" class="form-label">Nombres *</label><input id="nombres" name="nombres" value="{{ old('nombres') }}" maxlength="100" class="form-control" required></div>
        <div class="col-md-4"><label for="apellidos" class="form-label">Apellidos *</label><input id="apellidos" name="apellidos" value="{{ old('apellidos') }}" maxlength="100" class="form-control" required></div>
        <div class="col-md-6"><label for="registro_profesional" class="form-label">Registro profesional (opcional)</label><input id="registro_profesional" name="registro_profesional" value="{{ old('registro_profesional') }}" maxlength="100" class="form-control"></div>
        <div class="col-md-6"><label for="especialidad" class="form-label">Especialidad (opcional)</label><input id="especialidad" name="especialidad" value="{{ old('especialidad') }}" maxlength="100" class="form-control"></div>
    </div></div>
    <div class="card-footer"><button class="btn btn-primary">Registrar y volver a receta</button> <a href="{{ route('recetas.create') }}" class="btn btn-outline-secondary">Cancelar</a></div>
</form>
@endsection
