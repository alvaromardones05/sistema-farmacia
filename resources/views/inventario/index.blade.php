@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
@include('inventario._styles')
<div class="container-fluid px-0 inventario-modulo">

    {{-- ================= ENCABEZADO ================= --}}
    <div class="mb-3">
        <h1 class="h3 mb-0">Inventario</h1>
        <p class="text-muted mb-0">Control y gestión del stock</p>
    </div>

    <div class="accesos-rapidos d-flex gap-2 mb-4">
        <a href="{{ route('inventario.alertas') }}" class="btn btn-outline-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>Alertas
        </a>
        <a href="{{ route('inventario.proximos-vencer') }}" class="btn btn-outline-warning">
            <i class="bi bi-calendar-x me-1"></i>Próximos a vencer
        </a>
        <a href="{{ route('inventario.historial') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>Historial
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ================= TARJETAS RESUMEN ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 tarjeta-resumen">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="tarjeta-icon bg-primary-subtle text-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="etiqueta">Stock total</div>
                        <div class="valor text-primary">{{ $stockTotalUnidades }}</div>
                        <div class="text-muted small">unidades</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('inventario.alertas') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 tarjeta-resumen tarjeta-clickable">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="tarjeta-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="etiqueta">Stock crítico</div>
                            <div class="valor text-danger">{{ $stockCriticoCount }}</div>
                            <div class="text-muted small">productos</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('inventario.proximos-vencer') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 tarjeta-resumen tarjeta-clickable">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="tarjeta-icon bg-warning-subtle text-warning">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <div>
                            <div class="etiqueta">Próx. a vencer</div>
                            <div class="valor text-warning">{{ $porVencerCount }}</div>
                            <div class="text-muted small">lotes</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('inventario.proximos-vencer') }}?dias=0" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 tarjeta-resumen tarjeta-clickable">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="tarjeta-icon bg-secondary-subtle text-secondary">
                            <i class="bi bi-x-octagon"></i>
                        </div>
                        <div>
                            <div class="etiqueta">Vencidos</div>
                            <div class="valor text-dark">{{ $vencidosCount }}</div>
                            <div class="text-muted small">lotes</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ================= OPERACIONES ================= --}}
    @can('gestionar_stock')
        <div class="seccion-operaciones mb-4">
            <p class="texto-subtitulo text-secondary mb-3">Operaciones</p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('inventario.transferir.form') }}" class="btn btn-primary btn-operacion">
                    <i class="bi bi-arrow-left-right me-1"></i>Trasladar stock
                </a>
                <a href="{{ route('inventario.ajustar.form') }}" class="btn btn-operacion btn-operacion-ajuste">
                    <i class="bi bi-gear me-1"></i>Ajustar stock
                </a>
            </div>
        </div>
    @endcan

    {{-- ================= FILTROS ================= --}}
    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Producto</label>
                    <select name="producto_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}" @selected(request('producto_id') == $producto->id)>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ubicación</label>
                    <select name="ubicacion_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(request('ubicacion_id') == $ubicacion->id)>
                                {{ $ubicacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        class="form-control" placeholder="Nombre, código o lote">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </div>
        @if (request()->hasAny(['producto_id', 'ubicacion_id', 'buscar']))
            <div class="card-footer bg-white border-0">
                <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg me-1"></i>Limpiar filtros
                </a>
            </div>
        @endif
    </form>

    {{-- ================= TABLA: STOCK ACTUAL ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <p class="texto-subtitulo text-secondary mb-0">Stock actual</p>
        <small class="text-muted">
            {{ $stock->total() }} {{ $stock->total() === 1 ? 'registro' : 'registros' }}
        </small>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0 tabla-stock">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Ubicación</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stock as $s)
                        @php
                            $critico = $s->producto?->stock_critico ?? 0;
                            $minimo = $s->producto?->stock_minimo ?? 0;

                            if ($s->cantidad <= 0) {
                                [$estadoTexto, $estadoBadge] = ['Agotado', 'secondary'];
                            } elseif ($s->cantidad <= $critico) {
                                [$estadoTexto, $estadoBadge] = ['Crítico', 'danger'];
                            } elseif ($s->cantidad <= $minimo) {
                                [$estadoTexto, $estadoBadge] = ['Bajo', 'warning'];
                            } else {
                                [$estadoTexto, $estadoBadge] = ['Normal', 'success'];
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="producto-cell">
                                    <span class="producto-nombre">{{ $s->producto?->nombre }}</span>
                                    <span class="producto-codigo">{{ $s->producto?->codigo_interno }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="lote-chip">{{ $s->lote?->numero_lote }}</span>
                            </td>
                            <td>
                                <span class="ubicacion-cell">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $s->ubicacion?->nombre }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="cantidad-valor">{{ $s->cantidad }}</span>
                                <span class="cantidad-unidad">uds</span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-{{ $estadoBadge }}">{{ $estadoTexto }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                                No hay stock que coincida con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $stock->links() }}</div>
    </div>

</div>
@endsection