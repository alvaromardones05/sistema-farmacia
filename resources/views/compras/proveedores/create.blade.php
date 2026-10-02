@extends('layouts.app')

@section('title', 'Nuevo proveedor')

@section('content')
<div class="container-fluid px-0">

    <div class="mb-4">
        <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
        <h1 class="h3 mb-1">Nuevo proveedor</h1>
        <p class="text-muted mb-0">Registra un proveedor para utilizarlo en futuras órdenes de compra.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form method="POST" action="{{ route('compras.proveedores.store') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">RUT *</label>
                        <input
                            type="text"
                            name="rut"
                            class="form-control @error('rut') is-invalid @enderror"
                            value="{{ old('rut') }}"
                            required
                        >
                        @error('rut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nombre / Razón social *</label>
                        <input
                            type="text"
                            name="nombre_razon_social"
                            class="form-control @error('nombre_razon_social') is-invalid @enderror"
                            value="{{ old('nombre_razon_social') }}"
                            required
                        >
                        @error('nombre_razon_social')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Persona de contacto</label>
                        <input
                            type="text"
                            name="contacto_nombre"
                            class="form-control"
                            value="{{ old('contacto_nombre') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            class="form-control"
                            value="{{ old('telefono') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input
                            type="text"
                            name="direccion"
                            class="form-control"
                            value="{{ old('direccion') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Ciudad</label>
                        <input
                            type="text"
                            name="ciudad"
                            class="form-control"
                            value="{{ old('ciudad') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Región</label>
                        <input
                            type="text"
                            name="region"
                            class="form-control"
                            value="{{ old('region') }}"
                        >
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a
                        href="{{ route('compras.proveedores.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancelar
                    </a>

                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Guardar proveedor
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
@endsection