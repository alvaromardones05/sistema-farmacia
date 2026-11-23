@extends('layouts.app')

@section('title', 'Alertas de stock')

@section('content')
@include('inventario._styles')

@php
    $totalAlertas = $stockCritico->count() + $stockBajo->count() + $porVencer->count() + $vencidos->count();

    // Usa precio_base (campo real del modelo Producto)
    $valorLote      = fn ($l) => (float) ($l->stock_comercial ?? 0) * (float) ($l->producto?->precio_base ?? 0);
    $valorVencido   = $vencidos->sum($valorLote);
    $valorPorVencer = $porVencer->sum($valorLote);

    $kpis = [
        ['id' => 'critico',   'titulo' => 'Stock crítico', 'n' => $stockCritico->count(), 'color' => 'danger',  'icono' => 'bi-exclamation-triangle'],
        ['id' => 'bajo',      'titulo' => 'Stock bajo',    'n' => $stockBajo->count(),    'color' => 'warning', 'icono' => 'bi-exclamation-circle'],
        ['id' => 'porvencer', 'titulo' => 'Por vencer',    'n' => $porVencer->count(),    'color' => 'info',    'icono' => 'bi-clock'],
        ['id' => 'vencidos',  'titulo' => 'Vencidos',      'n' => $vencidos->count(),     'color' => 'dark',    'icono' => 'bi-x-octagon'],
    ];

    $tabInicial = collect($kpis)->firstWhere(fn ($k) => $k['n'] > 0)['id'] ?? 'critico';

    // Helper para búsqueda insensible a tildes
    $normalizar = fn ($texto) => \Illuminate\Support\Str::ascii(\Illuminate\Support\Str::lower($texto ?? ''));
@endphp

