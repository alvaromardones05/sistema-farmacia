@extends('layouts.app')

@section('title', 'Detalle del paciente')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $paciente->nombreCompleto() }}</h1>
        <a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">Datos personales</h6>
                    <p class="mb-1"><strong>RUT:</strong> {{ $paciente->rut }}</p>
                    <p class="mb-1"><strong>Fecha de nacimiento:</strong> {{ $paciente->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</p>
                    <p class="mb-1"><strong>Teléfono:</strong> {{ $paciente->telefono ?? '—' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $paciente->email ?? '—' }}</p>
                    <p class="mb-0"><strong>Dirección:</strong> {{ $paciente->direccion ?? '—' }}, {{ $paciente->ciudad ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">Convenio</h6>
                    <p class="mb-1"><strong>Convenio:</strong> {{ $paciente->convenio?->nombre ?? 'Particular / sin convenio' }}</p>
                    <p class="mb-0"><strong>N° póliza:</strong> {{ $paciente->numero_poliza ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Historial de ventas</strong></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>N° venta</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($paciente->ventas as $venta)
                        <tr>
                            <td>{{ $venta->numero_venta }}</td>
                            <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                            <td>${{ number_format($venta->total, 0, ',', '.') }}</td>
                            <td><span class="badge text-bg-secondary">{{ ucfirst($venta->estado) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin ventas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Recetas</strong></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>N° receta</th><th>Fecha emisión</th><th>Tipo</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($paciente->recetas as $receta)
                        <tr>
                            <td>{{ $receta->numero_receta }}</td>
                            <td>{{ $receta->fecha_emision->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($receta->tipo) }}</td>
                            <td><span class="badge text-bg-secondary">{{ ucfirst($receta->estado) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin recetas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection