@extends('layouts.app')

@section('title', 'Alertas de stock')

@section('content')
@include('inventario._styles')
<div class="container-fluid px-0 inventario-modulo">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario</p>
            <h1 class="h3 mb-0">Alertas de stock</h1>
        </div>
        <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    {{-- STOCK CRÍTICO --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-danger text-white">
            <strong>Stock crítico</strong> ({{ $stockCritico->count() }})
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock actual</th>
                        <th>Umbral crítico</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockCritico as $producto)
                        <tr>
                            <td>{{ $producto->nombre }} ({{ $producto->codigo_interno }})</td>
                            <td><span class="badge text-bg-danger">{{ (int) $producto->stock_total }}</span></td>
                            <td>{{ $producto->stock_critico }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin alertas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- STOCK BAJO --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-warning">
            <strong>Stock bajo</strong> ({{ $stockBajo->count() }})
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock actual</th>
                        <th>Umbral mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockBajo as $producto)
                        <tr>
                            <td>{{ $producto->nombre }} ({{ $producto->codigo_interno }})</td>
                            <td><span class="badge text-bg-warning">{{ (int) $producto->stock_total }}</span></td>
                            <td>{{ $producto->stock_minimo }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Sin alertas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PRÓXIMOS A VENCER --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <strong>Próximos a vencer</strong> (≤30 días) ({{ $porVencer->count() }})
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($porVencer as $lote)
                        <tr>
                            <td>{{ $lote->producto?->nombre }}</td>
                            <td>{{ $lote->numero_lote }}</td>
                            <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>{{ (int) $lote->stock_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin alertas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- VENCIDOS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <strong>Vencidos (con stock)</strong> ({{ $vencidos->count() }})
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Vencimiento</th>
                        <th>Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vencidos as $lote)
                        <tr>
                            <td>{{ $lote->producto?->nombre }}</td>
                            <td>{{ $lote->numero_lote }}</td>
                            <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td>{{ (int) $lote->stock_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Sin alertas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection