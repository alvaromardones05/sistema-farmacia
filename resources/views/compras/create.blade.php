@extends('layouts.app')

@section('title', 'Nueva orden de compra')

@section('content')
@php
    $catalogo = $productos->map(fn ($p) => [
        'id'      => $p->id,
        'nombre'  => $p->nombre,
        'codigo'  => $p->codigo_interno,
        'barra'   => $p->codigo_barra,
        'activo'  => $p->principio_activo,
        'stock'   => (int) ($p->stock_actual ?? 0),
        'minimo'  => (int) $p->stock_minimo,
        'critico' => (int) $p->stock_critico,
        'precio'  => $p->ultimo_precio ?? null,
    ])->values();

    $erroresDetalle = collect($errors->get('detalles.*'))->flatten()
        ->merge($errors->get('detalles'))->unique();
@endphp

<style>
    @media (min-width: 1200px) {
        .panel-orden { position: sticky; top: 1rem; }
    }
    #listaCatalogo { max-height: 60vh; overflow-y: auto; }
    #detalleWrap   { max-height: 36vh; overflow-y: auto; }
    #detalleWrap thead th { position: sticky; top: 0; z-index: 1; }
    .fila-agregada { background-color: #f8f9fa; }
    .fila-agregada .info { opacity: .55; }
</style>

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Compras / Recepciones</p>
            <h1 class="h3 mb-1">Nueva orden de compra</h1>
            <p class="text-muted mb-0">Elige los productos del catálogo y ajusta cantidades y precios en la orden.</p>
        </div>

        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    @if($errors->has('orden'))
        <div class="alert alert-danger">{{ $errors->first('orden') }}</div>
    @endif

    @if($erroresDetalle->isNotEmpty())
        <div class="alert alert-danger">
            <strong>Revisa los productos de la orden:</strong>
            <ul class="mb-0">
                @foreach($erroresDetalle as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($proveedores->isEmpty())
        <div class="alert alert-warning">
            No existen proveedores activos.
            <a href="{{ route('compras.proveedores.create') }}" class="alert-link">Registrar proveedor</a>
        </div>
    @endif

    <form method="POST" action="{{ route('compras.store') }}" id="formCompra">
        @csrf

        <div class="row g-4">

            {{-- ===================== CATÁLOGO ===================== --}}
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Catálogo de productos</h5>
                            <button type="button" id="btnAgregarBajos" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-lightning-charge me-1"></i>
                                Agregar todos los de bajo stock
                            </button>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="search" id="buscar" class="form-control"
                                   placeholder="Buscar por nombre, código, código de barras o principio activo..."
                                   autocomplete="off">
                        </div>

                        <div class="d-flex flex-wrap gap-2" id="chips">
                            <button type="button" class="btn btn-sm" data-filtro="bajo">
                                Bajo stock <span class="badge bg-light text-dark ms-1" id="cntBajo">0</span>
                            </button>
                            <button type="button" class="btn btn-sm" data-filtro="sin">
                                Sin stock <span class="badge bg-light text-dark ms-1" id="cntSin">0</span>
                            </button>
                            <button type="button" class="btn btn-sm" data-filtro="todos">
                                Todos <span class="badge bg-light text-dark ms-1" id="cntTodos">0</span>
                            </button>
                        </div>
                    </div>

                    <div id="listaCatalogo">
                        <ul class="list-group list-group-flush" id="catalogo"></ul>
                        <div id="sinResultados" class="text-center text-muted py-4 d-none">
                            No hay productos que coincidan.
                        </div>
                    </div>

                    <div class="card-footer bg-white small text-muted" id="contadorLista"></div>
                </div>
            </div>

            {{-- ===================== ORDEN ===================== --}}
            <div class="col-xl-5">
                <div class="panel-orden">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Orden de compra</h5>
                        </div>

                        <div class="card-body border-bottom">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label mb-1">Proveedor *</label>
                                    <select name="proveedor_id"
                                            class="form-select @error('proveedor_id') is-invalid @enderror"
                                            required>
                                        <option value="">Seleccione un proveedor</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}"
                                                {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                                {{ $proveedor->nombre_razon_social }} — {{ $proveedor->rut }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('proveedor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label mb-1">Entrega esperada</label>
                                    <input type="date" name="fecha_entrega_esperada" class="form-control"
                                           value="{{ old('fecha_entrega_esperada') }}">
                                </div>

                                <div class="col-md-7">
                                    <label class="form-label mb-1">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="1"
                                              placeholder="Información adicional...">{{ old('observaciones') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-3 pt-3 pb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Productos solicitados</span>
                            <span class="badge text-bg-secondary" id="contadorOrden">0</span>
                        </div>

                        <div id="detalleWrap" class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-end">Cant.</th>
                                        <th class="text-end">Precio unit.</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="detalleCompra">
                                    <tr id="filaVacia">
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            Agrega productos desde el catálogo.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold">Total estimado</span>
                                <span class="h5 mb-0" id="totalCompra">$0</span>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" id="btnCrear" class="btn btn-primary"
                                        {{ $proveedores->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bi bi-check-lg me-1"></i>
                                    Crear orden de compra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const CATALOGO = @json($catalogo);
    const detallesExistentes = @json(old('detalles', []));

    const buscar         = document.getElementById('buscar');
    const catalogoUl     = document.getElementById('catalogo');
    const sinResultados  = document.getElementById('sinResultados');
    const contadorLista  = document.getElementById('contadorLista');
    const detalle        = document.getElementById('detalleCompra');
    const filaVacia      = document.getElementById('filaVacia');
    const totalCompra    = document.getElementById('totalCompra');
    const contadorOrden  = document.getElementById('contadorOrden');
    const form           = document.getElementById('formCompra');
    const btnCrear       = document.getElementById('btnCrear');
    const btnBajos       = document.getElementById('btnAgregarBajos');
    const chips          = document.querySelectorAll('#chips [data-filtro]');

    const RANGO = { sin: 0, critico: 1, bajo: 2, ok: 3 };
    const ETIQUETA = {
        sin:     { texto: 'Sin stock', clase: 'text-bg-danger' },
        critico: { texto: 'Crítico',   clase: 'text-bg-danger' },
        bajo:    { texto: 'Bajo',      clase: 'text-bg-warning' },
        ok:      { texto: 'Normal',    clase: 'text-bg-light border text-muted' },
    };

    const porId     = new Map();   // id -> producto
    const filas     = new Map();   // id -> { li, btn, icono }
    const agregados = new Map();   // id -> <tr> de la orden
    let indice = 0;
    let filtro = 'bajo';
    let termino = [];

    // ---------- helpers ----------
    const normalizar = s => (s || '').toString().normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const fmt = n => '$' + Math.round(n).toLocaleString('es-CL');

    function el(tag, clase, texto) {
        const e = document.createElement(tag);
        if (clase) e.className = clase;
        if (texto !== undefined) e.textContent = texto;
        return e;
    }

    function estadoDe(p) {
        if (p.stock <= 0) return 'sin';
        if (p.stock <= p.critico) return 'critico';
        if (p.stock <= p.minimo) return 'bajo';
        return 'ok';
    }

    // ---------- preparar catálogo ----------
    CATALOGO.forEach(function (p) {
        p.estado   = estadoDe(p);
        p.busqueda = normalizar([p.nombre, p.codigo, p.barra, p.activo].join(' '));
        p.sugerida = Math.max(p.minimo - p.stock, 1);
        porId.set(String(p.id), p);
    });

    CATALOGO.sort(function (a, b) {
        return (RANGO[a.estado] - RANGO[b.estado]) || a.nombre.localeCompare(b.nombre, 'es');
    });

    document.getElementById('cntBajo').textContent  = CATALOGO.filter(p => p.estado !== 'ok').length;
    document.getElementById('cntSin').textContent   = CATALOGO.filter(p => p.estado === 'sin').length;
    document.getElementById('cntTodos').textContent = CATALOGO.length;

    if (!CATALOGO.some(p => p.estado !== 'ok')) filtro = 'todos';

    // ---------- construir lista del catálogo (una sola vez) ----------
    CATALOGO.forEach(function (p) {
        const li = el('li', 'list-group-item py-2');
        const fila = el('div', 'd-flex align-items-center gap-2');

        const info = el('div', 'info flex-grow-1');
        info.style.minWidth = '0';
        info.appendChild(el('div', 'fw-semibold text-truncate', p.nombre));
        const sub = [p.codigo, p.activo].filter(Boolean).join(' · ');
        info.appendChild(el('div', 'small text-muted text-truncate', sub));

        const estado = el('div', 'info text-end flex-shrink-0');
        const et = ETIQUETA[p.estado];
        estado.appendChild(el('span', 'badge ' + et.clase, et.texto));
        estado.appendChild(el('div', 'small text-muted mt-1', 'Stock ' + p.stock + ' / mín. ' + p.minimo));

        const btn = el('button', 'btn btn-sm btn-outline-primary flex-shrink-0');
        btn.type = 'button';
        btn.title = 'Agregar a la orden';
        const icono = el('i', 'bi bi-plus-lg');
        btn.appendChild(icono);
        btn.addEventListener('click', function () { agregar(p.id); });

        fila.appendChild(info);
        fila.appendChild(estado);
        fila.appendChild(btn);
        li.appendChild(fila);
        catalogoUl.appendChild(li);

        filas.set(String(p.id), { li: li, btn: btn, icono: icono });
    });

    // ---------- filtros ----------
    function coincide(p) {
        if (termino.length) return termino.every(t => p.busqueda.includes(t));
        if (filtro === 'bajo') return p.estado !== 'ok';
        if (filtro === 'sin')  return p.estado === 'sin';
        return true;
    }

    function pintarChips() {
        chips.forEach(function (c) {
            const activo = c.dataset.filtro === filtro && !termino.length;
            c.classList.toggle('btn-primary', activo);
            c.classList.toggle('btn-outline-primary', !activo);
        });
    }

    function aplicarFiltros() {
        let visibles = 0;
        CATALOGO.forEach(function (p) {
            const ok = coincide(p);
            filas.get(String(p.id)).li.classList.toggle('d-none', !ok);
            if (ok) visibles++;
        });
        sinResultados.classList.toggle('d-none', visibles > 0);
        contadorLista.textContent = 'Mostrando ' + visibles + ' de ' + CATALOGO.length +
            (termino.length ? ' (búsqueda en todo el catálogo)' : '');
        pintarChips();
    }

    chips.forEach(function (c) {
        c.addEventListener('click', function () {
            filtro = c.dataset.filtro;
            buscar.value = '';
            termino = [];
            aplicarFiltros();
        });
    });

    buscar.addEventListener('input', function () {
        termino = normalizar(buscar.value).split(/\s+/).filter(Boolean);
        aplicarFiltros();
    });

    // Enter agrega el primer resultado disponible (también sirve con lector de código de barras)
    buscar.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            buscar.value = ''; termino = []; aplicarFiltros();
            return;
        }
        if (e.key !== 'Enter') return;
        e.preventDefault();

        const p = CATALOGO.find(x => coincide(x) && !agregados.has(String(x.id)));
        if (!p) return;

        const fila = agregar(p.id);
        buscar.value = '';
        termino = [];
        aplicarFiltros();
        if (fila) {
            const inp = fila.querySelector('.inp-cant');
            inp.focus({ preventScroll: true });
            inp.select();
        }
    });

    // ---------- orden ----------
    function marcarAgregado(key, si) {
        const f = filas.get(key);
        if (!f) return;
        f.li.classList.toggle('fila-agregada', si);
        f.btn.disabled = si;
        f.btn.className = 'btn btn-sm flex-shrink-0 ' + (si ? 'btn-success' : 'btn-outline-primary');
        f.icono.className = si ? 'bi bi-check-lg' : 'bi bi-plus-lg';
    }

    function crearFila(p, cant, precio) {
        const idx = indice++;
        const tr = el('tr');
        tr.dataset.productoId = p.id;

        const tdP = el('td');
        tdP.appendChild(el('div', 'fw-semibold', p.nombre));
        tdP.appendChild(el('div', 'small text-muted', p.codigo || ''));
        tdP.appendChild(el('div', 'small text-muted sub', ''));
        const hid = el('input');
        hid.type = 'hidden';
        hid.name = 'detalles[' + idx + '][producto_id]';
        hid.value = p.id;
        tdP.appendChild(hid);

        const tdC = el('td', 'text-end');
        const inpC = el('input', 'form-control form-control-sm text-end inp-cant');
        inpC.type = 'number';
        inpC.name = 'detalles[' + idx + '][cantidad_solicitada]';
        inpC.min = '1';
        inpC.step = '1';
        inpC.required = true;
        inpC.value = cant;
        inpC.style.width = '80px';
        tdC.appendChild(inpC);

        const tdPr = el('td', 'text-end');
        const inpP = el('input', 'form-control form-control-sm text-end inp-prec');
        inpP.type = 'number';
        inpP.name = 'detalles[' + idx + '][precio_unitario]';
        inpP.min = '0';
        inpP.step = '0.01';
        inpP.required = true;
        inpP.placeholder = '$';
        inpP.value = (precio === null || precio === undefined) ? '' : precio;
        inpP.style.width = '110px';
        tdPr.appendChild(inpP);

        const tdA = el('td', 'text-end');
        const btnE = el('button', 'btn btn-sm btn-outline-danger eliminar');
        btnE.type = 'button';
        btnE.title = 'Quitar';
        btnE.appendChild(el('i', 'bi bi-trash'));
        tdA.appendChild(btnE);

        tr.appendChild(tdP);
        tr.appendChild(tdC);
        tr.appendChild(tdPr);
        tr.appendChild(tdA);
        return tr;
    }

    function agregar(id, cant, precio) {
        const key = String(id);
        const p = porId.get(key);
        if (!p || agregados.has(key)) return null;

        const tr = crearFila(p, cant || p.sugerida, precio !== undefined ? precio : p.precio);
        detalle.appendChild(tr);
        agregados.set(key, tr);
        marcarAgregado(key, true);
        actualizarTotal();
        return tr;
    }

    function actualizarTotal() {
        let total = 0;
        agregados.forEach(function (tr) {
            const c = parseFloat(tr.querySelector('.inp-cant').value) || 0;
            const pr = parseFloat(tr.querySelector('.inp-prec').value) || 0;
            tr.querySelector('.sub').textContent = 'Subtotal: ' + fmt(c * pr);
            total += c * pr;
        });
        totalCompra.textContent = fmt(total);
        contadorOrden.textContent = agregados.size;
        filaVacia.classList.toggle('d-none', agregados.size > 0);
    }

    detalle.addEventListener('input', function (e) {
        if (e.target.matches('.inp-cant, .inp-prec')) actualizarTotal();
    });

    // Teclado: Enter en cantidad -> precio; Enter en precio -> buscador
    detalle.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        if (e.target.matches('.inp-cant')) {
            e.preventDefault();
            const sig = e.target.closest('tr').querySelector('.inp-prec');
            sig.focus(); sig.select();
        } else if (e.target.matches('.inp-prec')) {
            e.preventDefault();
            buscar.focus(); buscar.select();
        }
    });

    detalle.addEventListener('click', function (e) {
        const btn = e.target.closest('.eliminar');
        if (!btn) return;
        const tr = btn.closest('tr');
        const key = String(tr.dataset.productoId);
        tr.remove();
        agregados.delete(key);
        marcarAgregado(key, false);
        actualizarTotal();
    });

    // ---------- agregar todos los de bajo stock ----------
    btnBajos.addEventListener('click', function () {
        const candidatos = CATALOGO.filter(p => p.estado !== 'ok' && !agregados.has(String(p.id)));
        if (!candidatos.length) {
            alert('No hay productos bajo stock pendientes de agregar.');
            return;
        }
        if (!confirm('Se agregarán ' + candidatos.length + ' productos con la cantidad sugerida. ¿Continuar?')) return;
        candidatos.forEach(p => agregar(p.id));
    });

    // ---------- repoblar tras error de validación ----------
    if (Array.isArray(detallesExistentes) || (detallesExistentes && typeof detallesExistentes === 'object')) {
        Object.values(detallesExistentes).forEach(function (det) {
            if (!det || !det.producto_id) return;
            agregar(
                det.producto_id,
                parseInt(det.cantidad_solicitada) || 1,
                det.precio_unitario !== undefined ? det.precio_unitario : ''
            );
        });
    }

    // ---------- envío ----------
    form.addEventListener('submit', function (e) {
        if (agregados.size === 0) {
            e.preventDefault();
            alert('Debes agregar al menos un producto.');
            return;
        }
        btnCrear.disabled = true; // evita órdenes duplicadas por doble clic
    });

    aplicarFiltros();
    actualizarTotal();
});
</script>
@endsection
