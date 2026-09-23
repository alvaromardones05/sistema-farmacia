@extends('layouts.app')

@section('title', 'Trasladar stock')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Trasladar stock</h1>

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

    <form action="{{ route('inventario.transferir') }}" method="POST" class="card border-0 shadow-sm">

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
                    <label for="ubicacion_origen_id" class="form-label">
                        Ubicación de origen *
                    </label>

                    <select id="ubicacion_origen_id"
                            name="ubicacion_origen_id"
                            class="form-select"
                            required>

                        <option value="">Seleccione la ubicación de origen</option>

                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}"
                                @selected(old('ubicacion_origen_id') == $ubicacion->id)>
                                {{ $ubicacion->nombre }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-6">
                    <label for="ubicacion_destino_id" class="form-label">
                        Ubicación de destino *
                    </label>

                    <select id="ubicacion_destino_id"
                            name="ubicacion_destino_id"
                            class="form-select"
                            required>

                        <option value="">Seleccione la ubicación de destino</option>

                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}"
                                @selected(old('ubicacion_destino_id') == $ubicacion->id)>
                                {{ $ubicacion->nombre }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="col-md-4">
                    <label for="cantidad" class="form-label">
                        Cantidad *
                    </label>

                    <input id="cantidad"
                           type="number"
                           name="cantidad"
                           min="1"
                           value="{{ old('cantidad') }}"
                           class="form-control"
                           required>
                </div>

                <div class="col-12">
                    <label for="motivo" class="form-label">
                        Motivo
                    </label>

                    <textarea id="motivo"
                              name="motivo"
                              rows="3"
                              class="form-control"
                              placeholder="Indique el motivo del traslado">{{ old('motivo') }}</textarea>
                </div>

            </div>

        </div>

        <div class="card-footer bg-white border-0 p-4 pt-0">

            <button type="submit" class="btn btn-primary">
                Trasladar stock
            </button>

            <a href="{{ route('inventario.index') }}"
               class="btn btn-outline-secondary ms-2">
                Cancelar
            </a>

        </div>

    </form>

</div>

@endsection