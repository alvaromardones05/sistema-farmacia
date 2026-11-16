@extends('layouts.app')

@section('title', 'Próximos a vencer')

@section('content')
@include('inventario._styles')
<div class="container-fluid px-0 inventario-modulo">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="texto-subtitulo text-primary mb-1">Inventario</p>
            <h1 class="h3 mb-0">
                @if ($dias === 0)
                    Lotes vencidos
                @else
                    Próximos a vencer (orden FEFO)
                @endif
            </h1>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->can('ver_stock') || auth()->user()->can('gestionar_stock'))
                <a href="{{ route('inventario.canjes.index') }}" class="btn btn-outline-primary">Seguimiento de canjes</a>
            @endif
            <a href="{{ auth()->user()->can('ver_stock') ? route('inventario.index') : route('inventario.canjes.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    {{-- Filtro de período --}}
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label for="dias" class="form-label mb-0">Período</label>
            <select name="dias" id="dias" class="form-select" onchange="this.form.submit()">
                @foreach ([0 => 'Vencidos', 15 => '15 días', 30 => '30 días', 60 => '60 días', 90 => '90 días'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($dias === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Días restantes</th>
                        <th>Stock comercial</th>
                        <th>Urgencia</th>
                        @can('gestionar_stock')
                            <th>Canje</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lotes as $lote)
                        @php
                            $diasRestantes = (int) today()->diffInDays($lote->fecha_vencimiento, false);
                            $badge = $diasRestantes < 0 ? 'dark' : ($diasRestantes <= 7 ? 'danger' : ($diasRestantes <= 30 ? 'warning' : 'info'));
                            $texto = $diasRestantes < 0 ? 'Vencido' : ($diasRestantes <= 7 ? 'Urgente' : ($diasRestantes <= 30 ? 'Próximo' : 'A tiempo'));
                        @endphp
                        <tr>
                            <td>{{ $lote->producto?->nombre }} ({{ $lote->producto?->codigo_interno }})</td>
                            <td>{{ $lote->numero_lote }}</td>
                            <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>{{ $diasRestantes < 0 ? 'Vencido hace ' . abs($diasRestantes) . ' días' : $diasRestantes . ' días' }}</td>
                            <td>{{ (int) $lote->stock_comercial }}</td>
                            <td><span class="badge text-bg-{{ $badge }}">{{ $texto }}</span></td>
                            @can('gestionar_stock')
                                <td>
                                    @if ($lote->stock_para_canje > 0 && $lote->estado === 'activo' && $lote->producto?->activo && $lote->fecha_vencimiento->gte(today()) && in_array($dias, [15, 30, 60, 90], true))
                                        <a href="{{ route('inventario.canjes.create', ['lote' => $lote->id, 'dias' => $dias]) }}" class="btn btn-sm btn-outline-primary">
                                            Solicitar canje
                                        </a>
                                        <div class="small text-muted">{{ $lote->stock_para_canje }} un. compatibles</div>
                                    @else
                                        <span class="text-muted small">No disponible</span>
                                    @endif
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('gestionar_stock') ? 7 : 6 }}" class="text-center text-muted py-4">
                                @if ($dias === 0)
                                    No hay lotes vencidos con stock.
                                @else
                                    No hay lotes en esta ventana.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection