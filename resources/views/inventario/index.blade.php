@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
@include('inventario._styles')

<div class="container-fluid px-0 inventario-modulo">

    {{-- ================= ENCABEZADO ================= --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Inventario</h1>
            <p class="text-muted mb-0">Control y gestión del stock de la farmacia</p>
        </div>

        @can('gestionar_stock')
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('inventario.transferir.form') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left-right me-1"></i>
                    Nuevo traslado
                </a>

                <a href="{{ route('inventario.ajustar.form') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-sliders me-1"></i>
                    Nuevo ajuste
                </a>
            </div>
        @endcan
    </div>

    {{-- ================= MENSAJES ================= --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- ================= UBICACIONES ================= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body pb-0">

            <div class="nav nav-tabs border-0 gap-2">

                @if($ubicacionesTabs->has('vitrina'))
                    <a href="{{ route('inventario.index', [
                        'tipo' => 'vitrina',
                        'producto_id' => request('producto_id'),
                        'buscar' => request('buscar')
                    ]) }}"
                       class="nav-link px-3 py-2 {{ $tipoActual === 'vitrina' ? 'active fw-semibold' : 'text-secondary' }}">

                        <i class="bi bi-shop me-1"></i>
                        Vitrina
                    </a>
                @endif

                @if($ubicacionesTabs->has('bodega'))
                    <a href="{{ route('inventario.index', [
                        'tipo' => 'bodega',
                        'producto_id' => request('producto_id'),
                        'buscar' => request('buscar')
                    ]) }}"
                       class="nav-link px-3 py-2 {{ $tipoActual === 'bodega' ? 'active fw-semibold' : 'text-secondary' }}">

                        <i class="bi bi-box-seam me-1"></i>
                        Bodega
                    </a>
                @endif

                @if($ubicacionesTabs->has('frigorifica'))
                    <a href="{{ route('inventario.index', [
                        'tipo' => 'frigorifica',
                        'producto_id' => request('producto_id'),
                        'buscar' => request('buscar')
                    ]) }}"
                       class="nav-link px-3 py-2 {{ $tipoActual === 'frigorifica' ? 'active fw-semibold' : 'text-secondary' }}">

                        <i class="bi bi-snow me-1"></i>
                        Frigorífica
                    </a>
                @endif

            </div>

        </div>

        <div class="card-body border-top">
            <h5 class="mb-1">{{ $ubicacionActual->nombre }}</h5>
            <small class="text-muted">
                Stock disponible actualmente en esta ubicación
            </small>
        </div>
    </div>

    {{-- ================= RESUMEN ================= --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 tarjeta-resumen">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="tarjeta-icon bg-primary-subtle text-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <div>
                        <div class="etiqueta">Stock total</div>
                        <div class="valor text-primary">
                            {{ $stockTotalUnidades }}
                        </div>
                        <div class="text-muted small">unidades</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('inventario.alertas') }}" class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 tarjeta-resumen tarjeta-clickable">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="tarjeta-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div>
                            <div class="etiqueta">Stock crítico</div>
                            <div class="valor text-danger">
                                {{ $stockCriticoCount }}
                            </div>
                            <div class="text-muted small">productos</div>
                        </div>

                    </div>
                </div>

            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('inventario.proximos-vencer') }}" class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 tarjeta-resumen tarjeta-clickable">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="tarjeta-icon bg-warning-subtle text-warning">
                            <i class="bi bi-calendar-event"></i>
                        </div>

                        <div>
                            <div class="etiqueta">Próx. a vencer</div>
                            <div class="valor text-warning">
                                {{ $porVencerCount }}
                            </div>
                            <div class="text-muted small">lotes</div>
                        </div>

                    </div>
                </div>

            </a>
        </div>

        <div class="col-6 col-xl-3">
            <a href="{{ route('inventario.proximos-vencer') }}?dias=0"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 tarjeta-resumen tarjeta-clickable">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="tarjeta-icon bg-secondary-subtle text-secondary">
                            <i class="bi bi-x-octagon"></i>
                        </div>

                        <div>
                            <div class="etiqueta">Vencidos</div>
                            <div class="valor text-dark">
                                {{ $vencidosCount }}
                            </div>
                            <div class="text-muted small">lotes</div>
                        </div>

                    </div>
                </div>

            </a>
        </div>

    </div>

    {{-- ================= FILTROS ================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="GET">
                <input type="hidden" name="tipo" value="{{ $tipoActual }}">

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Producto</label>

                        <select name="producto_id" class="form-select">
                            <option value="">Todos los productos</option>

                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}"
                                    @selected(request('producto_id') == $producto->id)>

                                    {{ $producto->nombre }}

                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Buscar</label>

                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search text-muted"></i>
                            </span>

                            <input type="text"
                                   name="buscar"
                                   value="{{ request('buscar') }}"
                                   class="form-control"
                                   placeholder="Nombre, código o lote">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary flex-grow-1">
                                Filtrar
                            </button>

                            @if(request()->hasAny(['producto_id', 'buscar']))
                                <a href="{{ route('inventario.index', ['tipo' => $tipoActual]) }}"
                                   class="btn btn-outline-secondary"
                                   title="Limpiar filtros">

                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif

                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    {{-- ================= STOCK ================= --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">

        <div>
            <h5 class="mb-0">
                Stock en {{ $ubicacionActual->nombre }}
            </h5>

            <small class="text-muted">
                {{ $stock->total() }}
                {{ $stock->total() === 1 ? 'producto encontrado' : 'productos encontrados' }}
            </small>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('inventario.alertas') }}"
               class="btn btn-sm btn-outline-danger">

                <i class="bi bi-exclamation-triangle me-1"></i>
                Alertas
            </a>

            <a href="{{ route('inventario.proximos-vencer') }}"
               class="btn btn-sm btn-outline-warning">

                <i class="bi bi-calendar-event me-1"></i>
                Vencimientos
            </a>

            <a href="{{ route('inventario.historial') }}"
               class="btn btn-sm btn-outline-secondary">

                <i class="bi bi-clock-history me-1"></i>
                Historial
            </a>

        </div>

    </div>

    {{-- ================= TABLA ================= --}}
    <div class="card border-0 shadow-sm">

        <div class="table-responsive">

            <table class="table align-middle mb-0 tabla-stock">

                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Lotes</th>
                        <th class="text-end">Stock</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($stock as $s)

                        @php
                            $cantidad = (int) $s->cantidad_total;
                            $critico = $s->producto?->stock_critico ?? 0;
                            $minimo = $s->producto?->stock_minimo ?? 0;

                            $descuadrePendiente = ($descuadresPendientes ?? collect())
                                ->first(function ($descuadre) use ($s, $ubicacionActual) {
                                    return (int) $descuadre->producto_id === (int) $s->producto_id
                                        && (int) $descuadre->ubicacion_id === (int) $ubicacionActual->id;
                                });

                            if ($cantidad <= 0) {
                                [$estadoTexto, $estadoBadge] = ['Agotado', 'secondary'];
                            } elseif ($cantidad <= $critico) {
                                [$estadoTexto, $estadoBadge] = ['Crítico', 'danger'];
                            } elseif ($cantidad <= $minimo) {
                                [$estadoTexto, $estadoBadge] = ['Bajo', 'warning'];
                            } else {
                                [$estadoTexto, $estadoBadge] = ['Normal', 'success'];
                            }
                        @endphp

                        <tr>

                            <td>
                                <div class="producto-cell">
                                    <span class="producto-nombre">
                                        {{ $s->producto?->nombre }}
                                    </span>

                                    <span class="producto-codigo">
                                        {{ $s->producto?->codigo_interno }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge text-bg-light border">
                                    {{ $s->lotes_count }}
                                    {{ $s->lotes_count == 1 ? 'lote' : 'lotes' }}
                                </span>
                            </td>

                            <td class="text-end">
                                <span class="cantidad-valor">
                                    {{ $cantidad }}
                                </span>

                                <span class="cantidad-unidad">
                                    uds
                                </span>
                            </td>

                            <td class="text-center">

                                @if($descuadrePendiente)

                                    <span class="badge text-bg-warning">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        Pendiente de aprobación
                                    </span>

                                    <div class="small text-muted mt-1">
                                        Ajuste:
                                        {{ $descuadrePendiente->diferencia > 0 ? '+' : '' }}
                                        {{ $descuadrePendiente->diferencia }} uds
                                    </div>

                                @else

                                    <span class="badge text-bg-{{ $estadoBadge }}">
                                        {{ $estadoTexto }}
                                    </span>

                                @endif

                            </td>

                            <td class="text-end">

                                @can('gestionar_stock')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success btn-trasladar"

                                            data-producto-id="{{ $s->producto_id }}"
                                            data-producto-nombre="{{ $s->producto?->nombre }}"
                                            data-producto-codigo="{{ $s->producto?->codigo_interno }}"
                                            data-producto-detalle="{{ $s->producto?->concentracion }}"

                                            data-ubicacion-id="{{ $ubicacionActual->id }}"
                                            data-ubicacion-nombre="{{ $ubicacionActual->nombre }}">

                                        <i class="bi bi-arrow-left-right me-1"></i>
                                        Trasladar

                                    </button>
                                @endcan

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5"
                                class="text-center text-muted py-5">

                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                No hay productos con stock en
                                {{ $ubicacionActual->nombre }}.

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($stock->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $stock->links() }}
            </div>
        @endif

    </div>

</div>

@include('inventario._modal-traslado')

@endsection