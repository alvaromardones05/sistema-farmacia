@extends('layouts.app')


@section('content')


    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

    <div class="mb-4">

        <h1 class="h3">
            Panel de Control
        </h1>

        <p class="text-muted mb-0">
            Bienvenido al Sistema de Gestión de Farmacia.
        </p>

    </div>

    <div class="row g-3 mb-4">
        @if ($metricas['productos_activos'] !== null)
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Productos activos</div><div class="fs-3 fw-semibold">{{ $metricas['productos_activos'] }}</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Stock crítico</div><div class="fs-3 fw-semibold text-danger">{{ $metricas['stock_critico'] }}</div></div></div></div>
        @endif
        @if ($metricas['lotes_por_vencer'] !== null)
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Lotes por vencer</div><div class="fs-3 fw-semibold text-warning">{{ $metricas['lotes_por_vencer'] }}</div></div></div></div>
        @endif
        @if ($metricas['ventas_dia'] !== null)
            <div class="col-sm-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">Ventas de hoy</div><div class="fs-3 fw-semibold">$ {{ number_format((float) $metricas['ventas_dia'], 0, ',', '.') }}</div><div class="small text-muted">Mes: $ {{ number_format((float) $metricas['ventas_mes'], 0, ',', '.') }}</div></div></div></div>
        @endif
    </div>



    @if ($puedeVerProductos)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Productos</h5>
            <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>

        <div class="lista-dashboard mb-4">
            @forelse ($productos as $producto)
                <div class="item-dashboard d-flex justify-content-between gap-3">
                    <div>
                        <strong>{{ $producto->nombre }}</strong>
                        <div class="small text-muted">{{ $producto->principio_activo ?: 'Principio activo no informado' }}</div>
                    </div>
                    <span class="text-nowrap">Stock: {{ $producto->stock->sum('cantidad') }}</span>
                </div>
            @empty
                <div class="item-dashboard text-muted">No hay productos registrados.</div>
            @endforelse
        </div>
    @endif

    @if ($puedeVerVentas)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Ventas</h5>
            <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>

        <div class="lista-dashboard mb-4">
            @forelse ($ventas as $venta)
                <div class="item-dashboard d-flex justify-content-between gap-3">
                    <div>
                        <strong>Venta {{ $venta->numero_venta }}</strong>
                        <div class="small text-muted">
                            {{ optional($venta->fecha_venta)->format('d/m/Y H:i') }}
                            · {{ $venta->usuario?->name ?: 'Usuario no disponible' }}
                        </div>
                    </div>
                    <span class="text-nowrap">$ {{ number_format((float) $venta->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="item-dashboard text-muted">No hay ventas registradas.</div>
            @endforelse
        </div>
    @endif

    @if ($puedeVerLotes)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Lotes</h5>
            <a href="{{ route('lotes.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>

        <div class="lista-dashboard mb-4">
            @forelse ($lotes as $lote)
                <div class="item-dashboard d-flex justify-content-between gap-3">
                    <div>
                        <strong>{{ $lote->producto?->nombre ?: 'Producto no disponible' }}</strong>
                        <div class="small text-muted">
                            Lote {{ $lote->numero_lote }} · Vence {{ $lote->fecha_vencimiento?->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="text-nowrap">Stock: {{ $lote->stock->sum('cantidad') }}</span>
                </div>
            @empty
                <div class="item-dashboard text-muted">No hay lotes registrados.</div>
            @endforelse
        </div>
    @endif

    @if ($esAdministrador)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Usuarios</h5>
            <a href="{{ route('usuarios.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>

        <div class="lista-dashboard">
            @forelse ($usuarios as $usuario)
                <div class="item-dashboard d-flex justify-content-between gap-3">
                    <div>
                        <strong>{{ $usuario->name }} {{ $usuario->apellidos }}</strong>
                        <div class="small text-muted">{{ $usuario->email }}</div>
                    </div>
                    <span class="text-nowrap">{{ $usuario->roles->pluck('name')->join(', ') }}</span>
                </div>
            @empty
                <div class="item-dashboard text-muted">No hay usuarios registrados.</div>
            @endforelse
        </div>
    @endif


@endsection
```
