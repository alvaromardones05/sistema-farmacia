@extends('layouts.app')

@section('title', 'Nueva venta')

@section('content')
@include('inventario._styles')

<div class="container-fluid px-0 inventario-modulo">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Nueva venta</h1>
        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">Volver</a>
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

    @if (!$aperturaCaja)

        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h5 class="card-title">
                    <span class="badge text-bg-secondary">Sin caja abierta</span>
                </h5>

                <p class="text-muted">
                    Debes abrir una caja antes de registrar una venta.
                </p>

                <form action="{{ route('caja.abrir') }}" method="POST" class="row g-3">
                    @csrf

                    <input type="hidden" name="redirect_to" value="{{ route('ventas.create') }}">

                    <div class="col-md-6">
                        <label class="form-label">Caja *</label>

                        <select name="caja_id" class="form-select" required>
                            <option value="">Selecciona una caja</option>

                            @foreach (\App\Models\Caja::activas()->get() as $caja)
                                <option value="{{ $caja->id }}">
                                    {{ $caja->descripcion }} ({{ $caja->numero_caja }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Saldo inicial *</label>
                        <input
                            type="number"
                            name="saldo_inicial"
                            class="form-control"
                            min="0"
                            step="1"
                            required
                        >
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            Abrir caja
                        </button>
                    </div>

                </form>

            </div>
        </div>

    @elseif (!$ubicacionVenta)

        <div class="alert alert-danger">
            No existe una ubicación de tipo "vitrina" configurada.
        </div>

    @else

        <form action="{{ route('ventas.store') }}" method="POST" id="form-venta">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label
                                for="paciente_id"
                                class="form-label"
                            >
                                Paciente
                            </label>

                            <select
                                name="paciente_id"
                                id="paciente_id"
                                class="form-select"
                            >
                                <option
                                    value=""
                                    data-convenio-id=""
                                >
                                    Venta libre / sin paciente
                                </option>

                                @foreach ($pacientes as $paciente)
                                    <option
                                        value="{{ $paciente->id }}"
                                        data-convenio-id="{{ $paciente->convenio_id ?? '' }}"
                                        @selected(
                                            old('paciente_id') == $paciente->id 
                                        )
                                    >
                                        {{ $paciente->nombreCompleto() }}
                                        ({{ $paciente->rut }})
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">
                                Convenio
                            </label>

                            <div
                                id="convenio-actual"
                                class="form-control bg-light"
                            >
                                Particular / sin convenio
                            </div>

                            <div class="form-text">
                                El convenio se obtiene automáticamente
                                desde el paciente seleccionado.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white">
                    <strong>Agregar producto</strong>
                </div>

                <div class="card-body">

                    <div class="row g-3 align-items-end">

                        

                        <div class="col-md-5">
                            <label class="form-label">Producto</label>

                            <div class="mb-3">
                                <label for="codigo-barra" class="form-label">Código de barras</label>

                                <input
                                    type="text"
                                    id="codigo-barra"
                                    class="form-control"
                                    placeholder="Escanear producto..."
                                    autocomplete="off"
                                >
                            </div>

                            <select id="producto-select" class="form-select">

                                <option value="">Selecciona un producto</option>

                                @foreach ($productos as $producto)

                                    <option
                                        value="{{ $producto['id'] }}"

                                        data-nombre="{{ $producto['nombre'] }}"

                                        data-codigo-barra="{{ $producto['codigo_barra'] }}"

                                        data-precio="{{ $producto['precio_neto'] }}"

                                        data-disponible="{{ $producto['disponible'] }}"

                                        data-requiere-receta="{{
                                            $producto['requiere_receta'] ? '1' : '0'
                                        }}"

                                        data-tipo-receta="{{ $producto['tipo_receta'] }}"
                                        
                                    >
                                        {{ $producto['nombre'] }}
                                        ({{ $producto['codigo'] }})

                                        
                                    </option>

                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Precio neto</label>
                            <input
                                type="text"
                                id="producto-precio"
                                class="form-control"
                                disabled
                            >
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>

                            <input
                                type="number"
                                id="producto-cantidad"
                                class="form-control"
                                min="1"
                                value="1"
                                disabled
                            >

                            <small class="text-muted">
                                Disp:
                                <span id="producto-disponible">—</span>
                            </small>
                        </div>

                        <div class="col-md-2">

                            <label
                                for="producto-beneficio"
                                class="form-label" >
                                Beneficio
                            </label>

                            <input
                                type="text"
                                id="producto-beneficio"
                                class="form-control"
                                value="Sin convenio"
                                disabled >
                        </div>

                        <div class="col-md-1">
                            <button
                                type="button"
                                id="btn-agregar"
                                class="btn btn-primary w-100"
                                disabled
                            >
                                +
                            </button>
                        </div>

                    </div>

                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white">
                    <strong>Detalle de la venta</strong>
                </div>

                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unit.</th>
                                <th>Beneficio</th>
                                <th>Subtotal neto</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="carrito-filas">

                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    Aún no hay productos agregados.
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

                <div class="card-footer bg-white text-end">

                    <strong>
                        Total estimado con IVA:
                        $<span id="total-aproximado">0</span>
                    </strong>

                    <small class="text-muted d-block">
                        Incluye IVA 19%.
                    </small>

                </div>

            </div>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white">
                    <strong>Pago</strong>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">
                                Método de pago *
                            </label>

                            <select
                                name="metodo_pago"
                                class="form-select"
                                required
                            >
                                <option value="efectivo">Efectivo</option>
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Referencia de transacción
                            </label>

                            <input
                                type="text"
                                name="referencia_transaccion"
                                class="form-control"
                                maxlength="100"
                                placeholder="Opcional"
                            >

                        </div>

                    </div>

                </div>

            </div>

            <div id="detalles-ocultos"></div>

            <button
                type="submit"
                class="btn btn-success btn-lg"
                id="btn-confirmar"
                disabled
            >
                Confirmar venta
            </button>

        </form>

        <script>
    let carrito = [];

    const convenios = @json($conveniosParaJs);

    const pacienteSelect =
        document.getElementById('paciente_id');

    const convenioActual =
        document.getElementById('convenio-actual');

    const productoSelect =
        document.getElementById('producto-select');

    const codigoBarraInput = document.getElementById('codigo-barra');

    const precioInput =
        document.getElementById('producto-precio');

    const cantidadInput =
        document.getElementById('producto-cantidad');

    const beneficioInput =
        document.getElementById('producto-beneficio');

    const disponibleSpan =
        document.getElementById('producto-disponible');

    const btnAgregar =
        document.getElementById('btn-agregar');


    function obtenerConvenioActual() {
        const opcion =
            pacienteSelect.options[
                pacienteSelect.selectedIndex
            ];

        const convenioId =
            opcion?.dataset?.convenioId;

        if (
            !convenioId
            || !convenios[convenioId]
        ) {
            return null;
        }

        return convenios[convenioId];
    }
    

    codigoBarraInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        const codigo = String(this.value).trim();

        if (!codigo) {
            return;
        }

        const opcion = Array.from(productoSelect.options).find(option => {
            const codigoProducto = String(
                option.dataset.codigoBarra || ''
            ).trim();

            return codigoProducto === codigo;
        });

        if (!opcion) {
            alert(`No se encontró un producto con el código ${codigo}.`);
            this.select();
            return;
        }

        productoSelect.value = opcion.value;

        actualizarProductoSeleccionado();

        this.value = '';
        this.focus();
    });

    /*
     * Solo estimación visual.
     * El servidor vuelve a calcular absolutamente todo.
     */
    function calcularEstimacion(
        productoId,
        precio
    ) {
        const convenio =
            obtenerConvenioActual();

        if (!convenio) {
            return {
                precioFinal: precio,
                texto: 'Sin convenio'
            };
        }

        const cobertura =
            convenio.coberturas[
                String(productoId)
            ] ?? null;

        if (cobertura) {

            if (
                cobertura.copago_fijo !== null
                && Number(cobertura.copago_fijo) > 0
            ) {
                const copago = Math.min(
                    precio,
                    Number(cobertura.copago_fijo)
                );

                return {
                    precioFinal: copago,

                    texto:
                        'Copago fijo $'
                        + Math.round(copago)
                            .toLocaleString('es-CL')
                };
            }

            if (
                cobertura.copago_pct !== null
            ) {
                const copagoPct = Math.max(
                    0,
                    Math.min(
                        100,
                        Number(
                            cobertura.copago_pct
                        )
                    )
                );

                return {
                    precioFinal:
                        precio
                        * (copagoPct / 100),

                    texto:
                        `Copago ${copagoPct}%`
                };
            }
        }

        const descuento = Math.max(
            0,
            Math.min(
                100,
                Number(
                    convenio.descuento_pct ?? 0
                )
            )
        );

        return {
            precioFinal:
                precio
                * (1 - descuento / 100),

            texto:
                descuento > 0
                    ? `${descuento}% descuento`
                    : 'Sin descuento'
        };
    }


    function actualizarConvenio() {
        const convenio =
            obtenerConvenioActual();

        convenioActual.textContent =
            convenio
                ? convenio.nombre
                : 'Particular / sin convenio';

        actualizarProductoSeleccionado();

        /*
         * Si cambia el paciente, cambia también
         * la estimación de los productos agregados.
         */
        renderCarrito();
    }


    function actualizarProductoSeleccionado() {
        const opcion =
            productoSelect.options[
                productoSelect.selectedIndex
            ];

        if (!productoSelect.value) {
            precioInput.value = '';

            beneficioInput.value =
                'Sin convenio';

            disponibleSpan.textContent =
                '—';

            cantidadInput.disabled =
                true;

            btnAgregar.disabled =
                true;

            return;
        }

        if (
            opcion.dataset.requiereReceta
            === '1'
        ) {
            alert(
                `El medicamento "${opcion.dataset.nombre}" requiere receta (${opcion.dataset.tipoReceta}) y no puede agregarse a una venta libre.`
            );

            productoSelect.value = '';

            actualizarProductoSeleccionado();

            return;
        }

        const precio =
            Number(opcion.dataset.precio);

        const estimacion =
            calcularEstimacion(
                productoSelect.value,
                precio
            );

        precioInput.value =
            Math.round(precio)
                .toLocaleString('es-CL');

        beneficioInput.value =
            estimacion.texto;

        disponibleSpan.textContent =
            opcion.dataset.disponible;

        cantidadInput.max =
            opcion.dataset.disponible;

        cantidadInput.value = 1;

        cantidadInput.disabled =
            false;

        btnAgregar.disabled =
            false;
    }


    btnAgregar.addEventListener(
        'click',
        function () {

            if (!productoSelect.value) {
                return;
            }

            const opcion =
                productoSelect.options[
                    productoSelect.selectedIndex
                ];

            const productoId =
                String(productoSelect.value);

            const cantidad =
                Number(cantidadInput.value);

            const disponible =
                Number(
                    opcion.dataset.disponible
                );

            const precio =
                Number(opcion.dataset.precio);

            if (
                !Number.isInteger(cantidad)
                || cantidad < 1
            ) {
                return;
            }

            /*
             * Si el producto ya está en el carrito,
             * aumentamos la cantidad en vez de duplicarlo.
             */
            const existente =
                carrito.find(
                    item =>
                        item.producto_id
                        === productoId
                );

            const cantidadTotal =
                cantidad
                + (existente?.cantidad ?? 0);

            if (
                cantidadTotal > disponible
            ) {
                alert(
                    'La cantidad supera el stock disponible en vitrina.'
                );

                return;
            }

            if (existente) {
                existente.cantidad =
                    cantidadTotal;

            } else {
                carrito.push({
                    producto_id:
                        productoId,

                    nombre:
                        opcion.dataset.nombre,

                    cantidad:
                        cantidad,

                    precio:
                        precio,
                });
            }

            renderCarrito();

            productoSelect.value = '';

            actualizarProductoSeleccionado();
        }
    );


    function renderCarrito() {
        const filas =
            document.getElementById(
                'carrito-filas'
            );

        const ocultos =
            document.getElementById(
                'detalles-ocultos'
            );

        const btnConfirmar =
            document.getElementById(
                'btn-confirmar'
            );

        const totalAproximado =
            document.getElementById(
                'total-aproximado'
            );

        filas.innerHTML = '';
        ocultos.innerHTML = '';

        if (carrito.length === 0) {
            filas.innerHTML = `
                <tr>
                    <td
                        colspan="6"
                        class="text-center text-muted py-3"
                    >
                        Aún no hay productos agregados.
                    </td>
                </tr>
            `;

            btnConfirmar.disabled =
                true;

            totalAproximado.textContent =
                '0';

            return;
        }

        let total = 0;

        carrito.forEach(
            (item, index) => {

                const estimacion =
                    calcularEstimacion(
                        item.producto_id,
                        item.precio
                    );

                const subtotalNeto =
                    estimacion.precioFinal
                    * item.cantidad;

                const totalConIva =
                    subtotalNeto * 1.19;

                total += totalConIva;

                const fila =
                    document.createElement('tr');

                fila.innerHTML = `
                    <td>
                        ${item.nombre}
                    </td>

                    <td>
                        ${item.cantidad}
                    </td>

                    <td>
                        $${Math.round(item.precio)
                            .toLocaleString('es-CL')}
                    </td>

                    <td>
                        <span
                            class="badge text-bg-light border"
                        >
                            ${estimacion.texto}
                        </span>
                    </td>

                    <td>
                        $${Math.round(subtotalNeto)
                            .toLocaleString('es-CL')}
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-index="${index}"
                        >
                            Quitar
                        </button>
                    </td>
                `;

                filas.appendChild(fila);


                /*
                 * ESTO ES TODO LO QUE EL POST
                 * ENVÍA POR PRODUCTO.
                 */
                const productoInput =
                    document.createElement(
                        'input'
                    );

                productoInput.type =
                    'hidden';

                productoInput.name =
                    `detalles[${index}][producto_id]`;

                productoInput.value =
                    item.producto_id;

                ocultos.appendChild(
                    productoInput
                );


                const cantidadOculta =
                    document.createElement(
                        'input'
                    );

                cantidadOculta.type =
                    'hidden';

                cantidadOculta.name =
                    `detalles[${index}][cantidad]`;

                cantidadOculta.value =
                    item.cantidad;

                ocultos.appendChild(
                    cantidadOculta
                );
            }
        );

        totalAproximado.textContent =
            Math.round(total)
                .toLocaleString('es-CL');

        btnConfirmar.disabled =
            false;
    }


    document
        .getElementById('carrito-filas')
        .addEventListener(
            'click',
            function (event) {

                const boton =
                    event.target.closest(
                        'button[data-index]'
                    );

                if (!boton) {
                    return;
                }

                carrito.splice(
                    Number(
                        boton.dataset.index
                    ),
                    1
                );

                renderCarrito();
            }
        );


    pacienteSelect.addEventListener(
        'change',
        actualizarConvenio
    );

    productoSelect.addEventListener(
        'change',
        actualizarProductoSeleccionado
    );


    actualizarConvenio();
</script>

    @endif

</div>

@endsection