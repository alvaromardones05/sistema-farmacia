@extends('layouts.app')

@section('title', 'Solicitar canje')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Inventario · Canjes</p>
            <h1 class="h3 mb-0">Solicitar canje</h1>
        </div>
        <a href="{{ route('inventario.proximos-vencer', ['dias' => $dias]) }}" class="btn btn-outline-secondary">Volver a vencimientos</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <section class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Producto y lote</h2>
                    <dl class="row mb-0">
                        <dt class="col-5">Producto</dt><dd class="col-7">{{ $producto->nombre }}</dd>
                        <dt class="col-5">Lote</dt><dd class="col-7">{{ $lote->numero_lote }}</dd>
                        <dt class="col-5">Vencimiento</dt><dd class="col-7">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</dd>
                        <dt class="col-5">Almacenamiento</dt><dd class="col-7">{{ ucfirst($producto->condicion_almacenamiento) }}</dd>
                    </dl>
                </div>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Datos de la solicitud</h2>

                    <form method="POST" action="{{ route('inventario.canjes.store', $lote) }}">
                        @csrf
                        <input type="hidden" name="dias" value="{{ $dias }}">

                        <div class="mb-3">
                            <label for="ubicacion_origen_id" class="form-label">Ubicación de origen</label>
                            <select name="ubicacion_origen_id" id="ubicacion_origen_id" class="form-select" required>
                                <option value="">Selecciona una ubicación</option>
                                @foreach ($stocks as $stock)
                                    <option value="{{ $stock->ubicacion_id }}" data-max="{{ $stock->cantidad }}">
                                        {{ $stock->ubicacion->nombre }} (Disponible: {{ $stock->cantidad }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad_solicitada" class="form-label">Cantidad solicitada</label>
                            <input type="number" class="form-control" id="cantidad_solicitada" name="cantidad_solicitada" min="1" value="{{ old('cantidad_solicitada', 1) }}" required>
                            <div class="form-text" id="max_hint"></div>
                        </div>

                        <div class="mb-3">
                            <label for="motivo_solicitud" class="form-label">Motivo</label>
                            <textarea class="form-control" id="motivo_solicitud" name="motivo_solicitud" rows="2" maxlength="2000" required>{{ old('motivo_solicitud') }}</textarea>
                        </div>

                        <input type="hidden" name="idempotency_token" value="{{ \Illuminate\Support\Str::uuid() }}">

                        <button type="submit" class="btn btn-primary">Solicitar canje</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
    document.getElementById('ubicacion_origen_id').addEventListener('change', function() {
        const max = this.options[this.selectedIndex].dataset.max;
        const inputCantidad = document.getElementById('cantidad_solicitada');
        const hint = document.getElementById('max_hint');

        if (max) {
            inputCantidad.max = max;
            hint.textContent = `Máximo disponible: ${max} unidades`;
            if (parseInt(inputCantidad.value) > max) {
                inputCantidad.value = max;
            }
        } else {
            inputCantidad.removeAttribute('max');
            hint.textContent = '';
        }
    });
</script>
@endsection
