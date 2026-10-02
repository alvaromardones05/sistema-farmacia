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
<script>
document.addEventListener('DOMContentLoaded', function () {

    const producto = document.getElementById('producto');
    const cantidad = document.getElementById('cantidad');
    const precio = document.getElementById('precio');
    const detalle = document.getElementById('detalleCompra');
    const totalCompra = document.getElementById('totalCompra');
    const form = document.getElementById('formCompra');

    let indice = 0;

    document.getElementById('agregarProducto').addEventListener('click', function () {

        if (!producto.value) {
            alert('Selecciona un producto.');
            return;
        }

        const cantidadValor = parseInt(cantidad.value);
        const precioValor = parseFloat(precio.value);

        if (!cantidadValor || cantidadValor < 1) {
            alert('Ingresa una cantidad válida.');
            return;
        }

        if (isNaN(precioValor) || precioValor < 0) {
            alert('Ingresa un precio válido.');
            return;
        }

        if (detalle.querySelector('[data-producto-id="' + producto.value + '"]')) {
            alert('Este producto ya fue agregado.');
            return;
        }

        const opcion = producto.options[producto.selectedIndex];

        document.getElementById('filaVacia')?.remove();

        const fila = document.createElement('tr');

        fila.dataset.productoId = producto.value;
        fila.dataset.subtotal = cantidadValor * precioValor;

        fila.innerHTML = `
            <td>
                <div class="fw-semibold">${opcion.dataset.nombre}</div>
                <div class="small text-muted">${opcion.dataset.codigo || ''}</div>

                <input
                    type="hidden"
                    name="detalles[${indice}][producto_id]"
                    value="${producto.value}"
                >
            </td>

            <td class="text-end">
                ${cantidadValor}

                <input
                    type="hidden"
                    name="detalles[${indice}][cantidad_solicitada]"
                    value="${cantidadValor}"
                >
            </td>

            <td class="text-end">
                $${precioValor.toLocaleString('es-CL')}

                <input
                    type="hidden"
                    name="detalles[${indice}][precio_unitario]"
                    value="${precioValor}"
                >
            </td>

            <td class="text-end fw-semibold">
                $${(cantidadValor * precioValor).toLocaleString('es-CL')}
            </td>

            <td class="text-end">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger eliminar"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        detalle.appendChild(fila);

        indice++;

        producto.value = '';
        cantidad.value = 1;
        precio.value = '';

        actualizarTotal();
    });

    detalle.addEventListener('click', function (event) {

        const boton = event.target.closest('.eliminar');

        if (!boton) return;

        boton.closest('tr').remove();

        if (!detalle.querySelector('tr')) {
            detalle.innerHTML = `
                <tr id="filaVacia">
                    <td colspan="5" class="text-center py-4 text-muted">
                        No has agregado productos.
                    </td>
                </tr>
            `;
        }

        actualizarTotal();
    });

    form.addEventListener('submit', function (event) {

        if (!detalle.querySelector('[data-producto-id]')) {
            event.preventDefault();
            alert('Debes agregar al menos un producto.');
        }
    });

    function actualizarTotal() {

        let total = 0;

        detalle.querySelectorAll('[data-producto-id]').forEach(function (fila) {
            total += parseFloat(fila.dataset.subtotal);
        });

        totalCompra.textContent = '$' + total.toLocaleString('es-CL');
    }

});
</script>
@endsection