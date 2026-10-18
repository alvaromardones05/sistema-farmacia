@extends('layouts.app')

@section('title', 'Nuevo paciente')

@section('content')
<div class="container-fluid px-0">
    @php
        $desdeVenta = old('origen', request('origen')) === 'venta';
        $volver = $desdeVenta ? route('ventas.create') : route('pacientes.index');
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Nuevo paciente</h1>
        <a href="{{ $volver }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pacientes.store') }}" method="POST" class="card border-0 shadow-sm">
        @csrf
        @if ($desdeVenta)
            <input type="hidden" name="origen" value="venta">
        @endif
        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">RUT *</label>
                    <input name="rut" value="{{ old('rut') }}" class="form-control" required placeholder="12345678-9">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nombres *</label>
                    <input name="nombres" value="{{ old('nombres') }}" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Apellidos *</label>
                    <input name="apellidos" value="{{ old('apellidos') }}" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha de nacimiento <span class="text-muted small">(Opcional)</span></label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Teléfono <span class="text-muted small">(Opcional)</span></label>
                    <input name="telefono" value="{{ old('telefono') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Email <span class="text-muted small">(Opcional)</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Convenio <span class="text-muted small">(Opcional)</span></label>
                    <select name="convenio_id" class="form-select">
                        <option value="">Particular / sin convenio</option>
                        @foreach ($convenios as $convenio)
                            <option value="{{ $convenio->id }}" @selected(old('convenio_id') == $convenio->id)>
                                {{ $convenio->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Dirección <span class="text-muted small">(Opcional)</span></label>
                    <input name="direccion" value="{{ old('direccion') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Ciudad <span class="text-muted small">(Opcional)</span></label>
                    <input name="ciudad" value="{{ old('ciudad') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">N° póliza <span class="text-muted small">(Opcional)</span></label>
                    <input name="numero_poliza" value="{{ old('numero_poliza') }}" class="form-control">
                </div>

            </div>
        </div>
        <div class="card-footer bg-white border-0 p-4 pt-0">
            <button class="btn btn-primary">{{ $desdeVenta ? 'Registrar y volver a venta' : 'Guardar paciente' }}</button>
            <a href="{{ $volver }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
    </form>

</div>
@endsection
