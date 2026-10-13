@extends('layouts.app')

@section('title', 'Convenios')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">Convenios</h1>
            <p class="text-muted mb-0">
                Convenios, descuentos y coberturas de productos
            </p>
        </div>

        <a href="{{ route('convenios.create') }}"
           class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo convenio
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">

        <div class="table-responsive">
            <table class="table align-middle mb-0">

                <thead>
                    <tr>
                        <th>Convenio</th>
                        <th>Tipo</th>
                        <th class="text-center">Descuento general</th>
                        <th class="text-center">Coberturas</th>
                        <th class="text-center">Pacientes</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($convenios as $convenio)

                        <tr>
                            <td>
                                <strong>{{ $convenio->nombre }}</strong>

                                <div class="small text-muted">
                                    {{ $convenio->codigo_convenio }}
                                </div>
                            </td>

                            <td>
                                {{ strtoupper($convenio->tipo) }}
                            </td>

                            <td class="text-center">
                                {{ number_format($convenio->descuento_pct, 0) }}%
                            </td>

                            <td class="text-center">
                                {{ $convenio->coberturas_count }}
                            </td>

                            <td class="text-center">
                                {{ $convenio->pacientes_count }}
                            </td>

                            <td class="text-center">
                                @if($convenio->activo)
                                    <span class="badge text-bg-success">
                                        Activo
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">
                                <a href="{{ route('convenios.edit', $convenio) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Gestionar
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="text-center text-muted py-5">
                                No hay convenios registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        @if($convenios->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $convenios->links() }}
            </div>
        @endif

    </div>

</div>
@endsection