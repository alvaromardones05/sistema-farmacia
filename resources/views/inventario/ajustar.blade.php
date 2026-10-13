@extends('layouts.app')

@section('title', 'Ajustar stock')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Ajustar stock</h1>

        <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary">
            Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-warning">
            <i class="bi bi-hourglass-split me-2"></i>
            {{ session('info') }}
        </div>
    @endif

    <form action="{{ route('inventario.ajuste') }}" method="POST" class="card border-0 shadow-sm">

        @csrf

        <div class="card-body p-4">

            <div class="row g-3">

                <div class="col-md-8">
                    <label for="producto_id" class="form-label">
                        Producto *
                    </label>

                    <select id="producto_id"
                            name="producto_id"
                            class="form-select"
                            required>

                        <option value="">Seleccione un producto</option>

                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}"
                                @selected(old('producto_id') == $producto->id)>
                                {{ $producto->nombre }}
                                ({{ $producto->codigo_interno }})
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-4">
                    <label for="lote_id" class="form-label">
                        Lote *
                    </label>

                    <select name="lote_id"
                            id="lote_id"
                            class="form-select"
                            required
                            disabled>
                        <option value="">Pawrimero selecciona un producto</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="ubicacion_id" class="form-label">
                        Ubicación *
                    </label>

                    <select id="ubicacion_id"
                            name="ubicacion_id"
                            class="form-select"
                            required>

                        <option value="">Seleccione una ubicación</option>

                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}"
                                @selected(old('ubicacion_id') == $ubicacion->id)>
                                {{ $ubicacion->nombre }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-6">
                    <label for="diferencia" class="form-label">
                        Diferencia de stock *
                    </label>

                    <input id="diferencia"
                           type="number"
                           name="diferencia"
                           value="{{ old('diferencia') }}"
                           class="form-control"
                           required>

                    <div class="form-text">
                        Use un valor positivo para aumentar el stock
                        y un valor negativo para disminuirlo.
                    </div>
                </div>

                <div class="col-12">
                    <label for="motivo" class="form-label">
                        Motivo del ajuste *
                    </label>

                    <textarea id="motivo"
                              name="motivo"
                              rows="3"
                              class="form-control"
                              required
                              placeholder="Indique el motivo del ajuste">{{ old('motivo') }}</textarea>
                </div>

            </div>

        </div>

        <div class="card-footer bg-white border-0 p-4 pt-0">

            <button type="submit" class="btn btn-primary">
                Ajustar stock
            </button>

            <a href="{{ route('inventario.index') }}"
               class="btn btn-outline-secondary ms-2">
                Cancelar
            </a>

        </div>

    </form>

</div>

@php
    $lotesParaJs = $lotes->map(function ($lote) {
        return [
            'id' => $lote->id,
            'producto_id' => $lote->producto_id,
            'numero_lote' => $lote->numero_lote,
        ];
    })->values();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productoSelect = document.getElementById('producto_id');
    const loteSelect = document.getElementById('lote_id');

    const lotes = @json($lotesParaJs);
    const loteAnterior = @json(old('lote_id'));

    function cargarLotes() {
        const productoId = productoSelect.value;

        loteSelect.innerHTML = '';

        if (!productoId) {
            loteSelect.innerHTML =
                '<option value="">Primero selecciona un producto</option>';

            loteSelect.disabled = true;
            return;
        }

        const relacionados = lotes.filter(
            lote => String(lote.producto_id) === String(productoId)
        );

        if (relacionados.length === 0) {
            loteSelect.innerHTML =
                '<option value="">Este producto no tiene lotes activos</option>';

            loteSelect.disabled = true;
            return;
        }

        loteSelect.disabled = false;

        loteSelect.innerHTML =
            '<option value="">Seleccionar lote</option>';

        relacionados.forEach(lote => {
            const option = document.createElement('option');

            option.value = lote.id;
            option.textContent = lote.numero_lote;

            if (String(lote.id) === String(loteAnterior)) {
                option.selected = true;
            }

            loteSelect.appendChild(option);
        });
    }

    productoSelect.addEventListener('change', cargarLotes);

    cargarLotes();
});
</script>

@endsection