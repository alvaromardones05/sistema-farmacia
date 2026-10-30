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

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    @elseif ($ubicacionesVenta->isEmpty())

        <div class="alert alert-danger">
            No existe una Vitrina ni una Vitrina Refrigerada activa configurada.
        </div>

    @else

        @if (!$ubicacionesVenta->has('vitrina') || !$ubicacionesVenta->has('vitrina_refrigerada'))
            <div class="alert alert-warning">
                No existe una {{ !$ubicacionesVenta->has('vitrina') ? 'Vitrina' : 'Vitrina Refrigerada' }} activa configurada.
                Solo se ofrecen productos con stock en su ubicación de venta correspondiente.
            </div>
        @endif

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

                                @can('gestionar_pacientes')
                                <a href="{{ route('pacientes.create', ['origen' => 'venta']) }}"
                                    class="btn btn-outline-secondary">
                                    + Registrar nuevo cliente
                                </a>
                                @endcan
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

            {{-- RECETAS DEL PACIENTE Y ASOCIACIONES POR PRODUCTO --}}
            <div class="card border-0 shadow-sm mb-4" id="recetas-tarjeta"
                @can('gestionar_recetas') data-registro-url="{{ route('recetas.create') }}" @endcan>
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <strong>Recetas del paciente</strong>
                    <kbd>F4</kbd>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Gestiona las recetas asociadas al cliente.</p>
                    <p id="recetas-paciente" class="mb-2" role="status" aria-live="polite">Selecciona un paciente para consultar o registrar sus recetas.</p>
                    @can('gestionar_recetas')
                        <div id="recetas-acciones" class="d-flex flex-wrap gap-2 mb-2 d-none">
                            <button type="button" id="ver-recetas-paciente" class="btn btn-sm btn-outline-primary" aria-expanded="false" aria-controls="recetas-paciente-panel">Ver recetas</button>
                            <a id="registrar-receta-paciente" class="btn btn-sm btn-outline-secondary">+ Registrar receta</a>
                        </div>
                        <div id="recetas-paciente-panel" class="mb-3" hidden>
                            <h2 id="recetas-paciente-titulo" class="h6" tabindex="-1">Recetas registradas</h2>
                            <div id="recetas-paciente-lista"></div>
                        </div>
                    @endcan
                    <p id="recetas-resumen" class="mb-0" role="status" aria-live="polite">No requerida para los productos actuales.</p>
                    <div id="recetas-mensaje" class="small text-warning" role="status" aria-live="polite"></div>
                    <div id="recetas-productos"></div>
                </div>
                <div id="receta-producto-bloque" class="card-body border-top" hidden>
                    <h2 id="receta-producto-titulo" class="h6">Buscar receta</h2>
                    <p id="receta-producto-aviso" class="small text-muted" role="status"></p>
                    <label for="receta-producto" class="form-label">Recetas aprobadas compatibles y con saldo suficiente</label>
                    <select id="receta-producto" class="form-select mb-2"></select>
                    <p id="receta-producto-info" class="small text-muted"></p>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="asociar-receta" class="btn btn-primary btn-sm">Seleccionar receta</button>
                        @can('gestionar_recetas')
                            <a id="registrar-receta" href="{{ route('recetas.create') }}" class="btn btn-outline-secondary btn-sm">Registrar nueva receta</a>
                        @endcan
                        <button type="button" id="cerrar-busqueda-receta" class="btn btn-link btn-sm">Cerrar</button>
                    </div>
                    <div class="form-text">Al registrar una nueva receta se abandona esta venta y se pierde el carrito. La receta necesitará aprobación antes de usarse.</div>
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
                                <th>Receta</th>
                                <th>Beneficio</th>
                                <th>Subtotal neto</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="carrito-filas">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
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
            const recetasDisponibles = @json($recetasParaJs);
            const recetasPaciente = @json($recetasPacienteParaJs);

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
            const recetaProducto = document.getElementById('receta-producto');
            const bloqueReceta = document.getElementById('receta-producto-bloque');
            const recetasMensaje = document.getElementById('recetas-mensaje');
            const recetasResumen = document.getElementById('recetas-resumen');
            const recetasTarjeta = document.getElementById('recetas-tarjeta');
            const recetasProductos = document.getElementById('recetas-productos');
            const recetasPacientePanel = document.getElementById('recetas-paciente-panel');
            const verRecetasPaciente = document.getElementById('ver-recetas-paciente');
            const asociarReceta = document.getElementById('asociar-receta');
            let indiceReceta = null;
            let pacienteRecetas = pacienteInput.value;

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

            function recetasParaProducto(productoId, tipo) {
                return recetasDisponibles.filter(r => String(r.paciente_id) === pacienteInput.value
                    && String(r.producto_id) === String(productoId)
                    && (tipo === 'ninguna' || r.tipo === tipo));
            }

            function llenarRecetas(select, recetas, seleccionado = '') {
                select.replaceChildren(new Option(recetas.length ? 'Selecciona una receta' : 'No existe una receta válida para este producto y cantidad.', ''));
                recetas.forEach(r => select.add(new Option(`#${r.numero} · emitida ${r.emision} · vence ${r.vencimiento} · disponible ${r.disponible}`, String(r.id))));
                select.value = String(seleccionado || (recetas.length === 1 ? recetas[0].id : ''));
            }

            function recetaDeItem(item) {
                return recetasParaProducto(item.producto_id, item.tipo_receta)
                    .find(r => String(r.id) === String(item.receta_detalle_id));
            }

            function recetaValidaItem(item) {
                const receta = recetaDeItem(item);
                return !!receta && item.cantidad <= receta.disponible;
            }

            function actualizarInformacionReceta() {
                const item = carrito[indiceReceta];
                const receta = item ? recetasParaProducto(item.producto_id, item.tipo_receta)
                    .find(r => String(r.id) === recetaProducto.value && r.disponible >= item.cantidad) : null;
                document.getElementById('receta-producto-info').textContent = receta
                    ? `Autorizada: ${receta.cantidad_prescrita} · Ya dispensada: ${receta.cantidad_dispensada} · Disponible: ${receta.disponible}`
                    : '';
                asociarReceta.disabled = !receta;
            }

            function requerirPacienteRecetas() {
                if (!obtenerPacienteActual()) {
                    recetasMensaje.textContent = 'Selecciona primero un paciente para consultar sus recetas.';
                    pacienteRutInput.focus(); pacienteRutInput.select();
                    return false;
                }
                return true;
            }

            function abrirRecetasPaciente() {
                if (!requerirPacienteRecetas()) return;
                if (!recetasPacientePanel) {
                    recetasMensaje.textContent = 'No tienes permiso para consultar el módulo de recetas.';
                    return;
                }
                recetasPacientePanel.hidden = false;
                verRecetasPaciente.setAttribute('aria-expanded', 'true');
                document.getElementById('recetas-paciente-titulo').focus();
            }

            function abrirRecetas(index = null) {
                if (!requerirPacienteRecetas()) return;
                const pendiente = carrito.findIndex(item => item.requiere_receta && !recetaValidaItem(item));
                if (index === null && pendiente < 0) {
                    abrirRecetasPaciente();
                    return;
                }
                indiceReceta = index ?? pendiente;
                const item = carrito[indiceReceta];
                if (!item?.requiere_receta) {
                    recetasMensaje.textContent = 'Agrega al carrito un producto que requiera receta para asociarla.';
                    return;
                }
                const recetas = recetasParaProducto(item.producto_id, item.tipo_receta)
                    .filter(r => r.disponible >= item.cantidad);
                document.getElementById('receta-producto-titulo').textContent = `Recetas para ${item.nombre}`;
                document.getElementById('receta-producto-aviso').textContent = recetas.length
                    ? `Paciente: ${obtenerPacienteActual().nombre}. Cantidad a dispensar: ${item.cantidad}.`
                    : 'No existe una receta válida para este producto y cantidad.';
                llenarRecetas(recetaProducto, recetas, item.receta_detalle_id);
                const registrar = document.getElementById('registrar-receta');
                if (registrar) {
                    registrar.href = urlRegistroReceta(item);
                }
                bloqueReceta.hidden = false;
                actualizarInformacionReceta();
                recetaProducto.focus();
            }

            function urlRegistroReceta(item = null) {
                const url = new URL(recetasTarjeta.dataset.registroUrl, window.location.origin);
                if (pacienteInput.value) url.searchParams.set('paciente_id', pacienteInput.value);
                if (item) url.searchParams.set('producto_id', item.producto_id);
                url.searchParams.set('origen', 'venta');
                return url.toString();
            }

            function actualizarRecetasPaciente() {
                const paciente = obtenerPacienteActual();
                const recetas = recetasPaciente.filter(r => String(r.paciente_id) === pacienteInput.value)
                    .sort((a, b) => Number(b.disponible) - Number(a.disponible));
                const aprobadas = recetas.filter(r => r.disponible).length;
                const pendientes = recetas.filter(r => r.estado === 'Pendiente').length;
                document.getElementById('recetas-paciente').textContent = paciente
                    ? `${paciente.nombre} · RUT: ${paciente.rut}${verRecetasPaciente ? ` · ${aprobadas} recetas aprobadas disponibles · ${pendientes} pendientes de revisión` : ''}`
                    : 'Selecciona un paciente para consultar o registrar sus recetas.';
                if (!verRecetasPaciente) return;
                document.getElementById('recetas-acciones').classList.toggle('d-none', !paciente);
                const registrar = document.getElementById('registrar-receta-paciente');
                if (paciente) registrar.href = urlRegistroReceta();
                else registrar.removeAttribute('href');
                const lista = document.getElementById('recetas-paciente-lista');
                lista.innerHTML = '';
                if (!paciente) return;
                if (!recetas.length) lista.textContent = 'Este paciente no tiene recetas registradas.';
                recetas.forEach(receta => {
                    lista.insertAdjacentHTML('beforeend', `
                        <div class="border-top py-2">
                            <strong>${escaparHtml(receta.numero)}</strong> · ${escaparHtml(receta.estado === 'Pendiente' ? 'Pendiente de validación' : receta.estado)}
                            ${receta.disponible ? '<span class="small text-success"> · Disponible para dispensar</span>' : ''}
                            <div class="small text-muted">Emisión: ${escaparHtml(receta.emision)} · Vence: ${escaparHtml(receta.vencimiento)} · ${escaparHtml(receta.medico)}</div>
                            <a href="${escaparHtml(receta.url)}" target="_blank" rel="noopener" class="small">Ver detalle (otra pestaña)</a>
                        </div>
                    `);
                });
            }

            function renderRecetas() {
                const conReceta = carrito.filter(item => item.requiere_receta);
                recetasResumen.textContent = conReceta.length
                    ? (conReceta.every(recetaValidaItem)
                        ? '✓ Todas las recetas requeridas están asociadas.'
                        : `${conReceta.filter(item => !recetaValidaItem(item)).length} medicamentos requieren asociar una receta válida.`)
                    : 'No requerida para los productos actuales.';
                recetasProductos.innerHTML = '';
                carrito.forEach((item, index) => {
                    if (!item.requiere_receta) return;
                    const receta = recetaDeItem(item);
                    const valida = recetaValidaItem(item);
                    recetasProductos.insertAdjacentHTML('beforeend', `
                        <div class="border-top pt-3 mt-3">
                            <strong class="d-block">${escaparHtml(item.nombre)}</strong>
                            <span class="badge text-bg-${valida ? 'success' : 'warning'} mt-1">${valida ? '✓ Receta válida' : '⚠ Requiere receta'}</span>
                            ${receta ? `
                                <div class="small mt-1">${escaparHtml(receta.numero)} · Aprobada · vence ${escaparHtml(receta.vencimiento)}</div>
                                <div class="small text-muted">Autorizado: ${receta.cantidad_prescrita} · Disponible: ${receta.disponible} · Venta actual: ${item.cantidad}</div>
                                ${!valida ? `<div class="small text-danger">La receta solo permite dispensar ${receta.disponible} unidades adicionales.</div>` : ''}
                            ` : '<div class="small text-muted mt-1">Sin receta asociada</div>'}
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-buscar-receta="${index}">${receta ? 'Cambiar receta' : 'Seleccionar receta'}</button>
                                ${!receta && obtenerPacienteActual() && recetasTarjeta.dataset.registroUrl ? `<a href="${escaparHtml(urlRegistroReceta(item))}" data-registrar-receta class="btn btn-sm btn-outline-secondary">Registrar nueva receta</a>` : ''}
                            </div>
                        </div>
                    `);
                });
            }

            function escaparHtml(texto) {
                const span = document.createElement('span'); span.textContent = texto;
                return span.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function obtenerConvenioActual() {
                const paciente = obtenerPacienteActual();
                return paciente?.convenio_id
                    ? convenios[paciente.convenio_id] ?? null
                    : null;
            }

            function mostrarPaciente(mensaje = 'La venta puede continuar como consumidor final. Para usar recetas, selecciona un paciente.') {
                const paciente = obtenerPacienteActual();
                const convenio = obtenerConvenioActual();

                if (pacienteRecetas !== pacienteInput.value) {
                    if (recetasPacientePanel) {
                        recetasPacientePanel.hidden = true;
                        verRecetasPaciente.setAttribute('aria-expanded', 'false');
                    }
                    const teniaRecetas = carrito.some(item => item.receta_detalle_id);
                    carrito.forEach(item => { item.receta_detalle_id = null; });
                    recetasMensaje.textContent = teniaRecetas
                        ? 'Se quitaron las recetas seleccionadas porque cambió el paciente.' : '';
                    pacienteRecetas = pacienteInput.value;
                }

                pacienteResultado.textContent = paciente
                    ? `✓ ${paciente.nombre} · RUT: ${paciente.rut} · Convenio: ${convenio?.nombre ?? 'Sin convenio'}`
                    : mensaje;

                actualizarRecetasPaciente();
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
                    alert('La cantidad supera el stock disponible en la ubicación de venta del producto.');
                    return;
                }

                if (existente) {
                    existente.cantidad = cantidadTotal;
                } else {
                    carrito.push({
                        producto_id: productoId,
                        nombre: opcion.dataset.nombre,
                        cantidad,
                        precio,
                        requiere_receta: opcion.dataset.requiereReceta === '1',
                        tipo_receta: opcion.dataset.tipoReceta,
                        receta_detalle_id: null
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
                indiceReceta = null;
                bloqueReceta.hidden = true;
                carritoFilas.innerHTML = '';
                detallesOcultos.innerHTML = '';
                renderRecetas();

                if (!carrito.length) {
                    carritoFilas.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                Aún no hay productos agregados.
                            </td>
                        </tr>
                    `;

                    totalAproximado.textContent = '0';
                    btnConfirmar.disabled = true;
                    return;
                }

                let total = 0;
                let recetasValidas = true;

                carrito.forEach((item, index) => {
                    const receta = item.requiere_receta ? recetaDeItem(item) : null;
                    const recetaValida = item.requiere_receta && recetaValidaItem(item);
                    if (item.requiere_receta && !recetaValida) recetasValidas = false;
                    const estimacion = calcularEstimacion(item.producto_id, item.precio);
                    const subtotalNeto = estimacion.precioFinal * item.cantidad;

                    total += subtotalNeto * 1.19;

                    carritoFilas.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${escaparHtml(item.nombre)}</td>
                            <td>${item.cantidad}</td>
                            <td>$${Math.round(item.precio).toLocaleString('es-CL')}</td>
                            <td>${item.requiere_receta ? `
                                <span class="badge text-bg-${recetaValida ? 'success' : 'warning'}">${recetaValida ? 'Receta seleccionada' : 'Requiere receta'}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-buscar-receta="${index}">${receta ? 'Cambiar receta' : 'Buscar receta'}</button>` : 'No requiere'}</td>
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
                        ${item.receta_detalle_id ? `<input type="hidden" name="detalles[${index}][receta_detalle_id]" value="${Number(item.receta_detalle_id)}">` : ''}
                    `);
                });

                totalAproximado.textContent =
                    Math.round(total).toLocaleString('es-CL');

                btnConfirmar.disabled = !recetasValidas;
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

                if (event.key === 'F4' && !event.altKey && !event.ctrlKey && !event.metaKey) {
                    event.preventDefault();
                    abrirRecetas();
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
                const buscar = event.target.closest('button[data-buscar-receta]');
                if (buscar) {
                    abrirRecetas(Number(buscar.dataset.buscarReceta));
                    return;
                }
                const boton = event.target.closest('button[data-index]');
                if (!boton) return;

                carrito.splice(Number(boton.dataset.index), 1);
                renderCarrito();
            });

            verRecetasPaciente?.addEventListener('click', () => {
                if (!requerirPacienteRecetas()) return;
                if (recetasPacientePanel.hidden) abrirRecetasPaciente();
                else {
                    recetasPacientePanel.hidden = true;
                    verRecetasPaciente.setAttribute('aria-expanded', 'false');
                }
            });
            recetasProductos.addEventListener('click', event => {
                const buscar = event.target.closest('button[data-buscar-receta]');
                if (buscar) abrirRecetas(Number(buscar.dataset.buscarReceta));
                if (event.target.closest('a[data-registrar-receta]') && !obtenerPacienteActual()) {
                    event.preventDefault();
                    abrirRecetas();
                }
            });
            recetaProducto.addEventListener('change', actualizarInformacionReceta);
            document.getElementById('cerrar-busqueda-receta').addEventListener('click', () => {
                bloqueReceta.hidden = true; indiceReceta = null;
            });
            asociarReceta.addEventListener('click', () => {
                const item = carrito[indiceReceta];
                if (!item || !obtenerPacienteActual()) return;
                const receta = recetasParaProducto(item.producto_id, item.tipo_receta)
                    .find(r => String(r.id) === recetaProducto.value && r.disponible >= item.cantidad);
                if (!receta) return;
                item.receta_detalle_id = receta.id;
                recetasMensaje.textContent = '';
                renderCarrito();
                codigoBarraInput.focus();
            });
            document.getElementById('form-venta').addEventListener('submit', event => {
                const pendiente = carrito.find(item => item.requiere_receta && !recetaValidaItem(item));
                if (!pendiente) return;
                event.preventDefault();
                recetasMensaje.textContent = `Falta asociar una receta válida para ${pendiente.nombre}.`;
                abrirRecetas();
            });

            const pacienteInicial = obtenerPacienteActual();

            pacienteRutInput.value = pacienteInicial?.rut ?? '';
            mostrarPaciente();
            codigoBarraInput.focus({ preventScroll: true });
        </script>

    @endif

</div>
@endsection
