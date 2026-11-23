@extends('layouts.app')

@section('title', 'Nueva orden de compra')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
            <h1 class="h3 mb-1">Nueva orden de compra</h1>
            <p class="text-muted mb-0">Registra los productos solicitados a un proveedor.</p>
        </div>

        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    @if($errors->has('orden'))
        <div class="alert alert-danger">
            {{ $errors->first('orden') }}
        </div>
    @endif

    @if($proveedores->isEmpty())
        <div class="alert alert-warning">
            No existen proveedores activos.
            <a href="{{ route('compras.proveedores.create') }}" class="alert-link">
                Registrar proveedor
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('compras.store') }}" id="formCompra">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Datos de la orden</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Proveedor *</label>

                        <select
                            name="proveedor_id"
                            class="form-select @error('proveedor_id') is-invalid @enderror"
                            required
                        >
                            <option value="">Seleccione un proveedor</option>

                            @foreach($proveedores as $proveedor)
                                <option
                                    value="{{ $proveedor->id }}"
                                    {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}
                                >
                                    {{ $proveedor->nombre_razon_social }} — {{ $proveedor->rut }}
                                </option>
                            @endforeach
                        </select>

                        @error('proveedor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fecha entrega esperada</label>

                        <input
                            type="date"
                            name="fecha_entrega_esperada"
                            class="form-control"
                            value="{{ old('fecha_entrega_esperada') }}"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observaciones</label>

                        <textarea
                            name="observaciones"
                            class="form-control"
                            rows="2"
                            placeholder="Información adicional de la orden..."
                        >{{ old('observaciones') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Agregar productos</h5>
            </div>

            <div class="card-body">

                <div class="row g-3 align-items-end">

                    <div class="col-md-6">
                        <label class="form-label">Producto</label>

                        <select id="producto" class="form-select">
                            <option value="">Seleccione un producto</option>

                            @foreach($productos as $producto)
                                <option
                                    value="{{ $producto->id }}"
                                    data-nombre="{{ $producto->nombre }}"
                                    data-codigo="{{ $producto->codigo_interno }}"
                                >
                                    {{ $producto->nombre }}
                                    @if($producto->codigo_interno)
                                        — {{ $producto->codigo_interno }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Cantidad</label>

                        <input
                            type="number"
                            id="cantidad"
                            class="form-control"
                            min="1"
                            value="1"
                        >
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Precio compra</label>

                        <input
                            type="number"
                            id="precio"
                            class="form-control"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="col-md-2 d-grid">
                        <button
                            type="button"
                            id="agregarProducto"
                            class="btn btn-outline-primary"
                        >
                            <i class="bi bi-plus-lg me-1"></i>
                            Agregar
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Productos solicitados</h5>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio unitario</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="detalleCompra">
                        <tr id="filaVacia">
                            <td colspan="5" class="text-center py-4 text-muted">
                                No has agregado productos.
                            </td>
                        </tr>
                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total estimado</th>
                            <th class="text-end" id="totalCompra">$0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">

            <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">
                Cancelar
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                {{ $proveedores->isEmpty() ? 'disabled' : '' }}
            >
                <i class="bi bi-check-lg me-1"></i>
                Crear orden de compra
            </button>

        </div>

    </form>

</div>
@endsection

@section('scripts')
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const producto     = document.getElementById('producto');
    const cantidad     = document.getElementById('cantidad');
    const precio       = document.getElementById('precio');
    const detalle      = document.getElementById('detalleCompra');
    const totalCompra  = document.getElementById('totalCompra');
    const form         = document.getElementById('formCompra');
    const btnAgregar   = document.getElementById('agregarProducto');

    // Filas ya existentes por old() cuando falla la validación
    const detallesExistentes = @json(old('detalles', []));
    let indice = 0;

    // ============================================================
    // HELPERS SEGUROS (sin innerHTML con datos de usuario)
    // ============================================================

    function crearFila(productoId, nombre, codigo, cant, prec) {
        const idx = indice;

        const fila = document.createElement('tr');
        fila.dataset.productoId = productoId;
        fila.dataset.subtotal   = cant * prec;

        // Celda producto
        const tdProducto = document.createElement('td');
        const divNombre = document.createElement('div');
        divNombre.className = 'fw-semibold';
        divNombre.textContent = nombre;  // ← textContent, seguro
        const divCodigo = document.createElement('div');
        divCodigo.className = 'small text-muted';
        divCodigo.textContent = codigo || '';

        const inputProd = document.createElement('input');
        inputProd.type = 'hidden';
        inputProd.name = `detalles[${idx}][producto_id]`;
        inputProd.value = productoId;

        tdProducto.appendChild(divNombre);
        tdProducto.appendChild(divCodigo);
        tdProducto.appendChild(inputProd);

        // Celda cantidad
        const tdCantidad = document.createElement('td');
        tdCantidad.className = 'text-end';
        tdCantidad.textContent = cant;
        const inputCant = document.createElement('input');
        inputCant.type = 'hidden';
        inputCant.name = `detalles[${idx}][cantidad_solicitada]`;
        inputCant.value = cant;
        tdCantidad.appendChild(inputCant);

        // Celda precio
        const tdPrecio = document.createElement('td');
        tdPrecio.className = 'text-end';
        tdPrecio.textContent = '$' + prec.toLocaleString('es-CL');
        const inputPrec = document.createElement('input');
        inputPrec.type = 'hidden';
        inputPrec.name = `detalles[${idx}][precio_unitario]`;
        inputPrec.value = prec;
        tdPrecio.appendChild(inputPrec);

        // Celda subtotal
        const tdSubtotal = document.createElement('td');
        tdSubtotal.className = 'text-end fw-semibold';
        tdSubtotal.textContent = '$' + (cant * prec).toLocaleString('es-CL');

        // Celda acciones
        const tdAcciones = document.createElement('td');
        tdAcciones.className = 'text-end';
        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.className = 'btn btn-sm btn-outline-danger eliminar';
        const icono = document.createElement('i');
        icono.className = 'bi bi-trash';
        btnEliminar.appendChild(icono);
        tdAcciones.appendChild(btnEliminar);

        fila.appendChild(tdProducto);
        fila.appendChild(tdCantidad);
        fila.appendChild(tdPrecio);
        fila.appendChild(tdSubtotal);
        fila.appendChild(tdAcciones);

        indice++;
        return fila;
    }

    function obtenerDatosProducto(productoId) {
        const opt = Array.from(producto.options).find(o => o.value == productoId);
        if (!opt) return null;
        return { nombre: opt.dataset.nombre, codigo: opt.dataset.codigo || '' };
    }

    // ============================================================
    // 5.1 — REPOBLAR FILAS SI FALLÓ LA VALIDACIÓN
    // ============================================================

    if (Array.isArray(detallesExistentes) && detallesExistentes.length > 0) {
        document.getElementById('filaVacia')?.remove();

        detallesExistentes.forEach(function (det) {
            const datos = obtenerDatosProducto(det.producto_id);
            if (!datos) return; // producto ya no existe

            const cant = parseInt(det.cantidad_solicitada) || 1;
            const prec = parseFloat(det.precio_unitario) || 0;
            const fila = crearFila(det.producto_id, datos.nombre, datos.codigo, cant, prec);

            detalle.appendChild(fila);
        });

        actualizarTotal();
    }

    // ============================================================
    // AGREGAR PRODUCTO
    // ============================================================

    function agregarProducto() {
        if (!producto.value) {
            alert('Selecciona un producto.');
            producto.focus();
            return false;
        }

        const cantidadValor = parseInt(cantidad.value);
        const precioValor   = parseFloat(precio.value);

        if (!cantidadValor || cantidadValor < 1) {
            alert('Ingresa una cantidad válida.');
            cantidad.focus();
            return false;
        }

        if (isNaN(precioValor) || precioValor < 0) {
            alert('Ingresa un precio válido.');
            precio.focus();
            return false;
        }

        if (detalle.querySelector('[data-producto-id="' + producto.value + '"]')) {
            alert('Este producto ya fue agregado.');
            producto.focus();
            return false;
        }

        const datos = obtenerDatosProducto(producto.value);
        if (!datos) return false;

        document.getElementById('filaVacia')?.remove();

        const fila = crearFila(producto.value, datos.nombre, datos.codigo, cantidadValor, precioValor);
        detalle.appendChild(fila);

        producto.value = '';
        cantidad.value = 1;
        precio.value = '';

        actualizarTotal();
        producto.focus(); // ← 5.5: vuelve al buscador
        return true;
    }

    btnAgregar.addEventListener('click', agregarProducto);

    // ============================================================
    // 5.5 — FLUJO CON TECLADO
    // ============================================================

    producto.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            cantidad.focus();
            cantidad.select();
        }
    });

    cantidad.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            precio.focus();
            precio.select();
        }
    });

    precio.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            agregarProducto();
        }
    });

    // ============================================================
    // ELIMINAR Y TOTAL
    // ============================================================

    detalle.addEventListener('click', function (event) {
        const boton = event.target.closest('.eliminar');
        if (!boton) return;

        boton.closest('tr').remove();

        if (!detalle.querySelector('tr[data-producto-id]')) {
            const filaVacia = document.createElement('tr');
            filaVacia.id = 'filaVacia';
            const td = document.createElement('td');
            td.colSpan = 5;
            td.className = 'text-center py-4 text-muted';
            td.textContent = 'No has agregado productos.';
            filaVacia.appendChild(td);
            detalle.appendChild(filaVacia);
        }

        actualizarTotal();
    });

    function actualizarTotal() {
        let total = 0;
        detalle.querySelectorAll('tr[data-producto-id]').forEach(function (fila) {
            total += parseFloat(fila.dataset.subtotal) || 0;
        });
        totalCompra.textContent = '$' + total.toLocaleString('es-CL');
    }

    form.addEventListener('submit', function (event) {
        if (!detalle.querySelector('tr[data-producto-id]')) {
            event.preventDefault();
            alert('Debes agregar al menos un producto.');
        }
    });

});
</script>
@endsection
@endsection
