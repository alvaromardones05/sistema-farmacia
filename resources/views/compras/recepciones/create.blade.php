@extends('layouts.app')

@section('title', 'Recibir mercadería')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">
                Compras / Recepciones
            </p>

            <h1 class="h3 mb-1">Recibir mercadería</h1>

            <p class="text-muted mb-0">
                Orden <strong>{{ $orden->numero_orden }}</strong>
                · {{ $orden->proveedor->nombre ?? 'Proveedor no informado' }}
            </p>
        </div>

        <a href="{{ route('compras.show', $orden) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    {{-- ERRORES --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">
                No se pudo registrar la recepción.
            </div>

            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ALERTA --}}
    <div class="alert alert-info border d-flex gap-2 mb-4">
        <i class="bi bi-shield-check fs-5 mt-1"></i>

        <div>
            <strong>Esta recepción quedará pendiente de revisión.</strong>
            El stock no ingresará al inventario hasta ser aprobado.
            Solo las <em>cantidades aceptadas</em> pasarán a stock,
            manteniendo la trazabilidad por lote.
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('compras.recepciones.store', $orden) }}"
        id="formRecepcion"
    >
        @csrf

        {{-- DATOS DE RECEPCIÓN --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Datos de recepción</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="numero_comprobante" class="form-label">
                            N° comprobante
                        </label>

                        <input
                            type="text"
                            id="numero_comprobante"
                            name="numero_comprobante"
                            class="form-control"
                            maxlength="100"
                            value="{{ old('numero_comprobante') }}"
                            placeholder="Factura, guía u otro documento"
                        >
                    </div>

                    <div class="col-md-8">
                        <label for="observaciones" class="form-label">
                            Observaciones generales
                        </label>

                        <input
                            type="text"
                            id="observaciones"
                            name="observaciones"
                            class="form-control"
                            value="{{ old('observaciones') }}"
                            placeholder="Opcional"
                        >
                    </div>

                </div>
            </div>
        </div>

        @php
            $detallesPendientes = $orden->detalles->filter(function ($detalle) {
                return
                    (int) $detalle->cantidad_solicitada >
                    (int) $detalle->cantidad_recibida;
            });
        @endphp

        {{-- TÍTULO --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                Productos pendientes

                <span class="badge text-bg-secondary ms-1">
                    {{ $detallesPendientes->count() }}
                </span>
            </h5>

            <small class="text-muted">
                Puedes dividir cada producto en varios lotes/series.
            </small>
        </div>

        @foreach ($detallesPendientes as $detalle)

            @php
                $pendiente = max(
                    0,
                    (int) $detalle->cantidad_solicitada -
                    (int) $detalle->cantidad_recibida
                );

                $esRefrigerado =
                    $detalle->producto->condicion_almacenamiento === 'refrigerado';

                $tipoUbicacion = $esRefrigerado
                    ? 'frigorifica'
                    : 'bodega';

                $nombreUbicacion =
                    $ubicacionesAlmacenamiento->get($tipoUbicacion)
                    ?? (
                        $esRefrigerado
                            ? 'Cámara Frigorífica'
                            : 'Bodega Central'
                    );

                $lotesOld = old("detalles.{$detalle->id}.lotes");

                if (!is_array($lotesOld) || count($lotesOld) === 0) {
                    $lotesOld = [[
                        'numero_lote' => '',
                        'cantidad_recibida' => 0,
                        'cantidad_rechazada' => 0,
                        'fecha_fabricacion' => '',
                        'fecha_vencimiento' => '',
                        'observacion' => '',
                    ]];
                }
            @endphp

            <div
                class="card border-0 shadow-sm mb-4 producto-card"
                data-pendiente="{{ $pendiente }}"
                data-detalle-id="{{ $detalle->id }}"
                data-next-index="{{ count($lotesOld) }}"
            >

                {{-- PRODUCTO --}}
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

                        <div>
                            <h6 class="mb-0">
                                {{ $detalle->producto->nombre }}
                            </h6>

                            <small class="text-muted">
                                Cód.
                                {{ $detalle->producto->codigo_interno
                                    ?? $detalle->producto->codigo_barra
                                    ?? 'Sin código' }}
                            </small>
                        </div>

                        <span class="badge text-bg-{{ $esRefrigerado ? 'info' : 'light' }} border badge-ubicacion">

                            <i class="bi {{ $esRefrigerado ? 'bi-snow' : 'bi-box-seam' }} me-1"></i>

                            Almacenar en
                            <strong>{{ $nombreUbicacion }}</strong>
                        </span>
                    </div>
                </div>

                {{-- RESUMEN --}}
                <div class="card-body py-2 info-linea border-bottom">
                    <div class="row text-center text-md-start g-2 resumen-linea">

                        <div class="col-4">
                            <div class="label">Solicitado</div>

                            <strong>
                                {{ $detalle->cantidad_solicitada }}
                            </strong>
                        </div>

                        <div class="col-4">
                            <div class="label">Ya recibido</div>

                            <strong>
                                {{ $detalle->cantidad_recibida }}
                            </strong>
                        </div>

                        <div class="col-4">
                            <div class="label">Pendiente</div>

                            <strong class="text-warning pendiente-original">
                                {{ $pendiente }}
                            </strong>
                        </div>

                    </div>
                </div>

                {{-- LOTES --}}
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th style="min-width:140px">Lote / Serie</th>
                                <th style="min-width:100px">Recibida</th>
                                <th style="min-width:100px">Aceptada</th>
                                <th style="min-width:100px">Rechazada</th>
                                <th style="min-width:140px">Fabricación</th>
                                <th style="min-width:140px">Vencimiento</th>
                                <th style="min-width:220px">Observación</th>
                                <th style="width:44px"></th>
                            </tr>
                        </thead>

                        <tbody class="lotes-body">

                            @foreach ($lotesOld as $indice => $lote)

                                @php
                                    $recibida = (int) ($lote['cantidad_recibida'] ?? 0);
                                    $rechazada = (int) ($lote['cantidad_rechazada'] ?? 0);
                                    $aceptada = max(0, $recibida - $rechazada);
                                @endphp

                                <tr class="lote-row">

                                    <td>
                                        <input
                                            type="text"
                                            name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][numero_lote]"
                                            class="form-control numero-lote"
                                            maxlength="100"
                                            value="{{ $lote['numero_lote'] ?? '' }}"
                                            placeholder="Ej. ABC001"
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][cantidad_recibida]"
                                            class="form-control cant-recibida"
                                            min="0"
                                            value="{{ $recibida }}"
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            class="form-control cant-aceptada"
                                            value="{{ $aceptada }}"
                                            readonly
                                            tabindex="-1"
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][cantidad_rechazada]"
                                            class="form-control cant-rechazada"
                                            min="0"
                                            value="{{ $rechazada }}"
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="date"
                                            name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][fecha_fabricacion]"
                                            class="form-control fecha-fabricacion"
                                            value="{{ $lote['fecha_fabricacion'] ?? '' }}"
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="date"
                                            name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][fecha_vencimiento]"
                                            class="form-control fecha-vencimiento"
                                            value="{{ $lote['fecha_vencimiento'] ?? '' }}"
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="text"
                                            name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][observacion]"
                                            class="form-control observacion-lote"
                                            maxlength="1000"
                                            value="{{ $lote['observacion'] ?? '' }}"
                                            placeholder="Opcional"
                                        >
                                    </td>

                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger btn-remove-lote"
                                            title="Eliminar lote"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>
                    </table>
                </div>

                {{-- PIE --}}
                <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary btn-add-lote"
                    >
                        <i class="bi bi-plus-lg me-1"></i>
                        Agregar otro lote / serie
                    </button>

                    <div class="d-flex gap-4 resumen-linea">

                        <div>
                            <span class="label d-block">
                                Total aceptado
                            </span>

                            <strong class="text-success total-aceptado">
                                0
                            </strong>
                        </div>

                        <div>
                            <span class="label d-block">
                                Pendiente restante
                            </span>

                            <strong class="text-warning total-pendiente">
                                {{ $pendiente }}
                            </strong>
                        </div>

                    </div>

                </div>

            </div>

        @endforeach

        {{-- ACCIONES --}}
        <div class="sticky-actions d-flex justify-content-between align-items-center flex-wrap gap-2">

            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Puedes dejar productos sin recibir; la orden quedará parcialmente recibida.
            </small>

            <div class="d-flex gap-2">

                <a
                    href="{{ route('compras.show', $orden) }}"
                    class="btn btn-outline-secondary"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="btnRegistrar"
                >
                    <i class="bi bi-clipboard-check me-1"></i>
                    Registrar recepción
                </button>

            </div>
        </div>

    </form>
