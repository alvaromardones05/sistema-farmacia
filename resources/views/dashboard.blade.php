{{-- resources/views/dashboard.blade.php --}}

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    .dashboard-header {
        margin-bottom: 1.75rem;
    }

    .dashboard-header h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: .25rem;
    }

    .dashboard-header p {
        color: #6b7280;
        margin: 0;
        font-size: .9rem;
    }

    .metric-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.4rem;
        height: 100%;
    }

    .metric-label {
        color: #6b7280;
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: .5rem;
    }

    .metric-value {
        color: #111827;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
    }

    .metric-description {
        color: #9ca3af;
        font-size: .75rem;
        margin-top: .6rem;
    }

    .attention-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
    }

    .attention-header {
        padding: 1.25rem 1.4rem;
        border-bottom: 1px solid #f0f2f5;
    }

    .attention-title {
        font-size: .95rem;
        font-weight: 700;
        margin: 0;
        color: #1f2937;
    }

    .attention-subtitle {
        font-size: .78rem;
        color: #9ca3af;
        margin-top: .2rem;
    }

    .attention-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .attention-item:last-child {
        border-bottom: 0;
    }

    .attention-content {
        display: flex;
        align-items: center;
        gap: .8rem;
    }

    .attention-indicator {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .indicator-danger {
        background: #dc3545;
    }

    .indicator-warning {
        background: #f59e0b;
    }

    .indicator-info {
        background: #0d6efd;
    }

    .attention-text {
        font-size: .86rem;
        color: #374151;
    }

    .attention-text strong {
        color: #111827;
    }

    .quick-action {
        display: block;
        height: 100%;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        text-decoration: none;
        color: #1f2937;
        transition: .15s ease;
    }

    .quick-action:hover {
        color: #0d6efd;
        border-color: #b8d0f5;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, .04);
    }

    .quick-action-title {
        font-size: .88rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .quick-action-description {
        font-size: .73rem;
        color: #9ca3af;
    }

    .dashboard-section-title {
        color: #6b7280;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .8rem;
    }

    .admin-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.5rem;
    }

    .admin-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #111827;
    }
</style>


