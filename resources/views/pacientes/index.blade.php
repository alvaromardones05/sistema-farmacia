@extends('layouts.app')

@section('title', 'Pacientes')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Pacientes</h1>
        </div>
        <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nuevo paciente
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        class="form-control" placeholder="RUT, nombre o apellido">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Buscar</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Convenio</th>
                        <th>Teléfono</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pacientes as $paciente)
                        <tr>
                            <td>{{ $paciente->rut }}</td>
                            <td>{{ $paciente->nombreCompleto() }}</td>
                            <td>{{ $paciente->convenio?->nombre ?? '—' }}</td>
                            <td>{{ $paciente->telefono ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('pacientes.show', $paciente) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay pacientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $pacientes->links() }}</div>
    </div>

</div>
@endsection