@extends('layouts.app')

@section('title', 'Nueva venta')

@section('content')
<div class="container-fluid px-0"><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Nueva venta</h1><a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Volver</a></div>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="alert alert-info">Caja abierta: <strong>{{ $aperturaCaja->id }}</strong>. La venta se asociará al usuario autenticado.</div>
<form action="{{ route('ventas.store') }}" method="POST" class="card border-0 shadow-sm"><div class="card-body p-4"><div class="row g-3">
@csrf
<input type="hidden" name="apertura_caja_id" value="{{ $aperturaCaja->id }}">
<div class="col-md-6"><label for="paciente_id" class="form-label">Paciente *</label><select id="paciente_id" name="paciente_id" class="form-select" required><option value="">Selecciona un paciente</option>@foreach ($pacientes as $paciente)<option value="{{ $paciente->id }}" @selected(old('paciente_id') == $paciente->id)>{{ $paciente->nombres }} {{ $paciente->apellidos }} ({{ $paciente->rut }})</option>@endforeach</select></div>
<div class="col-md-6"><label for="convenio_id" class="form-label">Convenio</label><select id="convenio_id" name="convenio_id" class="form-select"><option value="">Sin convenio</option>@foreach ($convenios as $convenio)<option value="{{ $convenio->id }}" @selected(old('convenio_id') == $convenio->id)>{{ $convenio->nombre }}</option>@endforeach</select></div>
<div class="col-12"><h2 class="h5 mt-3">Detalle</h2></div>
<div class="col-md-6"><label for="producto_id" class="form-label">Producto *</label><select id="producto_id" name="detalles[0][producto_id]" class="form-select" required><option value="">Selecciona un producto</option>@foreach ($productos as $producto)<option value="{{ $producto->id }}">{{ $producto->nombre }} ({{ $producto->codigo_interno }})</option>@endforeach</select></div>
<div class="col-md-4"><label for="lote_id" class="form-label">Lote *</label><select id="lote_id" name="detalles[0][lote_id]" class="form-select" required><option value="">Selecciona producto primero</option>@foreach ($productos as $producto)@foreach ($producto->lotes as $lote)<option value="{{ $lote->id }}" data-producto="{{ $producto->id }}">{{ $lote->numero_lote }} - vence {{ $lote->fecha_vencimiento->format('d/m/Y') }}</option>@endforeach @endforeach</select></div>
<div class="col-md-2"><label for="cantidad" class="form-label">Cantidad *</label><input id="cantidad" type="number" min="1" name="detalles[0][cantidad]" value="{{ old('detalles.0.cantidad', 1) }}" class="form-control" required></div>
<div class="col-md-2"><label for="descuento_pct" class="form-label">Descuento %</label><input id="descuento_pct" type="number" min="0" max="100" step="0.01" name="detalles[0][descuento_pct]" value="{{ old('detalles.0.descuento_pct', 0) }}" class="form-control"></div>
</div></div><div class="card-footer bg-white border-0 p-4 pt-0"><button class="btn btn-primary">Registrar venta</button><a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a></div></form></div>
<script>document.getElementById('producto_id').addEventListener('change', function () { const productId = this.value; const lotSelect = document.getElementById('lote_id'); lotSelect.value = ''; Array.from(lotSelect.options).forEach(option => { option.hidden = option.value && option.dataset.producto !== productId; }); });</script>
@endsection