<div class="container-fluid px-0">

    {{-- =========================================================
        ADMINISTRADOR
    ========================================================== --}}

    @if ($esAdministrador)

        <div class="dashboard-header">

            <h1>Panel de control</h1>

            <p>
                Administración general del sistema.
            </p>

        </div>


        <div class="row g-3">

            <div class="col-md-6 col-xl-4">

                <div class="admin-card">

                    <div class="text-muted small mb-2">
                        Usuarios registrados
                    </div>

                    <div class="admin-number">
                        {{ isset($usuarios) ? $usuarios->count() : 0 }}
                    </div>

                    <div class="mt-3">

                        <a href="{{ route('usuarios.index') }}"
                           class="btn btn-sm btn-outline-primary">

                            Gestionar usuarios

                        </a>

                    </div>

                </div>

            </div>

        </div>


    {{-- =========================================================
        DASHBOARD OPERACIONAL
    ========================================================== --}}

    @else

        <div class="dashboard-header">

            <h1>Panel de control</h1>

            <p>
                Estado actual de la farmacia y situaciones que requieren atención.
            </p>

        </div>


        {{-- =====================================================
            MÉTRICAS
        ====================================================== --}}

        <div class="row g-3 mb-4">

            <div class="col-md-4">

                <div class="metric-card">

                    <div class="metric-label">
                        Stock crítico
                    </div>

                    <div class="metric-value text-danger">
                        {{ $metricas['stock_critico'] ?? 0 }}
                    </div>

                    <div class="metric-description">
                        Productos que requieren reposición
                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="metric-card">

                    <div class="metric-label">
                        Próximos a vencer
                    </div>

                    <div class="metric-value">
                        {{ $metricas['lotes_por_vencer'] ?? 0 }}
                    </div>

                    <div class="metric-description">
                        Lotes que requieren atención
                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="metric-card">

                    <div class="metric-label">
                        Ventas de hoy
                    </div>

                    <div class="metric-value">
                        ${{ number_format(
                            (float) ($metricas['ventas_dia'] ?? 0),
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>

                    <div class="metric-description">
                        Total vendido durante la jornada
                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            REQUIERE ATENCIÓN
        ====================================================== --}}

        <div class="dashboard-section-title">
            Requiere atención
        </div>


        <div class="attention-card mb-4">

            <div class="attention-header">

                <h2 class="attention-title">
                    Estado operativo
                </h2>

                <div class="attention-subtitle">
                    Revisa únicamente los puntos que requieren una acción.
                </div>

            </div>


            {{-- STOCK CRÍTICO --}}

            @if (($metricas['stock_critico'] ?? 0) > 0)

                <div class="attention-item">

                    <div class="attention-content">

                        <div class="attention-indicator indicator-danger"></div>

                        <div class="attention-text">

                            <strong>
                                {{ $metricas['stock_critico'] }}
                            </strong>

                            productos tienen stock crítico.

                        </div>

                    </div>


                    @if (Route::has('inventario.index'))

                        <a href="{{ route('inventario.index') }}"
                           class="btn btn-sm btn-outline-danger">

                            Ver inventario

                        </a>

                    @endif

                </div>

            @endif


            {{-- LOTES PRÓXIMOS A VENCER --}}

            @if (($metricas['lotes_por_vencer'] ?? 0) > 0)

                <div class="attention-item">

                    <div class="attention-content">

                        <div class="attention-indicator indicator-warning"></div>

                        <div class="attention-text">

                            <strong>
                                {{ $metricas['lotes_por_vencer'] }}
                            </strong>

                            lotes están próximos a vencer.

                        </div>

                    </div>


                    @if (Route::has('lotes.index'))

                        <a href="{{ route('lotes.index') }}"
                           class="btn btn-sm btn-outline-warning">

                            Ver lotes

                        </a>

                    @endif

                </div>

            @endif


            {{-- AJUSTES PENDIENTES --}}

            @if (($metricas['ajustes_pendientes'] ?? 0) > 0)

                <div class="attention-item">

                    <div class="attention-content">

                        <div class="attention-indicator indicator-info"></div>

                        <div class="attention-text">

                            <strong>
                                {{ $metricas['ajustes_pendientes'] }}
                            </strong>

                            ajustes están pendientes de revisión.

                        </div>

                    </div>


                    @if (Route::has('inventario.index'))

                        <a href="{{ route('inventario.index') }}"
                           class="btn btn-sm btn-outline-primary">

                            Revisar

                        </a>

                    @endif

                </div>

            @endif


            {{-- SIN ALERTAS --}}

            @if (
                ($metricas['stock_critico'] ?? 0) == 0
                && ($metricas['lotes_por_vencer'] ?? 0) == 0
                && ($metricas['ajustes_pendientes'] ?? 0) == 0
            )

                <div class="attention-item">

                    <div class="attention-content">

                        <div class="attention-indicator"
                             style="background:#198754;">
                        </div>

                        <div class="attention-text">
                            No hay situaciones que requieran atención.
                        </div>

                    </div>

                </div>

            @endif

        </div>


        {{-- =====================================================
            ACCIONES RÁPIDAS
        ====================================================== --}}

        <div class="dashboard-section-title">
            Acciones rápidas
        </div>


        <div class="row g-3">

            @if (Route::has('ventas.create'))

                <div class="col-md-4">

                    <a href="{{ route('ventas.create') }}"
                       class="quick-action">

                        <div class="quick-action-title">
                            Nueva venta
                        </div>

                        <div class="quick-action-description">
                            Registrar una nueva venta.
                        </div>

                    </a>

                </div>

            @endif


            @if (Route::has('lotes.create'))

                <div class="col-md-4">

                    <a href="{{ route('lotes.create') }}"
                       class="quick-action">

                        <div class="quick-action-title">
                            Registrar lote
                        </div>

                        <div class="quick-action-description">
                            Incorporar nuevas existencias.
                        </div>

                    </a>

                </div>

            @endif


            @if (Route::has('inventario.index'))

                <div class="col-md-4">

                    <a href="{{ route('inventario.index') }}"
                       class="quick-action">

                        <div class="quick-action-title">
                            Ver inventario
                        </div>

                        <div class="quick-action-description">
                            Consultar stock, lotes y ubicaciones.
                        </div>

                    </a>

                </div>

            @endif

        </div>

    @endif

</div>

@endsection