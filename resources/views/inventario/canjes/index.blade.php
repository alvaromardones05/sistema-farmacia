@extends('layouts.app')

@section('content')
@php
    // Si tus columnas se llaman distinto, cámbialas solo aquí.
    $nombreProducto = fn($canje) => $canje->producto->nombre;
    $nombreUsuario  = fn($usuario) => $usuario?->name;

    $pestanas = [
        'activos'    => 'En curso',
        'pendiente'  => 'Por autorizar',
        'completado' => 'Completados',
        'rechazado'  => 'Rechazados',
    ];
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Canjes</h1>
            <p class="text-muted mb-0">
                Medicamentos próximos a vencer que se cambian con el distribuidor.
            </p>
        </div>
        @if(Route::has('inventario.proximos-vencer'))
            <a href="{{ route('inventario.proximos-vencer') }}" class="btn btn-primary">
                Buscar lotes para canjear
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <ul class="nav nav-tabs mb-3">
        @foreach($pestanas as $clave => $titulo)
            <li class="nav-item">
                <a class="nav-link {{ $filtro === $clave ? 'active' : '' }}"
                   href="{{ route('inventario.canjes.index', ['estado' => $clave]) }}">
                    {{ $titulo }}
                    <span class="badge bg-secondary ms-1">{{ $conteos[$clave] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
        <li class="nav-item">
            <a class="nav-link {{ $filtro === 'todos' ? 'active' : '' }}"
               href="{{ route('inventario.canjes.index', ['estado' => 'todos']) }}">
                Todos
            </a>
        </li>
    </ul>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Producto</th>
                        <th>Lote original</th>
                        <th class="text-end">Solicitadas</th>
                        <th>Avance</th>
                        <th>Estado</th>
                        <th>Qué toca hacer</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($canjes as $canje)
                        @php
                            $r = $canje->resumen();
                            $avance = $r['solicitada'] > 0
                                ? min(100, round($r['repuesta'] / $r['solicitada'] * 100))
                                : 0;
                        @endphp
                        <tr>
                            <td>#{{ $canje->id }}</td>
                            <td>
                                {{ $nombreProducto($canje) }}
                                <div class="small text-muted">
                                    Pedido el {{ $canje->fecha_solicitud->format('d-m-Y') }} por {{ $nombreUsuario($canje->solicitante) }}
                                </div>
                            </td>
                            <td>
                                {{ $canje->loteOriginal->numero_lote }}
                                <div class="small text-muted">
                                    Vence el {{ $canje->loteOriginal->fecha_vencimiento->format('d-m-Y') }}
                                </div>
                            </td>
                            <td class="text-end">{{ $r['solicitada'] }}</td>
                            <td style="min-width: 160px;">
                                @if(in_array($canje->estado, ['aprobado', 'en_proceso', 'completado'], true))
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ $avance }}%;"></div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Repuestas {{ $r['repuesta'] }} de {{ $r['solicitada'] }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>@include('inventario.canjes.partials.estado', ['estado' => $canje->estado])</td>
                            <td>{{ $canje->textoSiguientePaso() }}</td>
                            <td class="text-end">
                                <a href="{{ route('inventario.canjes.show', $canje) }}"
                                   class="btn btn-sm btn-outline-primary">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                No hay canjes en esta pestaña.
                                Para iniciar uno, entra a Próximos a vencer y usa «Enviar a canje» en el lote.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $canjes->links() }}
    </div>
</div>
@endsection
