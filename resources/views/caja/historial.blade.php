@extends('layouts.app')

@section('title', 'Historial de caja')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">Historial de caja</h1>
            <p class="text-muted mb-0">
                Aperturas, cierres y diferencias registradas
            </p>
        </div>

        <a href="{{ route('caja.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    <div class="card border-0 shadow-sm">

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Responsable</th>
                        <th class="text-end">Ventas</th>
                        <th class="text-end">Esperado</th>
                        <th class="text-end">Contado</th>
                        <th class="text-end">Diferencia</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($cierres as $cierre)

                        @php
                            /*
                             * diferencia = contado - esperado
                             * por lo tanto:
                             * esperado = contado - diferencia
                             */
                            $esperado =
                                (float) $cierre->saldo_contado -
                                (float) $cierre->diferencia;
                        @endphp

                        <tr>

                            <td>
                                <strong>
                                    {{ $cierre->aperturaCaja?->caja?->descripcion }}
                                </strong>

                                <div class="small text-muted">
                                    {{ $cierre->aperturaCaja?->caja?->numero_caja }}
                                </div>
                            </td>

                            <td>
                                {{ $cierre->aperturaCaja?->fecha_apertura?->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                {{ $cierre->fecha_cierre?->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                {{ $cierre->usuario?->name }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($cierre->monto_vendido, 0, ',', '.') }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($esperado, 0, ',', '.') }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($cierre->saldo_contado, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold
                                {{ $cierre->diferencia < 0 ? 'text-danger' : ($cierre->diferencia > 0 ? 'text-warning' : 'text-success') }}">

                                {{ $cierre->diferencia > 0 ? '+' : '' }}
                                ${{ number_format($cierre->diferencia, 0, ',', '.') }}

                            </td>

                            <td class="text-center">

                                @if($cierre->estado === 'aceptado')
                                    <span class="badge text-bg-success">
                                        Cuadrado
                                    </span>

                                @elseif($cierre->estado === 'con_diferencia')
                                    <span class="badge text-bg-warning">
                                        Con diferencia
                                    </span>

                                @else
                                    <span class="badge text-bg-danger">
                                        Rechazado
                                    </span>
                                @endif

                            </td>

                        </tr>

                        @if($cierre->observaciones)
                            <tr class="table-light">
                                <td colspan="9"
                                    class="small text-muted py-2">

                                    <strong>Observación:</strong>
                                    {{ $cierre->observaciones }}

                                </td>
                            </tr>
                        @endif

                    @empty

                        <tr>
                            <td colspan="9"
                                class="text-center text-muted py-5">

                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Todavía no existen cierres de caja.

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($cierres->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $cierres->links() }}
            </div>
        @endif

    </div>

</div>
@endsection