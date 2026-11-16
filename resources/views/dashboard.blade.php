@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<style>
:root {
    --bg:            var(--page-bg, #f6f8fb);
    --surface:       var(--surface, #ffffff);
    --border:        var(--border, #e5e9f0);
    --border-strong: var(--border-2, #d3dae5);
    --text:          var(--text, #0f172a);
    --text-muted:    var(--text-muted, #64748b);
    --text-soft:     var(--text-soft, #94a3b8);
    --primary:       var(--primary, #4f46e5);
    --primary-soft:  var(--primary-soft, #eef2ff);
    --success:       var(--success, #059669);
    --success-soft:  var(--success-soft, #ecfdf5);
    --warning:       var(--warning, #d97706);
    --warning-soft:  var(--warning-soft, #fffbeb);
    --danger:        var(--danger, #dc2626);
    --danger-soft:   var(--danger-soft, #fef2f2);
    --info:          var(--info, #0284c7);
    --info-soft:     var(--info-soft, #f0f9ff);
    --violet:        #7c3aed;
    --violet-soft:   #f3e8ff;
    --radius-sm: 8px; --radius: 12px; --radius-lg: 16px;
    --shadow-sm: 0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03);
    --shadow:    0 4px 12px rgba(15,23,42,.06), 0 2px 4px rgba(15,23,42,.04);
}
* { -webkit-font-smoothing: antialiased; box-sizing: border-box; }
.app { max-width: 1480px; margin: 0 auto; }

.page-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
.page-header .crumb { font-size: .72rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--primary); margin-bottom: .35rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 .25rem; letter-spacing: -.015em; color: var(--text); }
.page-header .subtitle { color: var(--text-muted); margin: 0; font-size: .9rem; }
.header-actions { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }

.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.kpi { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.1rem; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: .35rem; text-decoration: none; color: inherit; transition: all .15s ease; cursor: pointer; position: relative; }
.kpi:hover { transform: translateY(-1px); box-shadow: var(--shadow); border-color: var(--border-strong); }
.kpi-head { display: flex; align-items: center; justify-content: space-between; }
.kpi-label { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); }
.kpi-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.kpi-value { font-size: 1.75rem; font-weight: 700; line-height: 1.1; letter-spacing: -.02em; display: flex; align-items: baseline; gap: .3rem; color: var(--text); }
.kpi-value small { font-size: .72rem; font-weight: 500; color: var(--text-soft); }

.main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
@media (max-width: 1100px) { .main-grid { grid-template-columns: 1fr; } }
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); gap: .5rem; flex-wrap: wrap; }
.card-header h3 { font-size: .95rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: .5rem; color: var(--text); }
.card-body { padding: 1.25rem; }
.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1.25rem; }
@media (max-width: 760px) { .charts-row { grid-template-columns: 1fr; } }
.chart-container { position: relative; height: 240px; padding: .5rem 0 0; }
.chart-container.tall { height: 300px; }
.chart-container.short { height: 220px; }

.actions-list { display: flex; flex-direction: column; }
.action-item { display: flex; align-items: flex-start; gap: .75rem; padding: .95rem 1.15rem; border-bottom: 1px solid var(--border); transition: background .12s; cursor: pointer; text-decoration: none; color: inherit; }
.action-item:last-child { border-bottom: none; }
.action-item:hover { background: var(--bg); }
.action-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.action-body { flex: 1; min-width: 0; }
.action-title { font-size: .85rem; font-weight: 600; margin: 0 0 .15rem; display: flex; align-items: center; gap: .4rem; color: var(--text); }
.action-title .count { background: var(--bg); color: var(--text); font-size: .7rem; font-weight: 700; padding: .05rem .45rem; border-radius: 4px; }
.action-desc { font-size: .78rem; color: var(--text-muted); margin: 0; line-height: 1.35; }
.action-arrow { color: var(--text-soft); font-size: .9rem; align-self: center; transition: transform .15s; }
.action-item:hover .action-arrow { transform: translateX(3px); color: var(--primary); }
.priority-tag { font-size: .62rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; padding: .1rem .4rem; border-radius: 4px; }
.priority-tag.alta  { background: var(--danger-soft); color: var(--danger); }
.priority-tag.media { background: var(--warning-soft); color: var(--warning); }

.timeline { padding: 1.15rem; display: flex; flex-direction: column; gap: 1rem; max-height: 340px; overflow-y: auto; }
.tl-item { display: flex; gap: .75rem; position: relative; }
.tl-item:not(:last-child)::before { content: ''; position: absolute; left: 14px; top: 32px; bottom: -18px; width: 2px; background: var(--border); }
.tl-dot { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0; background: var(--surface); border: 2px solid var(--border); position: relative; z-index: 1; }
.tl-dot.exit    { border-color: var(--warning); color: var(--warning); }
.tl-dot.transfer{ border-color: var(--info); color: var(--info); }
.tl-dot.entry   { border-color: var(--success); color: var(--success); }
.tl-body { flex: 1; min-width: 0; padding-top: .1rem; }
.tl-text { font-size: .82rem; line-height: 1.35; margin: 0 0 .2rem; color: var(--text); }
.tl-meta { font-size: .72rem; color: var(--text-muted); }

.bottom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; }
@media (max-width: 1100px) { .bottom-grid { grid-template-columns: 1fr; } }
table.simple-table { width: 100%; border-collapse: collapse; }
.simple-table thead th { padding: .7rem 1.1rem; text-align: left; font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); background: var(--bg); border-bottom: 1px solid var(--border); white-space: nowrap; }
.simple-table tbody td { padding: .8rem 1.1rem; border-bottom: 1px solid var(--border); font-size: .82rem; color: var(--text); }
.simple-table tbody tr:hover { background: var(--bg); }
.venc-badge { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .55rem; border-radius: 5px; font-size: .72rem; font-weight: 600; }
.venc-badge.urgent  { background: var(--danger-soft); color: var(--danger); }
.venc-badge.warn    { background: var(--warning-soft); color: var(--warning); }
.venc-badge.ok      { background: var(--success-soft); color: var(--success); }

.alerts-list { padding: 1rem 1.15rem; display: flex; flex-direction: column; gap: .65rem; }
.alert-item { display: flex; align-items: flex-start; gap: .6rem; padding: .65rem .8rem; border-radius: 8px; font-size: .8rem; line-height: 1.35; }
.alert-item.danger  { background: var(--danger-soft); color: var(--danger); border-left: 3px solid var(--danger); }
.alert-item.warning { background: var(--warning-soft); color: var(--warning); border-left: 3px solid var(--warning); }
.btn-link-sm { background: transparent; border: none; color: var(--primary); font-size: .78rem; font-weight: 600; cursor: pointer; padding: 0; text-decoration: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<div class="app">
    <header class="page-header">
        <div>
            <h1>Resumen general</h1>
            <p class="subtitle">Última actualización: {{ now()->format('H:i') }}</p>
        </div>
        <div class="header-actions">
            @role('Administrador')
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                    <i class="bi bi-people me-1"></i> Gestionar usuarios
                </a>
            @endrole
        </div>
    </header>

    <div class="kpi-grid">
        @if ($puedeVerVentas)
        <a class="kpi" href="{{ $puedeAccederVentas ? route('ventas.index') : '#' }}">
            <div class="kpi-head">
                <span class="kpi-label">Ventas hoy</span>
                <div class="kpi-icon" style="background:var(--success-soft);color:var(--success);"><i class="bi bi-cash-coin"></i></div>
            </div>
            <div class="kpi-value">${{ number_format($metricas['total_ventas'], 0, ',', '.') }}</div>
            <span style="font-size: .75rem; color: var(--text-muted);">{{ $metricas['cantidad_ventas'] }} ventas completadas</span>
        </a>

        <a class="kpi" href="{{ $puedeAccederVentas ? route('ventas.index') : '#' }}">
            <div class="kpi-head">
                <span class="kpi-label">Ventas del mes</span>
                <div class="kpi-icon" style="background:var(--primary-soft);color:var(--primary);"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
            <div class="kpi-value">${{ number_format($metricas['ventas_mes'] ?? 0, 0, ',', '.') }}</div>
            <span style="font-size: .75rem; color: var(--text-muted);">Mes en curso</span>
        </a>
        @endif

        @if ($puedeVerInventario)
        <a class="kpi" href="{{ route('inventario.index') }}">
            <div class="kpi-head">
                <span class="kpi-label">Stock crítico</span>
                <div class="kpi-icon" style="background:var(--danger-soft);color:var(--danger);"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
            <div class="kpi-value" style="color:var(--danger);">{{ $metricas['stock_critico'] }}<small>productos</small></div>
            <span style="font-size: .75rem; color: var(--text-muted);">Requieren reposición</span>
        </a>

        <a class="kpi" href="{{ route('inventario.proximos-vencer', ['dias' => 30]) }}">
            <div class="kpi-head">
                <span class="kpi-label">Próximos a vencer</span>
                <div class="kpi-icon" style="background:var(--warning-soft);color:var(--warning);"><i class="bi bi-calendar-x"></i></div>
            </div>
            <div class="kpi-value" style="color:var(--warning);">{{ $metricas['lotes_por_vencer'] }}<small>lotes</small></div>
            <span style="font-size: .75rem; color: var(--text-muted);">En los próximos 30 días</span>
        </a>
        @endif

        @if ($puedeRevisarDescuadres)
        <a class="kpi" href="{{ route('descuadres-controlados.index') }}">
            <div class="kpi-head">
                <span class="kpi-label">Descuadres</span>
                <div class="kpi-icon" style="background:var(--info-soft);color:var(--info);"><i class="bi bi-clipboard-check"></i></div>
            </div>
            <div class="kpi-value">{{ $metricas['descuadres_pendientes'] }}<small>pendientes</small></div>
            <span style="font-size: .75rem; color: var(--text-muted);">Requieren revisión</span>
        </a>
        @endif

        @role('Químico Farmacéutico')
        <a class="kpi" href="{{ \Route::has('recetas.pendientes') ? route('recetas.pendientes') : '#' }}">
            <div class="kpi-head">
                <span class="kpi-label">Recetas pendientes</span>
                <div class="kpi-icon" style="background:var(--violet-soft);color:var(--violet);"><i class="bi bi-file-medical"></i></div>
            </div>
            <div class="kpi-value">{{ $metricas['recetas_pendientes'] ?? 0 }}<small>controlados</small></div>
            <span style="font-size: .75rem; color: var(--text-muted);">Requieren aprobación</span>
        </a>
        @endrole
    </div>

    <div class="main-grid">
        <div>
            <div class="card">
                <div class="card-header"><h3><i class="bi bi-graph-up text-primary"></i> Ventas últimos 14 días</h3></div>
                <div class="card-body"><div class="chart-container tall"><canvas id="salesChart"></canvas></div></div>
            </div>
            <div class="charts-row">
                <div class="card">
                    <div class="card-header"><h3><i class="bi bi-pie-chart text-info"></i> Distribución de stock</h3></div>
                    <div class="card-body"><div class="chart-container short"><canvas id="stockChart"></canvas></div></div>
                </div>
                <div class="card">
                    <div class="card-header"><h3><i class="bi bi-bar-chart text-success"></i> Top productos del mes</h3></div>
                    <div class="card-body"><div class="chart-container short"><canvas id="topProductsChart"></canvas></div></div>
                </div>
            </div>
        </div>

        <aside>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-clock-history text-info"></i> Actividad reciente</h3>
                </div>
                <div class="timeline">
                    <div class="tl-item">
                        <div class="tl-dot exit"><i class="bi bi-cart-check"></i></div>
                        <div class="tl-body">
                            <p class="tl-text"><strong>Venta #00124</strong> · 5 productos · $12.400</p>
                            <div class="tl-meta">por M. González <span class="sep">·</span> hace 5 min</div>
                        </div>
                    </div>
                    <div class="tl-item">
                        <div class="tl-dot transfer"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="tl-body">
                            <p class="tl-text"><strong>Traslado</strong> · 100 uds de Bodega → Vitrina</p>
                            <div class="tl-meta">por J. Pérez <span class="sep">·</span> hace 12 min</div>
                        </div>
                    </div>
                    <div class="tl-item">
                        <div class="tl-dot entry"><i class="bi bi-box-arrow-in-down"></i></div>
                        <div class="tl-body">
                            <p class="tl-text"><strong>Recepción aprobada</strong> · 200 uds ingresadas a stock</p>
                            <div class="tl-meta">por J. Pérez <span class="sep">·</span> hace 40 min</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="bottom-grid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="bi bi-calendar-x text-warning"></i> Próximos vencimientos</h3>
                        <a href="{{ route('inventario.proximos-vencer') }}" class="btn-link-sm">Ver todos</a>
                    </div>
                    <table class="simple-table">
                        <thead>
                            <tr>
                                <th>Lote / Serie</th>
                                <th>Producto</th>
                                <th class="text-end">Stock Comercial</th>
                                <th>Vence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lotesPorVencer as $lote)
                                @php
                                    $fechaVenc = \Carbon\Carbon::parse($lote->fecha_vencimiento)->startOfDay();
                                    $dias = now()->startOfDay()->diffInDays($fechaVenc, false);
                                    $badgeClass = $dias <= 7 ? 'urgent' : ($dias <= 30 ? 'warn' : 'ok');
                                    $badgeIcon = $dias <= 7 ? 'bi-exclamation-circle' : ($dias <= 30 ? 'bi-clock' : 'bi-check-circle');
                                @endphp
                                <tr>
                                    <td><strong style="font-family:monospace;">{{ $lote->numero_lote }}</strong></td>
                                    <td>{{ $lote->producto?->nombre ?? 'Desconocido' }}</td>
                                    <td class="text-end">{{ (int) $lote->stock_comercial }} uds</td>
                                    <td>
                                        <span class="venc-badge {{ $badgeClass }}">
                                            <i class="bi {{ $badgeIcon }}"></i>
                                            {{ $dias < 0 ? 'Vencido' : ($dias === 0 ? 'Vence hoy' : $dias . ' días') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No hay lotes próximos a vencer.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3><i class="bi bi-bell text-info"></i> Alertas del sistema</h3></div>
            <div class="alerts-list">
                <div class="alert-item danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><strong>Stock crítico detectado.</strong> Revisa el inventario para reabastecer.</div>
                </div>
                <div class="alert-item warning">
                    <i class="bi bi-clock-fill"></i>
                    <div><strong>Lotes próximos a vencer.</strong> Prioriza su salida en vitrina o gestiona canje.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // CORRECCIÓN AQUÍ: Bootstrap pone el atributo en <html> (documentElement), no en <body>.
    // Validamos ambos por si acaso.
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.body.getAttribute('data-bs-theme') === 'dark';

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = isDarkMode ? '#8b949e' : '#64748b';
    Chart.defaults.font.size = 11;

    const gridColor = isDarkMode ? '#363740' : '#f1f5f9';
    const tooltipBg = isDarkMode ? '#1c1d21' : '#0f172a';

    (function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(47, 129, 247, 0.25)');
        gradient.addColorStop(1, 'rgba(47, 129, 247, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartVentasLabels),
                datasets: [{
                    label: 'Ventas',
                    data: @json($chartVentasData),
                    borderColor: '#2f81f7',
                    backgroundColor: gradient,
                    borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: tooltipBg, padding: 10, cornerRadius: 8, displayColors: false,
                        callbacks: { label: ctx => '$' + ctx.parsed.y.toLocaleString('es-CL') }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: isDarkMode ? '#8b949e' : '#94a3b8' } },
                    y: { grid: { color: gridColor, drawBorder: false }, border: { display: false }, ticks: { color: isDarkMode ? '#8b949e' : '#94a3b8' } }
                }
            }
        });
    })();

    (function() {
        const ctx = document.getElementById('stockChart').getContext('2d');
        const colores = ['#2ea043', '#38bdf8', '#2f81f7', '#58a6ff', '#da3633', '#bb8009'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chartStockLabels),
                datasets: [{
                    data: @json($chartStockData),
                    backgroundColor: colores,
                    borderWidth: 3,
                    borderColor: isDarkMode ? '#232428' : '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, usePointStyle: true, color: isDarkMode ? '#c9d1d9' : '#475569' } },
                    tooltip: { backgroundColor: tooltipBg, padding: 10, cornerRadius: 8 }
                }
            }
        });
    })();

    (function() {
        const ctx = document.getElementById('topProductsChart').getContext('2d');

        // Obtenemos los datos reales inyectados desde Laravel
        const labelsTop = @json($topProductosLabels);
        const dataTop = @json($topProductosData);

        // Si no hay ventas en el mes, mostramos un mensaje o dejamos el gráfico vacío
        if (labelsTop.length === 0) {
            document.getElementById('topProductsChart').outerHTML = '<div class="text-center text-muted d-flex align-items-center justify-content-center h-100" style="font-size: 0.9rem;">No hay ventas registradas este mes.</div>';
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{
                    data: dataTop,
                    backgroundColor: '#2f81f7',
                    borderRadius: 6,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: tooltipBg,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' unidades vendidas';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor, drawBorder: false },
                        border: { display: false },
                        ticks: { color: isDarkMode ? '#8b949e' : '#94a3b8', precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: isDarkMode ? '#c9d1d9' : '#475569' }
                    }
                }
            }
        });
    })();
</script>
@endsection
