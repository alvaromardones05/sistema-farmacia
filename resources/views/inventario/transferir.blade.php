@extends('layouts.app')

@section('title', 'Trasladar stock')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Trasladar stock</h1>

        <a href="{{ route('inventario.index') }}"
           class="btn btn-outline-secondary">
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

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('inventario.transferir') }}"
          method="POST"
          class="card border-0 shadow-sm">

        @csrf

        <div class="card-body p-4">

            <div class="row g-3">

                {{-- PRODUCTO --}}
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

                {{-- LOTE --}}
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
                                    data-producto="{{ $lote->producto_id }}"
                                    @selected(old('lote_id') == $lote->id)>

                                {{ $lote->numero_lote }}

                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- UBICACIÓN ORIGEN --}}
                <div class="col-md-6">
                    <label for="ubicacion_origen_id" class="form-label">
                        Ubicación de origen *
                    </label>

                    <select id="ubicacion_origen_id"
                            name="ubicacion_origen_id"
                            class="form-select"
                            required>

                        <option value="">
                            Seleccione la ubicación de origen
                        </option>

                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}"
                                @selected(old('ubicacion_origen_id') == $ubicacion->id)>

                                {{ $ubicacion->nombre }}

                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- UBICACIÓN DESTINO --}}
                <div class="col-md-6">
                    <label for="ubicacion_destino_id" class="form-label">
                        Ubicación de destino *
                    </label>

                    <select id="ubicacion_destino_id"
                            name="ubicacion_destino_id"
                            class="form-select"
                            required>

                        <option value="">
                            Seleccione la ubicación de destino
                        </option>

                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}"
                                @selected(old('ubicacion_destino_id') == $ubicacion->id)>

                                {{ $ubicacion->nombre }}

                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- CANTIDAD --}}
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

                    <small class="text-muted">
                        Stock disponible:
                        <strong id="stock-disponible">0</strong>
                        unidades
                    </small>
                </div>

                {{-- MOTIVO --}}
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

<script>
const stock = @json($stock);

const producto = document.getElementById('producto_id');
const lote = document.getElementById('lote_id');
const origen = document.getElementById('ubicacion_origen_id');
const cantidad = document.getElementById('cantidad');
const disponible = document.getElementById('stock-disponible');

const lotes = [...lote.options];
const ubicaciones = [...origen.options];

producto.addEventListener('change', () => {

    lote.innerHTML = '';

    lotes.forEach(option => {
        if (!option.value || option.dataset.producto == producto.value) {
            lote.appendChild(option.cloneNode(true));
        }
    });

    origen.value = '';
    disponible.textContent = '0';
});

lote.addEventListener('change', () => {

    origen.innerHTML = '';

    ubicaciones.forEach(option => {

        if (!option.value) {
            origen.appendChild(option.cloneNode(true));
            return;
        }

        const existe = stock.some(s =>
            s.producto_id == producto.value &&
            s.lote_id == lote.value &&
            s.ubicacion_id == option.value &&
            s.cantidad > 0
        );

        if (existe) {
            origen.appendChild(option.cloneNode(true));
        }
    });

    disponible.textContent = '0';
});

origen.addEventListener('change', () => {

    const registro = stock.find(s =>
        s.producto_id == producto.value &&
        s.lote_id == lote.value &&
        s.ubicacion_id == origen.value
    );

    const stockActual = registro ? registro.cantidad : 0;

    disponible.textContent = stockActual;
    cantidad.max = stockActual;
});
</script>

@endsection