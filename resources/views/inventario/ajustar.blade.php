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

                    <select id="lote_id"
                            name="lote_id"
                            class="form-select"
                            required>

                        <option value="">Seleccione un lote</option>

                        @foreach ($lotes as $lote)
                            <option value="{{ $lote->id }}"
                                @selected(old('lote_id') == $lote->id)>
                                {{ $lote->numero_lote }}
                            </option>
                        @endforeach

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

@endsection