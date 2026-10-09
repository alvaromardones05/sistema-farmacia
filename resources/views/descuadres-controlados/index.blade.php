@extends('layouts.app')

@section('title', 'Descuadres controlados')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Descuadres controlados</h1>
            <p class="text-muted mb-0">
                Revisión de ajustes pendientes de medicamentos controlados
            </p>
        </div>

        <span class="badge text-bg-warning fs-6">
            {{ $pendientes }} pendientes
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- FILTRO --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <form method="GET"
                  class="d-flex align-items-end gap-2 flex-wrap">

                <div>
                    <label class="form-label">
                        Estado
                    </label>

                    <select name="estado"
                            class="form-select">

                        <option value="">Todos</option>

                        <option value="registrado"
                            @selected(request('estado') === 'registrado')>
                            Pendientes
                        </option>

                        <option value="resuelto"
                            @selected(request('estado') === 'resuelto')>
                            Aprobados
                        </option>

                        <option value="rechazado"
                            @selected(request('estado') === 'rechazado')>
                            Rechazados
                        </option>

                    </select>
                </div>

                <button class="btn btn-primary">
                    Filtrar
                </button>

                @if(request('estado'))
                    <a href="{{ route('descuadres-controlados.index') }}"
                       class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                @endif

            </form>

        </div>
    </div>

    {{-- TABLA --}}
    <div class="card border-0 shadow-sm">

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote / ubicación</th>
                        <th class="text-center">Esperado</th>
                        <th class="text-center">Contado</th>
                        <th class="text-center">Diferencia</th>
                        <th>Reportado por</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Revisión</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($descuadres as $descuadre)

                        @php
                            $pendiente = in_array(
                                $descuadre->estado,
                                ['registrado', 'investigado']
                            );
                        @endphp

                        <tr>

                            <td>
                                <strong>
                                    {{ $descuadre->producto?->nombre }}
                                </strong>

                                <div class="small text-muted">
                                    {{ $descuadre->producto?->codigo_interno }}
                                </div>
                            </td>

                            <td>
                                <div>
                                    Lote {{ $descuadre->lote?->numero_lote }}
                                </div>

                                <small class="text-muted">
                                    {{ $descuadre->ubicacion?->nombre ?? 'Sin ubicación' }}
                                </small>
                            </td>

                            <td class="text-center">
                                {{ $descuadre->cantidad_esperada }}
                            </td>

                            <td class="text-center fw-semibold">
                                {{ $descuadre->cantidad_contada }}
                            </td>

                            <td class="text-center">
                                <span class="fw-bold
                                    {{ $descuadre->diferencia < 0 ? 'text-danger' : 'text-success' }}">

                                    {{ $descuadre->diferencia > 0 ? '+' : '' }}
                                    {{ $descuadre->diferencia }}

                                </span>
                            </td>

                            <td>
                                {{ $descuadre->usuario?->name }}

                                <div class="small text-muted">
                                    {{ $descuadre->fecha_inventario?->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td class="text-center">

                                @if($descuadre->estado === 'resuelto')
                                    <span class="badge text-bg-success">
                                        Aprobado
                                    </span>

                                @elseif($descuadre->estado === 'rechazado')
                                    <span class="badge text-bg-danger">
                                        Rechazado
                                    </span>

                                @else
                                    <span class="badge text-bg-warning">
                                        Pendiente
                                    </span>
                                @endif

                            </td>

                            <td class="text-end">

                                @if($pendiente)

                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#revision-{{ $descuadre->id }}">
                                        Revisar
                                    </button>

                                @else

                                    <small class="text-muted">
                                        {{ $descuadre->revisor?->name }}
                                    </small>

                                @endif

                            </td>

                        </tr>

                        {{-- REVISIÓN --}}
                        @if($pendiente)

                            <tr class="collapse bg-light"
                                id="revision-{{ $descuadre->id }}">

                                <td colspan="8">

                                    <div class="p-3">

                                        <div class="mb-3">
                                            <strong>Motivo informado:</strong>

                                            <div class="text-muted">
                                                {{ $descuadre->explicacion ?: 'Sin explicación.' }}
                                            </div>
                                        </div>

                                        <form method="POST"
                                              action="{{ route('descuadres-controlados.aprobar', $descuadre) }}"
                                              class="mb-2">

                                            @csrf

                                            <label class="form-label">
                                                Observación del Químico
                                            </label>

                                            <textarea name="observacion_revision"
                                                      class="form-control mb-2"
                                                      rows="2"
                                                      maxlength="1000"
                                                      placeholder="Opcional al aprobar"></textarea>

                                            <button class="btn btn-success btn-sm"
                                                    onclick="return confirm('¿Confirmas aprobar este ajuste? El stock será modificado.')">

                                                <i class="bi bi-check-lg me-1"></i>
                                                Aprobar ajuste

                                            </button>

                                        </form>

                                        <form method="POST"
                                              action="{{ route('descuadres-controlados.rechazar', $descuadre) }}">

                                            @csrf

                                            <textarea name="observacion_revision"
                                                      class="form-control mb-2"
                                                      rows="2"
                                                      maxlength="1000"
                                                      placeholder="Motivo del rechazo"
                                                      required></textarea>

                                            <button class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('¿Confirmas rechazar este descuadre?')">

                                                <i class="bi bi-x-lg me-1"></i>
                                                Rechazar

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endif

                    @empty

                        <tr>
                            <td colspan="8"
                                class="text-center text-muted py-5">

                                <i class="bi bi-check-circle fs-3 d-block mb-2"></i>

                                No hay descuadres registrados.

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($descuadres->hasPages())
            <div class="card-footer bg-white">
                {{ $descuadres->links() }}
            </div>
        @endif

    </div>

</div>
@endsection