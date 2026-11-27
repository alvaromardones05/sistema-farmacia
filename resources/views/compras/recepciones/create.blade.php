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
                · {{ $orden->proveedor->nombre_razon_social ?? 'Proveedor no informado' }}
            </p>
        </div>

        <a href="{{ route('compras.show', $orden) }}" class="btn btn-outline-secondary">
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

    <form method="POST" action="{{ route('compras.recepciones.store', $orden) }}" id="formRecepcion">
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

                        <input type="text" id="numero_comprobante" name="numero_comprobante" class="form-control" maxlength="100" value="{{ old('numero_comprobante') }}" placeholder="Factura, guía u otro documento">
                    </div>

                    <div class="col-md-8">
                        <label for="observaciones" class="form-label">
                            Observaciones generales
                        </label>

                        <input type="text" id="observaciones" name="observaciones" class="form-control" value="{{ old('observaciones') }}" placeholder="Opcional">
                    </div>
                </div>
            </div>
        </div>

        @php
            $detallesPendientes = $orden->detalles->filter(function ($detalle) {
                return (int) $detalle->cantidad_solicitada > (int) $detalle->cantidad_recibida;
            });
        @endphp

        {{-- TÍTULO --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                Productos pendientes
                <span class="badge text-bg-secondary ms-1">{{ $detallesPendientes->count() }}</span>
            </h5>

            <small class="text-muted">
                Puedes dividir cada producto en varios lotes/series.
            </small>
        </div>

        @foreach ($detallesPendientes as $detalle)

            @php
                $pendiente = max(0, (int) $detalle->cantidad_solicitada - (int) $detalle->cantidad_recibida);
                $esRefrigerado = $detalle->producto->condicion_almacenamiento === 'refrigerado';
                $tipoUbicacion = $esRefrigerado ? 'frigorifica' : 'bodega';
                $nombreUbicacion = $ubicacionesAlmacenamiento->get($tipoUbicacion) ?? ($esRefrigerado ? 'Cámara Frigorífica' : 'Bodega Central');
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

            <div class="card border-0 shadow-sm mb-4 producto-card" data-pendiente="{{ $pendiente }}" data-detalle-id="{{ $detalle->id }}" data-next-index="{{ count($lotesOld) }}">
                {{-- PRODUCTO --}}
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0">{{ $detalle->producto->nombre }}</h6>
                            <small class="text-muted">
                                Cód. {{ $detalle->producto->codigo_interno ?? $detalle->producto->codigo_barra ?? 'Sin código' }}
                            </small>
                        </div>

                        <span class="badge text-bg-{{ $esRefrigerado ? 'info' : 'light' }} border badge-ubicacion">
                            <i class="bi {{ $esRefrigerado ? 'bi-snow' : 'bi-box-seam' }} me-1"></i>
                            Almacenar en <strong>{{ $nombreUbicacion }}</strong>
                        </span>
                    </div>
                </div>

                {{-- RESUMEN --}}
                <div class="card-body py-2 info-linea border-bottom">
                    <div class="d-flex flex-wrap gap-4 resumen-linea">
                        <div>
                            <span class="label d-block">Solicitado</span>
                            <strong>{{ $detalle->cantidad_solicitada }}</strong>
                        </div>
                        <div>
                            <span class="label d-block">Ya recibido</span>
                            <strong>{{ $detalle->cantidad_recibida }}</strong>
                        </div>
                        <div>
                            <span class="label d-block">Pendiente</span>
                            <strong class="text-warning pendiente-original">{{ $pendiente }}</strong>
                        </div>
                    </div>
                </div>

                {{-- LOTES --}}
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 tabla-lotes">
                        <colgroup>
                            <col style="width:15%"><col style="width:9%"><col style="width:9%"><col style="width:9%">
                            <col style="width:14%"><col style="width:14%"><col style="width:25%"><col style="width:5%">
                        </colgroup>
                        <thead class="table-light">
                            <tr>
                                <th>Lote / serie</th>
                                <th class="text-center">Recibida</th>
                                <th class="text-center">Aceptada</th>
                                <th class="text-center">Rechazada</th>
                                <th>Fabricación</th>
                                <th>Vencimiento</th>
                                <th>Observación</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="lotes-body">
                            @foreach ($lotesOld as $indice => $lote)
                                @php
                                    $recibida = max(0, (int) ($lote['cantidad_recibida'] ?? 0));
                                    $rechazada = max(0, (int) ($lote['cantidad_rechazada'] ?? 0));
                                    $aceptada = max(0, $recibida - $rechazada);
                                    $base = "detalles.{$detalle->id}.lotes.{$indice}";
                                    $err = fn ($campo) => $errors->has("{$base}.{$campo}") ? 'is-invalid' : '';
                                @endphp
                                <tr class="lote-row {{ $errors->has($base . '.*') ? 'fila-error' : '' }}">
                                    <td>
                                        <input type="text" name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][numero_lote]" class="form-control numero-lote {{ $err('numero_lote') }}" maxlength="100" value="{{ $lote['numero_lote'] ?? '' }}" placeholder="Ej. ABC001">
                                    </td>
                                    <td>
                                        <input type="number" name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][cantidad_recibida]" class="form-control cant-recibida {{ $err('cantidad_recibida') }}" min="0" step="1" inputmode="numeric" value="{{ $recibida ?: '' }}" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control cant-aceptada" value="{{ $aceptada }}" readonly tabindex="-1">
                                    </td>
                                    <td>
                                        <input type="number" name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][cantidad_rechazada]" class="form-control cant-rechazada {{ $err('cantidad_rechazada') }}" min="0" step="1" inputmode="numeric" value="{{ $rechazada ?: '' }}" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="date" name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][fecha_fabricacion]" class="form-control fecha-fabricacion {{ $err('fecha_fabricacion') }}" max="{{ now()->toDateString() }}" value="{{ $lote['fecha_fabricacion'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="date" name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][fecha_vencimiento]" class="form-control fecha-vencimiento {{ $err('fecha_vencimiento') }}" min="{{ now()->toDateString() }}" value="{{ $lote['fecha_vencimiento'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="detalles[{{ $detalle->id }}][lotes][{{ $indice }}][observacion]" class="form-control observacion-lote" maxlength="1000" value="{{ $lote['observacion'] ?? '' }}" placeholder="Opcional">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-lote" title="Eliminar lote">
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
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-add-lote">
                            <i class="bi bi-plus-lg me-1"></i> Agregar otro lote / serie
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-completar">
                            Recibir todo lo pendiente
                        </button>
                    </div>

                    <div class="d-flex gap-4 resumen-linea text-end">
                        <div>
                            <span class="label d-block">Total aceptado</span>
                            <strong class="text-success total-aceptado">0</strong>
                        </div>
                        <div>
                            <span class="label d-block">Pendiente restante</span>
                            <strong class="text-warning total-pendiente">{{ $pendiente }}</strong>
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
                <a href="{{ route('compras.show', $orden) }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary" id="btnRegistrar">
                    <i class="bi bi-clipboard-check me-1"></i> Registrar recepción
                </button>
            </div>
        </div>
    </form>
