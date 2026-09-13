@extends('layouts.app')

@section('title', 'Detalle de producto')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-uppercase text-primary small fw-semibold mb-1">Catálogo</p><h1 class="h3 mb-0">{{ $producto->nombre }}</h1></div><div><a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">Editar</a> <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">Volver</a></div></div>
    <div class="row g-3">
        <div class="col-lg-5"><div class="card border-0 shadow-sm h-100"><div class="card-body"><h2 class="h5">Información</h2><dl class="row mb-0"><dt class="col-5">Código</dt><dd class="col-7">{{ $producto->codigo_interno }}</dd><dt class="col-5">Categoría</dt><dd class="col-7">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</dd><dt class="col-5">Laboratorio</dt><dd class="col-7">{{ $producto->laboratorio?->nombre ?? 'Sin laboratorio' }}</dd><dt class="col-5">Principio activo</dt><dd class="col-7">{{ $producto->principio_activo ?: 'No informado' }}</dd><dt class="col-5">Receta</dt><dd class="col-7">{{ ucfirst($producto->tipo_receta) }}</dd></dl></div></div></div>
        <div class="col-lg-7"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between"><h2 class="h5">Stock por ubicación y lote</h2><span class="fs-5 fw-semibold">Total: {{ $stock }}</span></div><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Lote</th><th>Ubicación</th><th>Vencimiento</th><th>Cantidad</th></tr></thead><tbody>@forelse ($producto->stock as $registro)<tr><td>{{ $registro->lote?->numero_lote ?? 'Sin lote' }}</td><td>{{ $registro->ubicacion?->nombre ?? 'Sin ubicación' }}</td><td>{{ $registro->lote?->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</td><td>{{ $registro->cantidad }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">Sin stock registrado.</td></tr>@endforelse</tbody></table></div>@if ($estaCritico)<div class="alert alert-danger mb-0">El producto se encuentra en stock crítico.</div>@elseif ($estaBajo)<div class="alert alert-warning mb-0">El producto se encuentra bajo el stock mínimo.</div>@endif</div></div></div>
    </div>
</div>
@endsection
