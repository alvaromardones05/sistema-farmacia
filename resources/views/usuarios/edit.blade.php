@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Editar usuario</h1>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('usuarios.update', $usuario) }}" method="POST" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="name">Nombre *</label><input id="name" name="name" value="{{ old('name', $usuario->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label" for="apellidos">Apellidos *</label><input id="apellidos" name="apellidos" value="{{ old('apellidos', $usuario->apellidos) }}" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label" for="rut">RUT *</label><input id="rut" name="rut" value="{{ old('rut', $usuario->rut) }}" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label" for="telefono">Teléfono</label><input id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="form-control"></div>
                <div class="col-md-4"><label class="form-label" for="role">Rol *</label><select id="role" name="role" class="form-select" required><option value="">Selecciona un rol</option>@foreach ($roles as $role)<option value="{{ $role->name }}" @selected(old('role', $usuario->roles->first()?->name) === $role->name)>{{ $role->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label" for="email">Email *</label><input id="email" type="email" name="email" value="{{ old('email', $usuario->email) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label" for="numero_registro_tecnico">Registro técnico</label><input id="numero_registro_tecnico" name="numero_registro_tecnico" value="{{ old('numero_registro_tecnico', $usuario->numero_registro_tecnico) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label" for="password">Nueva contraseña</label><input id="password" type="password" name="password" class="form-control"></div>
                <div class="col-md-6"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control"></div>
                <div class="col-12"><div class="form-check form-switch"><input id="activo" type="checkbox" name="activo" value="1" class="form-check-input" @checked(old('activo', $usuario->activo))><label for="activo" class="form-check-label">Usuario activo</label></div></div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 p-4 pt-0"><button class="btn btn-primary">Actualizar usuario</button><a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a></div>
    </form>
</div>
@endsection