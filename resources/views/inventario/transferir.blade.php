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
                            <option
                                value="{{ $producto->id }}"
                                data-condicion="{{ $producto->condicion_almacenamiento }}"
                                @selected(old('producto_id') == $producto->id)
                            >

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
                            <option
                            value="{{ $ubicacion->id }}"
                            data-tipo="{{ $ubicacion->tipo }}"
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
                                data-tipo="{{ $ubicacion->tipo }}"
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
                              required maxlength="255"
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
const destino = document.getElementById('ubicacion_destino_id');
const cantidad = document.getElementById('cantidad');
const disponible = document.getElementById('stock-disponible');

const lotesOriginales = [...lote.options].map(
    option => option.cloneNode(true)
);

const ubicacionesOriginales = [...origen.options].map(
    option => option.cloneNode(true)
);

function obtenerCondicionProducto() {
    const option = producto.options[producto.selectedIndex];

    return option?.dataset.condicion || null;
}

function tiposPermitidos() {
    const condicion = obtenerCondicionProducto();

    if (condicion === 'refrigerado') {
        return [
            'frigorifica',
            'vitrina_refrigerada'
        ];
    }

    return condicion === 'ambiente' ? ['bodega', 'vitrina'] : [];
}

function cargarLotes() {
    lote.innerHTML = '';

    lotesOriginales.forEach(option => {

        if (
            !option.value ||
            option.dataset.producto == producto.value
        ) {
            lote.appendChild(
                option.cloneNode(true)
            );
        }
    });

    lote.value = '';
}

function cargarOrigenes() {
    origen.innerHTML = '';

    const permitidos = tiposPermitidos();

    ubicacionesOriginales.forEach(option => {

        if (!option.value) {
            origen.appendChild(
                option.cloneNode(true)
            );
            return;
        }

        if (!permitidos.includes(option.dataset.tipo)) {
            return;
        }

        const existe = stock.some(s =>
            s.producto_id == producto.value &&
            s.lote_id == lote.value &&
            s.ubicacion_id == option.value &&
            s.cantidad > 0
        );

        if (existe) {
            origen.appendChild(
                option.cloneNode(true)
            );
        }
    });

    origen.value = '';

    disponible.textContent = '0';

    cantidad.value = '';
    cantidad.removeAttribute('max');

    cargarDestinos();
}

function cargarDestinos() {
    destino.innerHTML = '';

    const permitidos = tiposPermitidos();
    const tipoOrigen = origen.options[origen.selectedIndex]?.dataset.tipo;

    ubicacionesOriginales.forEach(option => {

        if (!option.value) {

            const placeholder =
                option.cloneNode(true);

            placeholder.textContent =
                'Seleccione la ubicación de destino';

            destino.appendChild(placeholder);

            return;
        }

        if (!tipoOrigen || !permitidos.includes(option.dataset.tipo) || option.dataset.tipo === tipoOrigen) {
            return;
        }

        if (option.value == origen.value) {
            return;
        }

        destino.appendChild(
            option.cloneNode(true)
        );
    });

    destino.value = '';
}

function actualizarStockDisponible() {

    const registro = stock.find(s =>
        s.producto_id == producto.value &&
        s.lote_id == lote.value &&
        s.ubicacion_id == origen.value
    );

    const stockActual =
        registro ? parseInt(registro.cantidad) : 0;

    disponible.textContent = stockActual;

    cantidad.max = stockActual;

    if (
        parseInt(cantidad.value || 0) >
        stockActual
    ) {
        cantidad.value = stockActual;
    }
}

producto.addEventListener('change', () => {

    cargarLotes();

    origen.innerHTML =
        '<option value="">Seleccione la ubicación de origen</option>';

    destino.innerHTML =
        '<option value="">Seleccione la ubicación de destino</option>';

    disponible.textContent = '0';

    cantidad.value = '';
    cantidad.removeAttribute('max');
});

lote.addEventListener('change', () => {
    cargarOrigenes();
});

origen.addEventListener('change', () => {

    actualizarStockDisponible();

    cargarDestinos();
});

// Aplicar los mismos filtros al recuperar un formulario con errores.
const seleccionInicial = {
    lote: lote.value,
    origen: origen.value,
    destino: destino.value,
    cantidad: cantidad.value
};
cargarLotes();
if ([...lote.options].some(op => op.value === seleccionInicial.lote)) lote.value = seleccionInicial.lote;
cargarOrigenes();
if ([...origen.options].some(op => op.value === seleccionInicial.origen)) origen.value = seleccionInicial.origen;
cargarDestinos();
if ([...destino.options].some(op => op.value === seleccionInicial.destino)) destino.value = seleccionInicial.destino;
cantidad.value = seleccionInicial.cantidad;
actualizarStockDisponible();
</script>

@endsection
