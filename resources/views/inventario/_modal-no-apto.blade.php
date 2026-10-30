@can('gestionar_stock')
<dialog id="modalNoApto" class="traslado-dialog" aria-labelledby="no_apto_titulo">
    <div class="traslado-card">
        <div class="traslado-header d-flex justify-content-between align-items-start">
            <div>
                <h2 id="no_apto_titulo" class="traslado-title">Marcar como no apto</h2>
                <p class="traslado-subtitle">Trasladar stock físico a cuarentena</p>
            </div>
            <button type="button" class="btn-close" data-cerrar-no-apto aria-label="Cerrar"></button>
        </div>
        <form method="POST" action="{{ route('inventario.marcar-no-apto') }}" id="form-no-apto">
            @csrf
            <input type="hidden" name="producto_id" id="no_apto_producto_id">
            <input type="hidden" name="ubicacion_origen_id" id="no_apto_origen_id">
            <div class="traslado-body">
                <div class="traslado-producto">
                    <div>
                        <div id="no_apto_producto" class="traslado-producto-nombre"></div>
                        <div class="traslado-producto-detalle">Origen: <span id="no_apto_origen"></span></div>
                        <div class="traslado-producto-detalle">Destino: Cuarentena (productos no aptos para venta)</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="no_apto_lote" class="traslado-label">Lote *</label>
                    <select name="lote_id" id="no_apto_lote" class="form-select" required></select>
                    <div id="no_apto_disponible" class="form-text" role="status" aria-live="polite"></div>
                </div>
                <div class="mb-3">
                    <label for="no_apto_cantidad" class="traslado-label">Cantidad *</label>
                    <input name="cantidad" id="no_apto_cantidad" type="number" min="1" step="1" class="form-control" required disabled>
                </div>
                <div class="mb-3">
                    <label for="no_apto_motivo" class="traslado-label">Motivo *</label>
                    <select name="motivo" id="no_apto_motivo" class="form-select" required>
                        <option value="">Selecciona un motivo</option>
                        <option value="vencido">Vencido</option>
                        <option value="deteriorado">Deteriorado</option>
                        <option value="envase_danado">Envase dañado</option>
                        <option value="contaminado">Contaminado</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label for="no_apto_observacion" class="traslado-label">Observación (opcional)</label>
                    <textarea name="observacion" id="no_apto_observacion" class="form-control" rows="2" maxlength="1000" placeholder="Describe el motivo si seleccionaste Otro, o agrega información adicional"></textarea>
                </div>
            </div>
            <div class="traslado-footer">
                <button type="button" class="btn btn-outline-secondary btn-traslado-cancelar" data-cerrar-no-apto>Cancelar</button>
                <button type="submit" id="no_apto_confirmar" class="btn btn-warning" disabled>Confirmar</button>
            </div>
        </form>
    </div>
</dialog>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('modalNoApto');
    const form = document.getElementById('form-no-apto');
    const stock = @json($stockCompleto);
    const lote = document.getElementById('no_apto_lote');
    const cantidad = document.getElementById('no_apto_cantidad');
    const confirmar = document.getElementById('no_apto_confirmar');
    const disponible = document.getElementById('no_apto_disponible');

    function actualizarLote() {
        const saldo = Number(lote.options[lote.selectedIndex]?.dataset.cantidad || 0);
        cantidad.disabled = confirmar.disabled = saldo <= 0;
        cantidad.max = saldo;
        if (saldo <= 0) cantidad.value = '';
        else if (Number(cantidad.value) > saldo) cantidad.value = saldo;
        disponible.textContent = `${saldo} unidades disponibles en este lote.`;
    }

    document.addEventListener('click', event => {
        const boton = event.target.closest('.btn-no-apto');
        if (!boton) return;
        event.preventDefault();
        form.reset();
        document.getElementById('no_apto_producto_id').value = boton.dataset.productoId;
        document.getElementById('no_apto_origen_id').value = boton.dataset.ubicacionId;
        document.getElementById('no_apto_producto').textContent = boton.dataset.productoNombre;
        document.getElementById('no_apto_origen').textContent = boton.dataset.ubicacionNombre;
        lote.replaceChildren(new Option('Selecciona un lote', ''));
        stock.filter(s => String(s.producto_id) === boton.dataset.productoId
            && String(s.ubicacion_id) === boton.dataset.ubicacionId && Number(s.cantidad) > 0)
            .forEach(s => {
                const opcion = new Option(`${s.lote_numero} · ${s.cantidad} uds.`, String(s.lote_id));
                opcion.dataset.cantidad = s.cantidad;
                lote.add(opcion);
            });
        if (lote.options.length === 2) lote.value = lote.options[1].value;
        actualizarLote();
        dialog.showModal();
    });
    lote.addEventListener('change', actualizarLote);
    dialog.querySelectorAll('[data-cerrar-no-apto]').forEach(boton => {
        boton.addEventListener('click', () => dialog.close());
    });
});
</script>
@endcan