</div>
@endsection


@push('styles')
<style>
    .producto-card .card-header { border-bottom: 1px solid #eef0f3; }
    .info-linea { background: var(--bs-tertiary-bg, #f8f9fa); }
    .resumen-linea .label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color, #6c757d); }
    .badge-ubicacion { font-size: .8rem; }

    .tabla-lotes { table-layout: fixed; min-width: 960px; }
    .tabla-lotes th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; }
    .lote-row td { vertical-align: middle; }
    .lote-row input.form-control { font-size: .875rem; padding: .35rem .5rem; }
    .lote-row input[type=number] { text-align: center; -moz-appearance: textfield; }
    .lote-row input[type=number]::-webkit-outer-spin-button,
    .lote-row input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .lote-row .cant-aceptada { background: var(--bs-tertiary-bg, #f1f3f5); font-weight: 600; }
    .fila-error { background: rgba(220, 53, 69, .04); }

    .sticky-actions { position: sticky; bottom: 0; background: var(--bs-body-bg, #fff); border-top: 1px solid var(--bs-border-color, #e3e6eb); border-radius: .5rem; padding: 1rem 1.25rem; margin-top: 2rem; z-index: 10; }
</style>
@endpush


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('formRecepcion');

    function numero(input) {
        const n = Math.floor(Number(input?.value));
        return Number.isFinite(n) && n > 0 ? n : 0;
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

    function vaciarFila(row) {
        row.classList.remove('fila-error');
        row.querySelectorAll('input').forEach(input => {
            limpiarError(input);
            input.value = input.classList.contains('cant-aceptada') ? 0 : '';
        });
    }

    function recalcularFila(row) {
        const rechazadaInput = row.querySelector('.cant-rechazada');
        const aceptadaInput = row.querySelector('.cant-aceptada');
        const observacionInput = row.querySelector('.observacion-lote');

        const recibida = numero(row.querySelector('.cant-recibida'));
        const rechazada = numero(rechazadaInput);

        if (rechazada > recibida) {
            aceptadaInput.value = 0;
            marcarError(rechazadaInput, 'La cantidad rechazada no puede superar la cantidad recibida.');
            row.classList.add('fila-error');
        } else {
            aceptadaInput.value = recibida - rechazada;
            row.classList.remove('fila-error');
        }

        observacionInput.placeholder = rechazada > 0 ? 'Indica el motivo del rechazo' : 'Opcional';
    }

    function recalcularProducto(card) {
        const pendienteOriginal = parseInt(card.dataset.pendiente || 0, 10);
        let totalRecibido = 0;
        let totalAceptado = 0;

        card.querySelectorAll('.lote-row').forEach(row => {
            recalcularFila(row);
            totalRecibido += numero(row.querySelector('.cant-recibida'));
            totalAceptado += numero(row.querySelector('.cant-aceptada'));
        });

        card.querySelector('.total-aceptado').textContent = totalAceptado;

        const restanteEl = card.querySelector('.total-pendiente');
        const restante = pendienteOriginal - totalAceptado;

        if (totalRecibido > pendienteOriginal) {
            restanteEl.textContent = 'Recepción excedida';
            restanteEl.className = 'total-pendiente text-danger';
        } else if (restante === 0) {
            restanteEl.textContent = '0';
            restanteEl.className = 'total-pendiente text-success';
        } else {
            restanteEl.textContent = Math.max(0, restante);
            restanteEl.className = 'total-pendiente text-warning';
        }
    }

    document.querySelectorAll('.producto-card').forEach(recalcularProducto);

    // Solo dígitos enteros en las cantidades (sin e, +, -, punto ni coma)
    document.addEventListener('keydown', e => {
        if (e.target.matches('.cant-recibida, .cant-rechazada') && ['e', 'E', '+', '-', '.', ','].includes(e.key)) {
            e.preventDefault();
        }
    });

    document.addEventListener('input', e => {
        const row = e.target.closest('.lote-row');
        if (!row) return;

        const card = row.closest('.producto-card');

        // Normaliza "012", "5.0" o "1e3" a un entero limpio
        if (e.target.matches('.cant-recibida, .cant-rechazada')) {
            const v = e.target.value;
            if (v !== '' && !/^(0|[1-9]\d*)$/.test(v)) {
                e.target.value = String(numero(e.target));
            }
        }

        // Al corregir, se limpian los avisos viejos para que no bloqueen el envío
        card.querySelectorAll('.is-invalid').forEach(limpiarError);
        recalcularProducto(card);
    });

    document.querySelectorAll('.btn-add-lote').forEach(btn => {
        btn.addEventListener('click', function () {
            const card = this.closest('.producto-card');
            const tbody = card.querySelector('.lotes-body');
            const clone = tbody.querySelector('.lote-row').cloneNode(true);
            const indice = parseInt(card.dataset.nextIndex || 0, 10);

            card.dataset.nextIndex = indice + 1;

            clone.querySelectorAll('input[name]').forEach(input => {
                input.name = input.name.replace(/\[lotes\]\[\d+\]/, `[lotes][${indice}]`);
            });
            vaciarFila(clone);

            tbody.appendChild(clone);
            recalcularProducto(card);
            clone.querySelector('.numero-lote')?.focus();
        });
    });

    document.querySelectorAll('.btn-completar').forEach(btn => {
        btn.addEventListener('click', function () {
            const card = this.closest('.producto-card');
            const pendiente = parseInt(card.dataset.pendiente || 0, 10);
            const filas = card.querySelectorAll('.lote-row');
            let enOtrasFilas = 0;

            filas.forEach((row, i) => {
                if (i > 0) enOtrasFilas += numero(row.querySelector('.cant-recibida'));
            });

            const primera = filas[0].querySelector('.cant-recibida');
            primera.value = Math.max(0, pendiente - enOtrasFilas) || '';
            recalcularProducto(card);
            filas[0].querySelector('.numero-lote').focus();
        });
    });

    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-remove-lote');
        if (!btn) return;

        const row = btn.closest('.lote-row');
        const card = row.closest('.producto-card');

        if (row.closest('.lotes-body').querySelectorAll('.lote-row').length > 1) {
            row.remove();
        } else {
            vaciarFila(row);
        }
        recalcularProducto(card);
    });

    // Devuelve [input, mensaje] si la fila tiene un problema, o null si está bien
    function validarFila(row) {
        const recibidaInput = row.querySelector('.cant-recibida');
        const rechazadaInput = row.querySelector('.cant-rechazada');
        const loteInput = row.querySelector('.numero-lote');
        const fabricacionInput = row.querySelector('.fecha-fabricacion');
        const vencimientoInput = row.querySelector('.fecha-vencimiento');
        const observacionInput = row.querySelector('.observacion-lote');

        const recibida = numero(recibidaInput);
        const rechazada = numero(rechazadaInput);

        if (recibida === 0) {
            const conDatos = loteInput.value.trim() || fabricacionInput.value || vencimientoInput.value;
            return conDatos ? [recibidaInput, 'Indica la cantidad recibida de este lote o elimina la fila.'] : null;
        }
        if (!loteInput.value.trim()) return [loteInput, 'Debes ingresar el lote o serie.'];
        if (rechazada > recibida) return [rechazadaInput, 'La cantidad rechazada no puede superar la recibida.'];
        if (rechazada > 0 && !observacionInput.value.trim()) return [observacionInput, 'Debes indicar por qué se rechazaron unidades.'];
        if (!fabricacionInput.value) return [fabricacionInput, 'Debes ingresar la fecha de fabricación.'];
        if (!vencimientoInput.value) return [vencimientoInput, 'Debes ingresar la fecha de vencimiento.'];
        if (vencimientoInput.value <= fabricacionInput.value) return [vencimientoInput, 'El vencimiento debe ser posterior a la fabricación.'];

        return null;
    }

    form.addEventListener('submit', e => {
        form.querySelectorAll('.is-invalid').forEach(limpiarError);

        // Deja las cantidades como enteros limpios antes de enviar
        form.querySelectorAll('.cant-recibida, .cant-rechazada').forEach(input => {
            input.value = input.value === '' ? '' : String(numero(input));
        });

        let existeRecepcion = false;
        let primerError = null;
        const fallar = (input, mensaje) => {
            marcarError(input, mensaje);
            primerError = primerError || input;
        };

        document.querySelectorAll('.producto-card').forEach(card => {
            const pendiente = parseInt(card.dataset.pendiente || 0, 10);
            const lotesUsados = new Set();
            let totalRecibido = 0;
            let ultimaConCantidad = null;

            card.querySelectorAll('.lote-row').forEach(row => {
                const problema = validarFila(row);
                if (problema) fallar(problema[0], problema[1]);

                const recibidaInput = row.querySelector('.cant-recibida');
                const recibida = numero(recibidaInput);
                if (recibida <= 0) return;

                existeRecepcion = true;
                totalRecibido += recibida;
                ultimaConCantidad = recibidaInput;

                const loteInput = row.querySelector('.numero-lote');
                const lote = loteInput.value.trim().toUpperCase();
                if (lote) {
                    if (lotesUsados.has(lote)) {
                        fallar(loteInput, 'Este lote ya fue ingresado para el mismo producto. Agrupa sus unidades en una sola fila.');
                    }
                    lotesUsados.add(lote);
                }
            });

            if (totalRecibido > pendiente && ultimaConCantidad) {
                fallar(ultimaConCantidad, `Recibiste ${totalRecibido} unidades y solo hay ${pendiente} pendientes de este producto.`);
            }
        });

        if (!existeRecepcion) {
            fallar(document.querySelector('.cant-recibida'), 'Debes registrar al menos un producto recibido.');
        }

        if (primerError) {
            e.preventDefault();
            primerError.scrollIntoView({ block: 'center' });
            primerError.focus();
            primerError.reportValidity();
        }
    });
});
</script>
@endpush