<div class="container-fluid px-0 inventario-modulo">

    {{-- HEADER CON SELECTOR DE VENTANA --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario</p>
            <h1 class="h3 mb-0">Alertas de stock</h1>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <label for="ventana-dias" class="text-muted small mb-0">Ventana de vencimiento:</label>
                <select id="ventana-dias"
                        class="form-select form-select-sm"
                        style="width: auto;"
                        onchange="window.location.href = '{{ route('inventario.alertas') }}?dias=' + this.value">
                    @foreach ($diasPermitidos as $d)
                        <option value="{{ $d }}" @selected($d === $diasVencimiento)>{{ $d }} días</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    {{-- KPI CARDS (SELECTOR PRINCIPAL) --}}
    <div class="row g-3 mb-4">
        @foreach ($kpis as $k)
            <div class="col-6 col-lg-3">
                <button type="button"
                        class="card border-0 shadow-sm w-100 text-start h-100 kpi-alerta {{ $k['n'] === 0 ? 'kpi-disabled' : '' }}"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-{{ $k['id'] }}"
                        @if($k['n'] === 0) disabled @endif>
                    <div class="card-body border-start border-4 border-{{ $k['color'] }}">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="small text-muted">{{ $k['titulo'] }}</div>
                            <i class="bi {{ $k['icono'] }} text-{{ $k['color'] }}"></i>
                        </div>
                        <div class="fs-2 fw-semibold lh-1 mt-1">{{ $k['n'] }}</div>
                    </div>
                </button>
            </div>
        @endforeach
    </div>

    @if ($valorVencido > 0 || $valorPorVencer > 0)
        <div class="alert alert-light border d-flex flex-wrap gap-4 mb-4">
            <div>
                <span class="text-muted small d-block">Valor ya vencido</span>
                <strong class="text-danger">${{ number_format($valorVencido, 0, ',', '.') }}</strong>
            </div>
            <div>
                <span class="text-muted small d-block">Valor por vencer (≤{{ $diasVencimiento }} días)</span>
                <strong class="text-warning">${{ number_format($valorPorVencer, 0, ',', '.') }}</strong>
            </div>
        </div>
    @endif

    @if ($totalAlertas === 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="fs-1 text-success" aria-hidden="true">✔</div>
                <h2 class="h5 mt-2">Todo en orden</h2>
                <p class="text-muted mb-0">No hay productos bajo el umbral ni lotes por vencer en los próximos {{ $diasVencimiento }} días.</p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <span class="text-muted small">
                        Mostrando: <strong id="tab-activa-label">{{ collect($kpis)->firstWhere('id', $tabInicial)['titulo'] }}</strong>
                    </span>
                    <input type="search" id="filtro-alertas" class="form-control form-control-sm"
                           style="max-width: 280px"
                           placeholder="Buscar en esta pestaña..."
                           aria-label="Buscar en alertas">
                </div>
            </div>

            <div class="tab-content">

                {{-- STOCK CRÍTICO --}}
                <div class="tab-pane fade {{ $tabInicial === 'critico' ? 'show active' : '' }}" id="tab-critico" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock utilizable</th>
                                    <th>Umbral crítico</th>
                                    <th>Faltante para umbral</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockCritico->sortBy(fn ($p) => (int) $p->stock_utilizable / max(1, (int) $p->stock_critico)) as $producto)
                                    @php
                                        $stock    = (int) $producto->stock_utilizable;
                                        $limite   = (int) $producto->stock_critico;
                                        $pct      = $limite > 0 ? min(100, round($stock / $limite * 100)) : 0;
                                        $faltante = max(0, $limite - $stock);
                                    @endphp
                                    <tr data-buscar="{{ $normalizar($producto->nombre.' '.$producto->codigo_interno) }}">
                                        <td>
                                            <div class="fw-semibold">{{ $producto->nombre }}</div>
                                            <div class="small text-muted">{{ $producto->codigo_interno }}</div>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-danger">{{ $stock }}</span>
                                            <div class="progress alerta-progreso mt-1" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-danger" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </td>
                                        <td>{{ $limite }}</td>
                                        <td>
                                            @if ($faltante > 0)
                                                <span class="text-muted small">Faltan {{ $faltante }} uds</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin productos en stock crítico.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- STOCK BAJO --}}
                <div class="tab-pane fade {{ $tabInicial === 'bajo' ? 'show active' : '' }}" id="tab-bajo" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock utilizable</th>
                                    <th>Umbral mínimo</th>
                                    <th>Faltante para umbral</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockBajo->sortBy(fn ($p) => (int) $p->stock_utilizable / max(1, (int) $p->stock_minimo)) as $producto)
                                    @php
                                        $stock    = (int) $producto->stock_utilizable;
                                        $limite   = (int) $producto->stock_minimo;
                                        $pct      = $limite > 0 ? min(100, round($stock / $limite * 100)) : 0;
                                        $faltante = max(0, $limite - $stock);
                                    @endphp
                                    <tr data-buscar="{{ $normalizar($producto->nombre.' '.$producto->codigo_interno) }}">
                                        <td>
                                            <div class="fw-semibold">{{ $producto->nombre }}</div>
                                            <div class="small text-muted">{{ $producto->codigo_interno }}</div>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-warning">{{ $stock }}</span>
                                            <div class="progress alerta-progreso mt-1" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </td>
                                        <td>{{ $limite }}</td>
                                        <td>
                                            @if ($faltante > 0)
                                                <span class="text-muted small">Faltan {{ $faltante }} uds</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin productos con stock bajo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- POR VENCER --}}
                <div class="tab-pane fade {{ $tabInicial === 'porvencer' ? 'show active' : '' }}" id="tab-porvencer" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th>Vencimiento</th>
                                    <th>Plazo</th>
                                    <th>Stock comercial</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($porVencer->sortBy('fecha_vencimiento') as $lote)
                                    @php
                                        $dias = (int) now()->startOfDay()->diffInDays($lote->fecha_vencimiento->startOfDay(), false);
                                        if ($dias < 0)       { $clase = 'dark';    $texto = 'Vencido hace '.abs($dias).' '.(abs($dias) === 1 ? 'día' : 'días'); }
                                        elseif ($dias === 0) { $clase = 'danger';  $texto = 'Vence hoy'; }
                                        elseif ($dias <= 7)  { $clase = 'danger';  $texto = $dias.' '.($dias === 1 ? 'día' : 'días'); }
                                        elseif ($dias <= 15) { $clase = 'warning'; $texto = $dias.' días'; }
                                        else                 { $clase = 'info';    $texto = $dias.' días'; }
                                        $valor = (float) $lote->stock_comercial * (float) ($lote->producto?->precio_base ?? 0);
                                    @endphp
                                    <tr data-buscar="{{ $normalizar(($lote->producto?->nombre ?? '').' '.$lote->numero_lote) }}">
                                        <td class="fw-semibold">{{ $lote->producto?->nombre ?? '—' }}</td>
                                        <td class="font-monospace small">{{ $lote->numero_lote }}</td>
                                        <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td><span class="badge text-bg-{{ $clase }}">{{ $texto }}</span></td>
                                        <td>{{ (int) $lote->stock_comercial }}</td>
                                        <td>{{ $valor > 0 ? '$'.number_format($valor, 0, ',', '.') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Sin lotes próximos a vencer en los próximos {{ $diasVencimiento }} días.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- VENCIDOS --}}
                <div class="tab-pane fade {{ $tabInicial === 'vencidos' ? 'show active' : '' }}" id="tab-vencidos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th>Vencimiento</th>
                                    <th>Plazo</th>
                                    <th>Stock comercial</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vencidos->sortBy('fecha_vencimiento') as $lote)
                                    @php
                                        $dias = (int) now()->startOfDay()->diffInDays($lote->fecha_vencimiento->startOfDay(), false);
                                        if ($dias < 0)       { $clase = 'dark';   $texto = 'Vencido hace '.abs($dias).' '.(abs($dias) === 1 ? 'día' : 'días'); }
                                        elseif ($dias === 0) { $clase = 'danger'; $texto = 'Vence hoy'; }
                                        else                 { $clase = 'danger'; $texto = $dias.' días'; }
                                        $valor = (float) $lote->stock_comercial * (float) ($lote->producto?->precio_base ?? 0);
                                    @endphp
                                    <tr data-buscar="{{ $normalizar(($lote->producto?->nombre ?? '').' '.$lote->numero_lote) }}">
                                        <td class="fw-semibold">{{ $lote->producto?->nombre ?? '—' }}</td>
                                        <td class="font-monospace small">{{ $lote->numero_lote }}</td>
                                        <td>{{ $lote->fecha_vencimiento->format('d/m/Y') }}</td>
                                        <td><span class="badge text-bg-{{ $clase }}">{{ $texto }}</span></td>
                                        <td>{{ (int) $lote->stock_comercial }}</td>
                                        <td>{{ $valor > 0 ? '$'.number_format($valor, 0, ',', '.') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Sin lotes vencidos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .kpi-alerta { cursor: pointer; background: #fff; transition: all .15s; }
    .kpi-alerta:hover:not(.kpi-disabled),
    .kpi-alerta:focus-visible:not(.kpi-disabled) {
        box-shadow: 0 .25rem .75rem rgba(0,0,0,.12) !important;
        transform: translateY(-1px);
    }
    .kpi-alerta.active {
        box-shadow: 0 0 0 3px var(--primary-soft), 0 .25rem .75rem rgba(0,0,0,.12) !important;
    }
    .kpi-alerta.active .card-body { background: var(--primary-soft); }
    .kpi-disabled { opacity: .55; cursor: not-allowed; }
    .alerta-progreso { height: 6px; min-width: 90px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kpiCards = document.querySelectorAll('.kpi-alerta');
    const inputFiltro = document.getElementById('filtro-alertas');
    const labelTab = document.getElementById('tab-activa-label');

    // Normaliza texto insensible a tildes/mayúsculas
    const normalizar = (texto) => (texto || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    // Marcar la KPI activa inicial
    const tabActivaInicial = document.querySelector('.tab-pane.active');
    if (tabActivaInicial) {
        const kpiActiva = document.querySelector(`.kpi-alerta[data-bs-target="#${tabActivaInicial.id}"]`);
        if (kpiActiva) kpiActiva.classList.add('active');
    }

    // Sincronizar estado visual al cambiar de tab
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function () {
            kpiCards.forEach(c => c.classList.remove('active'));
            const target = this.getAttribute('data-bs-target');
            const kpi = document.querySelector(`.kpi-alerta[data-bs-target="${target}"]`);
            if (kpi) kpi.classList.add('active');

            if (labelTab && kpi) {
                const titulo = kpi.querySelector('.small.text-muted')?.textContent?.trim() ?? '';
                labelTab.textContent = titulo;
            }

            // Resetear filtro al cambiar de pestaña
            if (inputFiltro) {
                inputFiltro.value = '';
                filtrarTabActiva('');
            }
        });
    });

    // Filtro en vivo
    if (inputFiltro) {
        inputFiltro.addEventListener('input', function () {
            filtrarTabActiva(this.value);
        });
    }

    function filtrarTabActiva(query) {
        const q = normalizar(query);
        const tabActiva = document.querySelector('.tab-pane.active');
        if (!tabActiva) return;

        tabActiva.querySelectorAll('tbody tr[data-buscar]').forEach(tr => {
            const contenido = tr.dataset.buscar ?? '';
            tr.classList.toggle('d-none', q !== '' && !contenido.includes(q));
        });
    }
});
</script>
@endsection