</div>
@endsection


@push('styles')
<style>
    .producto-card .card-header {
        border-bottom: 1px solid #eef0f3;
    }

    .info-linea {
        background: var(--bs-tertiary-bg, #f8f9fa);
    }

    .lote-row input.form-control {
        font-size: .875rem;
        padding: .35rem .5rem;
    }

    .lote-row td {
        vertical-align: middle;
    }

    .resumen-linea .label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--bs-secondary-color, #6c757d);
    }

    .sticky-actions {
        position: sticky;
        bottom: 0;
        background: var(--bs-body-bg, #fff);
        border-top: 1px solid var(--bs-border-color, #e3e6eb);
        border-radius: .5rem;
        padding: 1rem 1.25rem;
        margin-top: 2rem;
        z-index: 10;
    }

    .badge-ubicacion {
        font-size: .8rem;
    }

    .fila-error {
        background: rgba(220, 53, 69, .04);
    }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('formRecepcion');

    function numero(input) {
        return Math.max(0, parseInt(input?.value || 0, 10) || 0);
    }

    function limpiarError(input) {
        if (!input) return;

        input.classList.remove('is-invalid');
        input.setCustomValidity('');
    }

    function marcarError(input, mensaje) {
        input.classList.add('is-invalid');
        input.setCustomValidity(mensaje);
    }

    function recalcularFila(row) {
        const recibidaInput = row.querySelector('.cant-recibida');
        const rechazadaInput = row.querySelector('.cant-rechazada');
        const aceptadaInput = row.querySelector('.cant-aceptada');
        const observacionInput = row.querySelector('.observacion-lote');

        const recibida = numero(recibidaInput);
        const rechazada = numero(rechazadaInput);

        limpiarError(rechazadaInput);

        if (rechazada > recibida) {
            aceptadaInput.value = 0;

            marcarError(
                rechazadaInput,
                'La cantidad rechazada no puede superar la cantidad recibida.'
            );

            row.classList.add('fila-error');
        } else {
            aceptadaInput.value = recibida - rechazada;
            row.classList.remove('fila-error');
        }

        if (rechazada > 0) {
            observacionInput.placeholder = 'Indica el motivo del rechazo';
        } else {
            observacionInput.placeholder = 'Opcional';
            limpiarError(observacionInput);
        }
    }

    function recalcularProducto(card) {
        const pendienteOriginal = parseInt(card.dataset.pendiente || 0, 10);

        let totalRecibido = 0;
        let totalAceptado = 0;

        card.querySelectorAll('.lote-row').forEach(row => {
            recalcularFila(row);

            totalRecibido += numero(
                row.querySelector('.cant-recibida')
            );

            totalAceptado += numero(
                row.querySelector('.cant-aceptada')
            );
        });

        card.querySelector('.total-aceptado').textContent =
            totalAceptado;

        const restanteEl = card.querySelector('.total-pendiente');

        const restante = pendienteOriginal - totalAceptado;

        if (totalRecibido > pendienteOriginal) {

            restanteEl.textContent =
                'Recepción excedida';

            restanteEl.className =
                'total-pendiente text-danger';

        } else if (restante === 0) {

            restanteEl.textContent = '0';

            restanteEl.className =
                'total-pendiente text-success';

        } else {

            restanteEl.textContent =
                Math.max(0, restante);

            restanteEl.className =
                'total-pendiente text-warning';
        }
    }

    document.querySelectorAll('.producto-card').forEach(card => {
        recalcularProducto(card);
    });

    document.addEventListener('input', e => {

        const row = e.target.closest('.lote-row');

        if (!row) return;

        recalcularFila(row);

        const card = row.closest('.producto-card');

        recalcularProducto(card);
    });

    document.querySelectorAll('.btn-add-lote').forEach(btn => {

        btn.addEventListener('click', function () {

            const card = this.closest('.producto-card');

            const tbody =
                card.querySelector('.lotes-body');

            const primeraFila =
                tbody.querySelector('.lote-row');

            const clone =
                primeraFila.cloneNode(true);

            const indice =
                parseInt(card.dataset.nextIndex || 0, 10);

            card.dataset.nextIndex =
                indice + 1;

            clone.classList.remove('fila-error');

            clone.querySelectorAll('input').forEach(input => {

                input.classList.remove('is-invalid');
                input.setCustomValidity('');

                if (input.name) {
                    input.name = input.name.replace(
                        /\[lotes\]\[\d+\]/,
                        `[lotes][${indice}]`
                    );
                }

                if (
                    input.classList.contains('cant-recibida') ||
                    input.classList.contains('cant-rechazada') ||
                    input.classList.contains('cant-aceptada')
                ) {
                    input.value = 0;
                } else {
                    input.value = '';
                }
            });

            tbody.appendChild(clone);

            recalcularProducto(card);

            clone.querySelector('.numero-lote')?.focus();
        });
    });

    document.addEventListener('click', e => {

        const btn =
            e.target.closest('.btn-remove-lote');

        if (!btn) return;

        const row =
            btn.closest('.lote-row');

        const tbody =
            row.closest('.lotes-body');

        const card =
            row.closest('.producto-card');

        if (
            tbody.querySelectorAll('.lote-row').length > 1
        ) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach(input => {

                if (
                    input.classList.contains('cant-recibida') ||
                    input.classList.contains('cant-rechazada') ||
                    input.classList.contains('cant-aceptada')
                ) {
                    input.value = 0;
                } else {
                    input.value = '';
                }

                limpiarError(input);
            });
        }

        recalcularProducto(card);
    });

    function validarFila(row) {

        const recibidaInput =
            row.querySelector('.cant-recibida');

        const rechazadaInput =
            row.querySelector('.cant-rechazada');

        const loteInput =
            row.querySelector('.numero-lote');

        const fabricacionInput =
            row.querySelector('.fecha-fabricacion');

        const vencimientoInput =
            row.querySelector('.fecha-vencimiento');

        const observacionInput =
            row.querySelector('.observacion-lote');

        const recibida =
            numero(recibidaInput);

        const rechazada =
            numero(rechazadaInput);

        [
            recibidaInput,
            rechazadaInput,
            loteInput,
            fabricacionInput,
            vencimientoInput,
            observacionInput
        ].forEach(limpiarError);

        if (recibida === 0) {
            return true;
        }

        if (!loteInput.value.trim()) {
            marcarError(
                loteInput,
                'Debes ingresar el lote o serie.'
            );

            loteInput.reportValidity();
            return false;
        }

        if (rechazada > recibida) {
            marcarError(
                rechazadaInput,
                'La cantidad rechazada no puede superar la recibida.'
            );

            rechazadaInput.reportValidity();
            return false;
        }

        if (rechazada > 0 && !observacionInput.value.trim()) {
            marcarError(
                observacionInput,
                'Debes indicar por qué se rechazaron unidades.'
            );

            observacionInput.reportValidity();
            return false;
        }

        if (!fabricacionInput.value) {
            marcarError(
                fabricacionInput,
                'Debes ingresar la fecha de fabricación.'
            );

            fabricacionInput.reportValidity();
            return false;
        }

        if (!vencimientoInput.value) {
            marcarError(
                vencimientoInput,
                'Debes ingresar la fecha de vencimiento.'
            );

            vencimientoInput.reportValidity();
            return false;
        }

        if (
            vencimientoInput.value <=
            fabricacionInput.value
        ) {
            marcarError(
                vencimientoInput,
                'El vencimiento debe ser posterior a la fabricación.'
            );

            vencimientoInput.reportValidity();
            return false;
        }

        return true;
    }

    form.addEventListener('submit', e => {

        let existeRecepcion = false;
        let formularioValido = true;

        document.querySelectorAll('.producto-card').forEach(card => {

            const pendiente =
                parseInt(card.dataset.pendiente || 0, 10);

            let totalRecibido = 0;
            const lotesUsados = new Set();

            card.querySelectorAll('.lote-row').forEach(row => {

                const recibida =
                    numero(row.querySelector('.cant-recibida'));

                if (recibida > 0) {
                    existeRecepcion = true;
                }

                totalRecibido += recibida;

                if (!validarFila(row)) {
                    formularioValido = false;
                    return;
                }

                if (recibida > 0) {

                    const loteInput =
                        row.querySelector('.numero-lote');

                    const lote =
                        loteInput.value.trim().toUpperCase();

                    if (lotesUsados.has(lote)) {

                        marcarError(
                            loteInput,
                            'Este lote ya fue ingresado para el mismo producto. Agrupa sus unidades en una sola fila.'
                        );

                        formularioValido = false;

                    } else {
                        lotesUsados.add(lote);
                    }
                }
            });

            if (totalRecibido > pendiente) {

                formularioValido = false;

                alert(
                    'La cantidad física recibida de uno de los productos supera la cantidad pendiente de la orden.'
                );
            }
        });

        if (!existeRecepcion) {
            e.preventDefault();

            alert(
                'Debes registrar al menos un producto recibido.'
            );

            return;
        }

        if (!formularioValido) {
            e.preventDefault();
        }
    });
});
</script>
@endpush