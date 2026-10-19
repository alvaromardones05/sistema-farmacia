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

                <p class="text-muted">Debes abrir una caja antes de registrar una venta.</p>

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
                        <input type="number" name="saldo_inicial" class="form-control" min="0" step="1" required>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Abrir caja</button>
                    </div>
                </form>
            </div>
        </div>

    @elseif (!$ubicacionVenta)

        <div class="alert alert-danger">
            No existe una ubicación de tipo "vitrina" configurada.
        </div>

    @else

        @php
            $pacientesParaJs = $pacientes->map(fn ($paciente) => [
                'id' => $paciente->id,
                'rut' => $paciente->rut,
                'nombre' => $paciente->nombreCompleto(),
                'convenio_id' => $paciente->convenio_id,
            ])->values();

            $pacienteSeleccionado = $pacientes->firstWhere(
                'id',
                (int) old('paciente_id', request()->integer('paciente_id'))
            );
        @endphp

        <form action="{{ route('ventas.store') }}" method="POST" id="form-venta">
            @csrf

            {{-- PACIENTE Y CONVENIO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="paciente-rut" class="form-label">
                                RUT del paciente <kbd class="ms-2">F3</kbd>
                            </label>

                            <input type="hidden" name="paciente_id" id="paciente_id"
                                value="{{ $pacienteSeleccionado?->id }}">

                            <div class="d-flex flex-wrap gap-2">
                                <input type="text" id="paciente-rut"
                                    class="form-control w-auto flex-grow-1"
                                    placeholder="12.345.678-9"
                                    autocomplete="off"
                                    aria-describedby="paciente-resultado">

                                <button type="button" id="buscar-paciente"
                                    class="btn btn-outline-primary">
                                    Buscar
                                </button>

                                <a href="{{ route('pacientes.create', ['origen' => 'venta']) }}"
                                    class="btn btn-outline-secondary">
                                    + Registrar nuevo cliente
                                </a>
                            </div>

                            <div id="paciente-resultado" class="form-text"
                                role="status" aria-live="polite"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Convenio</label>

                            <div id="convenio-actual" class="form-control bg-light">
                                Particular / sin convenio
                            </div>

                            <div class="form-text">
                                El convenio se obtiene automáticamente desde el paciente seleccionado.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- PRODUCTO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <strong>Agregar producto</strong>
                </div>

                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-5">
                            <label for="codigo-barra" class="form-label">
                                Código de barras <kbd class="ms-2">F2</kbd>
                            </label>

                            <input type="text" id="codigo-barra" class="form-control mb-3"
                                placeholder="Escanear producto..." autocomplete="off">

                            <select id="producto-select" class="form-select">
                                <option value="">Selecciona un producto</option>

                                @foreach ($productos as $producto)
                                    <option
                                        value="{{ $producto['id'] }}"
                                        data-nombre="{{ $producto['nombre'] }}"
                                        data-codigo-barra="{{ $producto['codigo_barra'] }}"
                                        data-precio="{{ $producto['precio_neto'] }}"
                                        data-disponible="{{ $producto['disponible'] }}"
                                        data-requiere-receta="{{ $producto['requiere_receta'] ? '1' : '0' }}"
                                        data-tipo-receta="{{ $producto['tipo_receta'] }}">
                                        {{ $producto['nombre'] }} ({{ $producto['codigo'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Precio neto</label>
                            <input type="text" id="producto-precio" class="form-control" disabled>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="producto-cantidad" class="form-control"
                                min="1" value="1" disabled>

                            <small class="text-muted">
                                Disp: <span id="producto-disponible">—</span>
                            </small>
                        </div>

                        <div class="col-md-2">
                            <label for="producto-beneficio" class="form-label">Beneficio</label>
                            <input type="text" id="producto-beneficio" class="form-control"
                                value="Sin convenio" disabled>
                        </div>

                        <div class="col-md-1">
                            <button type="button" id="btn-agregar"
                                class="btn btn-primary w-100" disabled>
                                +
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- DETALLE --}}
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
                    <small class="text-muted d-block">Incluye IVA 19%.</small>
                </div>
            </div>

            {{-- PAGO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <strong>Pago</strong>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Método de pago *</label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Referencia de transacción</label>
                            <input type="text" name="referencia_transaccion"
                                class="form-control" maxlength="100" placeholder="Opcional">
                        </div>

                    </div>
                </div>
            </div>

            <div id="detalles-ocultos"></div>

            <button type="submit" class="btn btn-success btn-lg"
                id="btn-confirmar" disabled>
                Confirmar venta
            </button>
        </form>

        <script>
            let carrito = [];

            const convenios = @json($conveniosParaJs);
            const pacientes = @json($pacientesParaJs);

            const pacienteInput = document.getElementById('paciente_id');
            const pacienteRutInput = document.getElementById('paciente-rut');
            const pacienteResultado = document.getElementById('paciente-resultado');
            const convenioActual = document.getElementById('convenio-actual');

            const productoSelect = document.getElementById('producto-select');
            const codigoBarraInput = document.getElementById('codigo-barra');
            const precioInput = document.getElementById('producto-precio');
            const cantidadInput = document.getElementById('producto-cantidad');
            const beneficioInput = document.getElementById('producto-beneficio');
            const disponibleSpan = document.getElementById('producto-disponible');
            const btnAgregar = document.getElementById('btn-agregar');

            const carritoFilas = document.getElementById('carrito-filas');
            const detallesOcultos = document.getElementById('detalles-ocultos');
            const btnConfirmar = document.getElementById('btn-confirmar');
            const totalAproximado = document.getElementById('total-aproximado');

            function normalizarRut(rut) {
                return String(rut).replace(/[.\-\s]/g, '').toUpperCase();
            }

            function obtenerPacienteActual() {
                return pacientes.find(p => String(p.id) === pacienteInput.value) ?? null;
            }

            function obtenerConvenioActual() {
                const paciente = obtenerPacienteActual();
                return paciente?.convenio_id
                    ? convenios[paciente.convenio_id] ?? null
                    : null;
            }

            function mostrarPaciente(mensaje = 'Venta libre / sin paciente') {
                const paciente = obtenerPacienteActual();
                const convenio = obtenerConvenioActual();

                pacienteResultado.textContent = paciente
                    ? `✓ ${paciente.nombre} · RUT: ${paciente.rut} · Convenio: ${convenio?.nombre ?? 'Sin convenio'}`
                    : mensaje;

                actualizarConvenio();
            }

            function buscarPaciente() {
                const rut = normalizarRut(pacienteRutInput.value);
                const paciente = rut
                    ? pacientes.find(p => normalizarRut(p.rut) === rut)
                    : null;

                pacienteInput.value = paciente ? String(paciente.id) : '';

                mostrarPaciente(
                    rut
                        ? 'No se encontró un paciente registrado con este RUT. Puedes registrar un nuevo cliente.'
                        : 'Ingresa un RUT para buscar al paciente.'
                );
            }

            function calcularEstimacion(productoId, precio) {
                const convenio = obtenerConvenioActual();

                if (!convenio) {
                    return { precioFinal: precio, texto: 'Sin convenio' };
                }

                const cobertura = convenio.coberturas[String(productoId)] ?? null;

                if (cobertura?.copago_fijo !== null &&
                    cobertura?.copago_fijo !== undefined &&
                    Number(cobertura.copago_fijo) > 0) {

                    const copago = Math.min(precio, Number(cobertura.copago_fijo));

                    return {
                        precioFinal: copago,
                        texto: `Copago fijo $${Math.round(copago).toLocaleString('es-CL')}`
                    };
                }

                if (cobertura?.copago_pct !== null &&
                    cobertura?.copago_pct !== undefined) {

                    const copagoPct = Math.max(
                        0,
                        Math.min(100, Number(cobertura.copago_pct))
                    );

                    return {
                        precioFinal: precio * (copagoPct / 100),
                        texto: `Copago ${copagoPct}%`
                    };
                }

                const descuento = Math.max(
                    0,
                    Math.min(100, Number(convenio.descuento_pct ?? 0))
                );

                return {
                    precioFinal: precio * (1 - descuento / 100),
                    texto: descuento > 0
                        ? `${descuento}% descuento`
                        : 'Sin descuento'
                };
            }

            function actualizarConvenio() {
                const convenio = obtenerConvenioActual();

                convenioActual.textContent = convenio
                    ? convenio.nombre
                    : 'Particular / sin convenio';

                actualizarProductoSeleccionado();
                renderCarrito();
            }

            function actualizarProductoSeleccionado() {
                const opcion = productoSelect.options[productoSelect.selectedIndex];

                if (!productoSelect.value) {
                    precioInput.value = '';
                    beneficioInput.value = 'Sin convenio';
                    disponibleSpan.textContent = '—';
                    cantidadInput.disabled = true;
                    btnAgregar.disabled = true;
                    return;
                }

                if (opcion.dataset.requiereReceta === '1') {
                    alert(
                        `El medicamento "${opcion.dataset.nombre}" requiere receta (${opcion.dataset.tipoReceta}) y no puede agregarse a una venta libre.`
                    );

                    productoSelect.value = '';
                    actualizarProductoSeleccionado();
                    return;
                }

                const precio = Number(opcion.dataset.precio);
                const estimacion = calcularEstimacion(productoSelect.value, precio);

                precioInput.value = Math.round(precio).toLocaleString('es-CL');
                beneficioInput.value = estimacion.texto;
                disponibleSpan.textContent = opcion.dataset.disponible;
                cantidadInput.max = opcion.dataset.disponible;
                cantidadInput.value = 1;
                cantidadInput.disabled = false;
                btnAgregar.disabled = false;
            }

            function agregarProducto() {
                if (!productoSelect.value) return;

                const opcion = productoSelect.options[productoSelect.selectedIndex];
                const productoId = String(productoSelect.value);
                const cantidad = Number(cantidadInput.value);
                const disponible = Number(opcion.dataset.disponible);
                const precio = Number(opcion.dataset.precio);

                if (!Number.isInteger(cantidad) || cantidad < 1) return;

                const existente = carrito.find(
                    item => item.producto_id === productoId
                );

                const cantidadTotal = cantidad + (existente?.cantidad ?? 0);

                if (cantidadTotal > disponible) {
                    alert('La cantidad supera el stock disponible en vitrina.');
                    return;
                }

                if (existente) {
                    existente.cantidad = cantidadTotal;
                } else {
                    carrito.push({
                        producto_id: productoId,
                        nombre: opcion.dataset.nombre,
                        cantidad,
                        precio
                    });
                }

                renderCarrito();
                productoSelect.value = '';
                actualizarProductoSeleccionado();
            }

            function procesarCodigoBarra() {
                const codigo = codigoBarraInput.value.trim();
                if (!codigo) return;

                const opcion = Array.from(productoSelect.options).find(
                    option => String(option.dataset.codigoBarra || '').trim() === codigo
                );

                if (!opcion) {
                    alert(`No se encontró un producto con el código ${codigo}.`);
                    return;
                }

                productoSelect.value = opcion.value;
                actualizarProductoSeleccionado();
                agregarProducto();
            }

            function renderCarrito() {
                carritoFilas.innerHTML = '';
                detallesOcultos.innerHTML = '';

                if (!carrito.length) {
                    carritoFilas.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Aún no hay productos agregados.
                            </td>
                        </tr>
                    `;

                    totalAproximado.textContent = '0';
                    btnConfirmar.disabled = true;
                    return;
                }

                let total = 0;

                carrito.forEach((item, index) => {
                    const estimacion = calcularEstimacion(item.producto_id, item.precio);
                    const subtotalNeto = estimacion.precioFinal * item.cantidad;

                    total += subtotalNeto * 1.19;

                    carritoFilas.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${item.nombre}</td>
                            <td>${item.cantidad}</td>
                            <td>$${Math.round(item.precio).toLocaleString('es-CL')}</td>
                            <td>
                                <span class="badge text-bg-light border">
                                    ${estimacion.texto}
                                </span>
                            </td>
                            <td>$${Math.round(subtotalNeto).toLocaleString('es-CL')}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-index="${index}">
                                    Quitar
                                </button>
                            </td>
                        </tr>
                    `);

                    detallesOcultos.insertAdjacentHTML('beforeend', `
                        <input type="hidden"
                            name="detalles[${index}][producto_id]"
                            value="${item.producto_id}">

                        <input type="hidden"
                            name="detalles[${index}][cantidad]"
                            value="${item.cantidad}">
                    `);
                });

                totalAproximado.textContent =
                    Math.round(total).toLocaleString('es-CL');

                btnConfirmar.disabled = false;
            }

            document.addEventListener('keydown', event => {
                if (event.key === 'F2') {
                    event.preventDefault();
                    codigoBarraInput.focus();
                    codigoBarraInput.select();
                }

                if (event.key === 'F3') {
                    event.preventDefault();
                    pacienteRutInput.focus();
                    pacienteRutInput.select();
                }
            });

            document.getElementById('buscar-paciente')
                .addEventListener('click', buscarPaciente);

            pacienteRutInput.addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    buscarPaciente();
                }
            });

            pacienteRutInput.addEventListener('input', () => {
                pacienteInput.value = '';
                mostrarPaciente(
                    'Presiona Buscar o Enter para identificar al paciente.'
                );
            });

            productoSelect.addEventListener(
                'change',
                actualizarProductoSeleccionado
            );

            btnAgregar.addEventListener('click', agregarProducto);

            codigoBarraInput.addEventListener('keydown', event => {
                if (event.key !== 'Enter') return;

                event.preventDefault();

                try {
                    procesarCodigoBarra();
                } finally {
                    codigoBarraInput.value = '';
                    codigoBarraInput.focus();
                }
            });

            carritoFilas.addEventListener('click', event => {
                const boton = event.target.closest('button[data-index]');
                if (!boton) return;

                carrito.splice(Number(boton.dataset.index), 1);
                renderCarrito();
            });

            const pacienteInicial = obtenerPacienteActual();

            pacienteRutInput.value = pacienteInicial?.rut ?? '';
            mostrarPaciente();
            codigoBarraInput.focus();
        </script>

    @endif

</div>
@endsection