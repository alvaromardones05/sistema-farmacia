@extends('layouts.app')

@section('title', 'Nueva venta')

@section('content')
@include('inventario._styles') {{-- reutiliza el CSS del modal-custom y estilos base --}}
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
        {{-- Sin caja abierta: se resuelve AQUÍ MISMO, sin salir de Ventas --}}
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
                                <option value="{{ $caja->id }}">{{ $caja->descripcion }} ({{ $caja->numero_caja }})</option>
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
            No existe una ubicación de tipo "vitrina" configurada. No se puede vender sin eso.
        </div>

    @else
        {{-- Formulario de venta --}}
        <form action="{{ route('ventas.store') }}" method="POST" id="form-venta">
            @csrf

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Paciente *</label>
                            <select name="paciente_id" class="form-select" required>
                                <option value="">Selecciona un paciente</option>
                                @foreach ($pacientes as $paciente)
                                    <option value="{{ $paciente->id }}">{{ $paciente->nombreCompleto() }} ({{ $paciente->rut }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Convenio (opcional)</label>
                            <select name="convenio_id" class="form-select">
                                <option value="">Particular / sin convenio</option>
                                @foreach ($convenios as $convenio)
                                    <option value="{{ $convenio->id }}">{{ $convenio->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Agregar producto</strong></div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Producto</label>
                            <select id="producto-select" class="form-select">
                                <option value="">Selecciona un producto</option>
                                @foreach ($productos as $producto)
                                    <option
                                        value="{{ $producto['id'] }}"
                                        data-nombre="{{ $producto['nombre'] }}"
                                        data-precio="{{ $producto['precio_neto'] }}"
                                        data-disponible="{{ $producto['disponible'] }}"
                                        @if (!$producto['tiene_precio'] || $producto['disponible'] <= 0) disabled @endif
                                    >
                                        {{ $producto['nombre'] }} ({{ $producto['codigo'] }})
                                        @if (!$producto['tiene_precio']) — sin precio @endif
                                        @if ($producto['disponible'] <= 0) — sin stock @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Precio</label>
                            <input type="text" id="producto-precio" class="form-control" disabled>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="producto-cantidad" class="form-control" min="1" value="1" disabled>
                            <small class="text-muted">Disp: <span id="producto-disponible">—</span></small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Descuento %</label>
                            <input type="number" id="producto-descuento" class="form-control" min="0" max="100" value="0" disabled>
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="btn-agregar" class="btn btn-primary w-100" disabled>+</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><strong>Detalle de la venta</strong></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unit.</th>
                                <th>Desc. %</th>
                                <th>Subtotal aprox.</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="carrito-filas">
                            <tr id="carrito-vacio">
                                <td colspan="6" class="text-center text-muted py-3">Aún no hay productos agregados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-end">
                    <strong>Total aproximado: $<span id="total-aproximado">0</span></strong>
                    <small class="text-muted d-block">El total final (con IVA) se calcula al confirmar.</small>
                </div>
            </div>

            <div id="detalles-ocultos"></div>

            <button type="submit" class="btn btn-success btn-lg" id="btn-confirmar" disabled>
                Confirmar venta
            </button>
        </form>

        <script>
            let carrito = [];

            const productoSelect = document.getElementById('producto-select');
            const precioInput = document.getElementById('producto-precio');
            const cantidadInput = document.getElementById('producto-cantidad');
            const descuentoInput = document.getElementById('producto-descuento');
            const disponibleSpan = document.getElementById('producto-disponible');
            const btnAgregar = document.getElementById('btn-agregar');

            productoSelect.addEventListener('change', function () {
                const opcion = this.options[this.selectedIndex];
                const habilitado = !!this.value;

                [precioInput, cantidadInput, descuentoInput, btnAgregar].forEach(el => el.disabled = !habilitado);

                if (habilitado) {
                    precioInput.value = opcion.dataset.precio;
                    disponibleSpan.textContent = opcion.dataset.disponible;
                    cantidadInput.max = opcion.dataset.disponible;
                    cantidadInput.value = 1;
                } else {
                    precioInput.value = '';
                    disponibleSpan.textContent = '—';
                }
            });

            btnAgregar.addEventListener('click', function () {
                const opcion = productoSelect.options[productoSelect.selectedIndex];
                const cantidad = parseInt(cantidadInput.value || '0', 10);
                const descuento = parseFloat(descuentoInput.value || '0');
                const precio = parseFloat(opcion.dataset.precio);
                const disponible = parseInt(opcion.dataset.disponible, 10);

                if (!cantidad || cantidad < 1) return;
                if (cantidad > disponible) {
                    alert('Cantidad supera el stock disponible en vitrina.');
                    return;
                }

                carrito.push({
                    producto_id: productoSelect.value,
                    nombre: opcion.dataset.nombre,
                    cantidad: cantidad,
                    precio: precio,
                    descuento: descuento,
                });

                renderCarrito();

                productoSelect.value = '';
                productoSelect.dispatchEvent(new Event('change'));
            });

            function renderCarrito() {
                const filas = document.getElementById('carrito-filas');
                const ocultos = document.getElementById('detalles-ocultos');
                filas.innerHTML = '';
                ocultos.innerHTML = '';

                if (carrito.length === 0) {
                    filas.innerHTML = '<tr id="carrito-vacio"><td colspan="6" class="text-center text-muted py-3">Aún no hay productos agregados.</td></tr>';
                    document.getElementById('btn-confirmar').disabled = true;
                    document.getElementById('total-aproximado').textContent = '0';
                    return;
                }

                let total = 0;

                carrito.forEach((item, index) => {
                    const subtotal = item.precio * item.cantidad * (1 - item.descuento / 100);
                    total += subtotal;

                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${item.nombre}</td>
                        <td>${item.cantidad}</td>
                        <td>$${item.precio.toLocaleString('es-CL')}</td>
                        <td>${item.descuento}%</td>
                        <td>$${Math.round(subtotal).toLocaleString('es-CL')}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" data-index="${index}">Quitar</button></td>
                    `;
                    filas.appendChild(fila);

                    ['producto_id', 'cantidad', 'descuento'].forEach(campo => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `detalles[${index}][${campo === 'descuento' ? 'descuento_pct' : campo}]`;
                        input.value = item[campo];
                        ocultos.appendChild(input);
                    });
                });

                document.getElementById('total-aproximado').textContent = Math.round(total).toLocaleString('es-CL');
                document.getElementById('btn-confirmar').disabled = false;
            }

            document.getElementById('carrito-filas').addEventListener('click', function (e) {
                const boton = e.target.closest('button[data-index]');
                if (!boton) return;
                carrito.splice(parseInt(boton.dataset.index, 10), 1);
                renderCarrito();
            });
        </script>
    @endif

</div>
@endsection