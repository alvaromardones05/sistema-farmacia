{{-- Formulario de una acción del canje.
     Variables: $accion, $canje, $r (resumen), $proveedores,
                $tokenEntrega, $tokenReposicion, $tokenDevoluciones --}}
@php
    $maxEntrega = max(0, min((int) $r['por_enviar'], (int) $canje->stock_en_canje));
@endphp

@switch($accion)

    {{-- ENTREGA AL DISTRIBUIDOR --}}
    @case('entrega')
        @if($maxEntrega < 1)
            <div class="alert alert-warning mb-0">
                No hay unidades de este lote en la zona de Canje para entregar.
            </div>
        @else
            <p class="text-muted">
                Se descontarán de la zona de Canje las unidades que entregues. Puedes entregar hasta {{ $maxEntrega }}.
            </p>
            <form method="POST" action="{{ route('inventario.canjes.entregas.store', $canje) }}">
                @csrf
                <input type="hidden" name="idempotency_token" value="{{ $tokenEntrega }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Unidades que entregas</label>
                        <input type="number" name="cantidad_enviada" min="1" max="{{ $maxEntrega }}"
                               value="{{ old('cantidad_enviada', $maxEntrega) }}" required class="form-control">
                    </div>
                    @include('inventario.canjes.partials.documento', ['etiquetaDocumento' => 'N° de guía o documento de entrega'])
                    <div class="col-12">
                        <button class="btn btn-primary">Registrar entrega</button>
                    </div>
                </div>
            </form>
        @endif
        @if($r['sin_respuesta'] < 1 && $r['por_devolver'] < 1 && $r['por_enviar'] > 0)
            <hr>
            <p class="text-muted mb-2">
                {{ $canje->entregas->isEmpty() ? '¿No vas a entregar estas unidades?' : '¿El distribuidor no recibirá más unidades?' }}
                Las {{ $r['por_enviar'] }} unidades salen de la zona de Canje. Vuelven a su ubicación de origen si al lote
                le quedan más de {{ \App\Models\Canje::DIAS_MINIMOS_PARA_REINGRESO }} días; si no, pasan a Cuarentena como no aptas.
            </p>
            <form method="POST" action="{{ route('inventario.canjes.saldo', $canje) }}"
                  onsubmit="return confirm('Las unidades saldrán de la zona de Canje. ¿Confirmas?')">
                @csrf
                <button class="btn btn-outline-secondary">
                    {{ $canje->entregas->isEmpty() ? 'Cancelar canje' : 'No enviar más unidades' }}
                </button>
            </form>
        @endif
        @break

    {{-- RESPUESTA DEL DISTRIBUIDOR (una por envío con unidades sin clasificar) --}}
    @case('respuesta')
        @foreach($canje->entregas as $entrega)
            @php
                $porClasificar = (int) $entrega->cantidad_enviada - (int) $entrega->cantidad_aceptada - (int) $entrega->cantidad_rechazada;
            @endphp
            @continue($porClasificar < 1)
            <p class="text-muted mb-2">
                Envío del {{ $entrega->fecha_entrega->format('d-m-Y') }}, documento {{ $entrega->numero_documento }}:
                el distribuidor aún no clasifica {{ $porClasificar }} unidades. Aceptadas y rechazadas deben sumar como máximo {{ $porClasificar }}.
            </p>
            <form method="POST" action="{{ route('inventario.canjes.respuestas.store', [$canje, $entrega]) }}" class="mb-4">
                @csrf
                <input type="hidden" name="idempotency_token" value="{{ \Illuminate\Support\Str::uuid() }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Unidades aceptadas</label>
                        <input type="number" name="cantidad_aceptada" min="0" max="{{ $porClasificar }}"
                               value="{{ old('cantidad_aceptada', $porClasificar) }}" required class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unidades rechazadas</label>
                        <input type="number" name="cantidad_rechazada" min="0" max="{{ $porClasificar }}"
                               value="{{ old('cantidad_rechazada', 0) }}" required class="form-control">
                    </div>
                    <div class="col-md-4"></div>
                    @include('inventario.canjes.partials.documento', ['etiquetaDocumento' => 'N° de documento de respuesta'])
                    <div class="col-12">
                        <button class="btn btn-primary">Registrar respuesta</button>
                    </div>
                </div>
            </form>
        @endforeach
        @break

    {{-- RETORNO DE UNIDADES RECHAZADAS (una por envío con rechazadas sin retornar) --}}
    @case('devolucion')
        @foreach($canje->entregas as $entrega)
            @php
                $porRetornar = (int) $entrega->cantidad_rechazada - (int) $entrega->devoluciones->sum('cantidad');
            @endphp
            @continue($porRetornar < 1)
            <p class="text-muted mb-2">
                Envío del {{ $entrega->fecha_entrega->format('d-m-Y') }}: {{ $porRetornar }} unidades rechazadas siguen en poder del distribuidor.
                Al registrar el retorno vuelven a la zona de Canje.
            </p>
            <form method="POST" action="{{ route('inventario.canjes.devoluciones.store', [$canje, $entrega]) }}" class="mb-4">
                @csrf
                <input type="hidden" name="idempotency_token" value="{{ $tokenDevoluciones[$entrega->id] ?? \Illuminate\Support\Str::uuid() }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Unidades que regresan</label>
                        <input type="number" name="cantidad" min="1" max="{{ $porRetornar }}"
                               value="{{ old('cantidad', $porRetornar) }}" required class="form-control">
                    </div>
                    @include('inventario.canjes.partials.documento', ['etiquetaDocumento' => 'N° de documento de retorno'])
                    <div class="col-12">
                        <button class="btn btn-primary">Registrar retorno</button>
                    </div>
                </div>
            </form>
        @endforeach
        @break

        {{-- REPOSICIÓN CON UNO O VARIOS LOTES NUEVOS --}}
        @case('reposicion')
            @php
                $filasLotes = old('lotes', [['cantidad' => $r['por_reponer']]]);
                $siguienteIndice = collect(array_keys($filasLotes))->max() + 1;
            @endphp
            <p class="text-muted">
                Faltan {{ $r['por_reponer'] }} unidades por reponer. Si llegaron cajas de distintos lotes, agrega una fila por lote con el botón +.
                Los lotes nuevos entran a
                {{ $canje->producto->condicion_almacenamiento === 'refrigerado' ? 'la cámara frigorífica' : 'bodega' }}
                y deben ser distintos del lote original ({{ $canje->loteOriginal->numero_lote }}).
            </p>
            <form method="POST" action="{{ route('inventario.canjes.reposiciones.store', $canje) }}">
                @csrf
                <input type="hidden" name="idempotency_token" value="{{ $tokenReposicion }}">

                <div id="filas-lotes" data-siguiente="{{ $siguienteIndice }}">
                    @foreach($filasLotes as $i => $fila)
                        <div class="row g-2 align-items-end mb-2 fila-lote">
                            <div class="col-md-3">
                                <label class="form-label">N° de lote</label>
                                <input type="text" name="lotes[{{ $i }}][numero_lote]" value="{{ $fila['numero_lote'] ?? '' }}"
                                       maxlength="100" required class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fabricación</label>
                                <input type="date" name="lotes[{{ $i }}][fecha_fabricacion]" value="{{ $fila['fecha_fabricacion'] ?? '' }}"
                                       required class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vencimiento</label>
                                <input type="date" name="lotes[{{ $i }}][fecha_vencimiento]" value="{{ $fila['fecha_vencimiento'] ?? '' }}"
                                       required class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unidades</label>
                                <input type="number" name="lotes[{{ $i }}][cantidad]" value="{{ $fila['cantidad'] ?? '' }}"
                                       min="1" max="{{ $r['por_reponer'] }}" required class="form-control">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger quitar-lote" title="Quitar este lote">&times;</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="agregar-lote" class="btn btn-sm btn-outline-secondary mb-1" title="Agregar otro lote">+ Agregar lote</button>
                <div class="form-text mb-3">El total de unidades no puede superar {{ $r['por_reponer'] }}.</div>

                <div class="row g-3 align-items-end">
                    @include('inventario.canjes.partials.documento', ['etiquetaDocumento' => 'N° de factura o guía de reposición'])
                    <div class="col-12">
                        <button class="btn btn-primary">Registrar reposición</button>
                    </div>
                </div>
            </form>

            <template id="plantilla-lote">
                <div class="row g-2 align-items-end mb-2 fila-lote">
                    <div class="col-md-3">
                        <label class="form-label">N° de lote</label>
                        <input type="text" name="lotes[__I__][numero_lote]" maxlength="100" required class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fabricación</label>
                        <input type="date" name="lotes[__I__][fecha_fabricacion]" required class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vencimiento</label>
                        <input type="date" name="lotes[__I__][fecha_vencimiento]" required class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unidades</label>
                        <input type="number" name="lotes[__I__][cantidad]" min="1" max="{{ $r['por_reponer'] }}" required class="form-control">
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger quitar-lote" title="Quitar este lote">&times;</button>
                    </div>
                </div>
            </template>

            <script>
                (function () {
                    var contenedor = document.getElementById('filas-lotes');
                    var plantilla = document.getElementById('plantilla-lote');
                    var siguiente = parseInt(contenedor.dataset.siguiente, 10);

                    function actualizarQuitar() {
                        var filas = contenedor.querySelectorAll('.fila-lote');
                        filas.forEach(function (fila) {
                            fila.querySelector('.quitar-lote').style.visibility = filas.length > 1 ? 'visible' : 'hidden';
                        });
                    }

                    document.getElementById('agregar-lote').addEventListener('click', function () {
                        contenedor.insertAdjacentHTML('beforeend', plantilla.innerHTML.replace(/__I__/g, siguiente++));
                        actualizarQuitar();
                    });

                    contenedor.addEventListener('click', function (evento) {
                        var boton = evento.target.closest('.quitar-lote');
                        if (boton) {
                            boton.closest('.fila-lote').remove();
                            actualizarQuitar();
                        }
                    });

                    actualizarQuitar();
                })();
            </script>
            @break

    {{-- CIERRE --}}
    @case('cerrar')
        <p class="text-muted">
            Todas las unidades están entregadas, aceptadas y repuestas. Al cerrar, el canje queda como completado.
        </p>
        <form method="POST" action="{{ route('inventario.canjes.cerrar', $canje) }}">
            @csrf
            <button class="btn btn-success">Cerrar canje</button>
        </form>
        @break

@endswitch
