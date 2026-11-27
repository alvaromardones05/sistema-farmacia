<style>
/* Estilos del modal (idénticos al de traslado) */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1050; opacity: 0; pointer-events: none; transition: opacity .2s ease; }
.modal-overlay.active { opacity: 1; pointer-events: auto; }
.modal-box { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-modal); width: 100%; max-width: 820px; max-height: calc(100vh - 3rem); display: flex; flex-direction: column; overflow: hidden; transform: scale(.95) translateY(8px); transition: transform .25s cubic-bezier(.16, 1, .3, 1); }
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

.modal-header { padding: 1.35rem 1.5rem 1.15rem; border-bottom: 1px solid var(--border); display: flex; align-items: flex-start; gap: .9rem; flex-shrink: 0; }
.header-icon { width: 44px; height: 44px; border-radius: 11px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(99,102,241,.3); }
.header-text { flex: 1; min-width: 0; }
.header-text h3 { margin: 0 0 .25rem; font-size: 1.05rem; font-weight: 700; letter-spacing: -.01em; color: var(--text); }
.header-text p { margin: 0; font-size: .82rem; color: var(--text-muted); }
.modal-close { width: 32px; height: 32px; border: none; background: transparent; color: var(--text-soft); border-radius: 7px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.modal-close:hover { background: var(--danger-soft); color: var(--danger); }

.modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: var(--surface-2); display: flex; justify-content: flex-end; gap: .6rem; flex-shrink: 0; }

.section { margin-bottom: 1.25rem; }
.section:last-child { margin-bottom: 0; }
.section-title { display: flex; align-items: center; gap: .55rem; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .65rem; }
.step-num { width: 20px; height: 20px; border-radius: 50%; background: var(--primary); color: #fff; font-size: .68rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }

/* Lotes visuales (igual al de traslado) */
.lote-options {
    display: flex;
    flex-direction: column;
    gap: .55rem;
    max-height: 210px; /* Un poco más alto ahora que tiene su propia columna */
    overflow-y: auto;
    padding-right: 4px;
}
.lote-options::-webkit-scrollbar { width: 5px; }
.lote-options::-webkit-scrollbar-track { background: transparent; }
.lote-options::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }
.lote-options::-webkit-scrollbar-thumb:hover { background: var(--text-soft); }

.lote-option { display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem; border: 1.5px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all .15s ease; background: var(--surface); position: relative; }
.lote-option:hover { border-color: var(--primary); background: var(--surface-2); }
.lote-option.selected { border-color: var(--primary); background: var(--primary-soft); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
.radio-dot { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border-strong); flex-shrink: 0; position: relative; transition: all .15s; }
.lote-option.selected .radio-dot { border-color: var(--primary); }
.lote-option.selected .radio-dot::after { content: ''; position: absolute; inset: 3px; background: var(--primary); border-radius: 50%; }
.lote-info { flex: 1; min-width: 0; }
.lote-code { font-weight: 700; font-size: .85rem; color: var(--text); display: flex; align-items: center; gap: .4rem; margin-bottom: .15rem; }
.lote-meta { font-size: .76rem; color: var(--text-muted); }
.lote-stock { text-align: right; flex-shrink: 0; }
.lote-stock-num { font-size: 1.05rem; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; line-height: 1.1; }
.lote-stock-lbl { font-size: .68rem; color: var(--text-soft); font-weight: 500; }

/* Inputs */
.form-control { display: block; width: 100%; padding: .65rem .85rem; font-size: .875rem; font-weight: 400; line-height: 1.5; color: var(--text); background-color: var(--surface); border: 1.5px solid var(--border-strong); border-radius: var(--radius-sm); transition: all .15s; }
.form-control:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.form-control:disabled, .form-control[readonly] { background-color: var(--surface-2); opacity: 1; }

/* Cantidad */
.qty-control { display: flex; align-items: center; background: var(--surface-2); border: 1.5px solid var(--border-strong); border-radius: var(--radius-sm); overflow: hidden; transition: all .15s; }
.qty-control:focus-within { border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px var(--primary-soft); }
.qty-btn { width: 38px; height: 42px; border: none; background: transparent; color: var(--text-muted); font-size: 1.1rem; cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center; }
.qty-btn:hover:not(:disabled) { background: var(--primary-soft); color: var(--primary); }
.qty-input { width: 60px; height: 42px; border: none; background: transparent; text-align: center; font-size: 1.05rem; font-weight: 700; color: var(--text); font-family: inherit; }
.qty-input:focus { outline: none; }
.qty-input::-webkit-outer-spin-button, .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

/* Preview */
.preview-box { background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%); border: 1px solid var(--border); border-radius: var(--radius); padding: .85rem 1rem; }
.preview-title { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .6rem; display: flex; align-items: center; gap: .4rem; }
.preview-row { display: flex; justify-content: space-between; align-items: center; font-size: .82rem; margin-bottom: .5rem; }
.preview-row:last-child { margin-bottom: 0; }
.preview-after { font-weight: 700; color: var(--text); background: var(--surface); padding: .1rem .5rem; border-radius: 5px; border: 1px solid var(--border); font-size: .8rem; }
.preview-after.negative { color: var(--danger); border-color: var(--danger); background: var(--danger-soft); }
.preview-after.positive { color: var(--success); border-color: var(--success); background: var(--success-soft); }

/* Botones */
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .65rem 1.15rem; border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all .15s; border: 1px solid transparent; white-space: nowrap; }
.btn-outline { background: var(--surface); border-color: var(--border-strong); color: var(--text); }
.btn-outline:hover { background: var(--surface-2); border-color: var(--text-soft); }
.btn-primary { background: var(--primary); color: #fff; box-shadow: 0 1px 3px rgba(79,70,229,.3); }
.btn-primary:hover:not(:disabled) { background: var(--primary-hover); box-shadow: 0 2px 6px rgba(79,70,229,.4); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; }
</style>

@can('gestionar_stock')
<div class="modal-overlay" id="modalAjuste" onclick="if(event.target===this)closeAjuste()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="header-icon">
                <i class="bi bi-sliders"></i>
            </div>
            <div class="header-text">
                <h3>Ajuste de stock</h3>
                <p id="ajuste_subtitulo">Cargando producto...</p>
            </div>
            <button type="button" class="modal-close" onclick="closeAjuste()" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('inventario.ajuste') }}" id="form-ajuste">
            @csrf
            <input type="hidden" name="producto_id" id="ajuste_producto_id">
            <input type="hidden" name="ubicacion_id" id="ajuste_ubicacion_id">
            <input type="hidden" name="lote_id" id="ajuste_lote_id_hidden">

            <div class="modal-body">
                {{-- Aviso controlado --}}
                <div id="ajuste_aviso_controlado" class="alert alert-warning mb-3 d-none" style="font-size:.8rem; padding: .75rem 1rem; border-radius: 8px;">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Producto controlado: quedará pendiente de aprobación del Químico Farmacéutico.
                </div>

                <div class="row g-4">
                    {{-- COLUMNA IZQUIERDA: Ubicación y Lotes --}}
                    <div class="col-md-6 border-end" style="border-color: var(--border) !important;">
                        <div class="section">
                            <div class="preview-box mb-3" style="padding: .75rem 1rem;">
                                <div class="preview-row" style="margin-bottom: .25rem;"><span>Ubicación:</span><strong id="ajuste_ubicacion_nombre">—</strong></div>
                                <div class="preview-row"><span>Stock total aquí:</span><strong id="ajuste_stock_producto">0 uds</strong></div>
                            </div>

                            <div class="section-title mt-3">
                                <span class="step-num">1</span> Selecciona el lote
                            </div>
                            <div class="lote-options" id="ajuste_lotes_container">
                                <!-- Se llena con JS -->
                            </div>
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: Diferencia y Motivo --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="section">
                            <div class="section-title">
                                <span class="step-num">2</span> Diferencia de stock
                            </div>
                            <div class="row g-2 align-items-end">
                                <div class="col-6">
                                    <label class="form-label" style="font-size: .8rem;">Stock actual lote</label>
                                    <input type="text" id="ajuste_stock_actual" class="form-control" readonly value="—">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" style="font-size: .8rem;">Diferencia *</label>
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="cambiarDiferencia(-1)"><i class="bi bi-dash"></i></button>
                                        <input type="number" name="diferencia" id="ajuste_diferencia" class="qty-input" placeholder="0" required>
                                        <button type="button" class="qty-btn" onclick="cambiarDiferencia(1)"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text mt-2" style="font-size: .75rem; color: var(--text-muted);">Positivo para aumentar, negativo para disminuir.</div>
                        </div>

                        <div class="section flex-grow-1">
                            <div class="section-title">
                                <span class="step-num">3</span> Motivo del ajuste
                            </div>
                            <textarea name="motivo" id="ajuste_motivo" class="form-control" rows="4" maxlength="255" required placeholder="Ej. Conteo físico, merma, error de registro..."></textarea>
                        </div>

                        <div class="section mt-auto">
                            <div class="preview-box" style="padding: .75rem 1rem;">
                                <div class="preview-row" style="margin-bottom: 0;">
                                    <span>Stock después del ajuste:</span>
                                    <span id="ajuste_preview_after" class="preview-after">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAjuste()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="ajuste_confirmar" disabled>
                    <i class="bi bi-check-lg"></i> <span id="ajuste_confirmar_texto">Registrar ajuste</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const stockCompleto = @json($stockCompleto ?? []);
    const el = id => document.getElementById(id);

    // Extraer lotes únicos
    const todosLosLotes = Object.values(stockCompleto.reduce((acc, s) => {
        acc[s.lote_id] ??= {
            id: s.lote_id,
            producto_id: s.producto_id,
            numero_lote: s.lote_numero,
            fecha_vencimiento: s.lote_vencimiento,
            estado: s.lote_estado,
        };
        return acc;
    }, {}));

    let stockActualLote = 0;
    let loteSeleccionadoId = null;

    const stockEn = (loteId, uId) => {
        const r = stockCompleto.find(s => String(s.lote_id) === String(loteId) && String(s.ubicacion_id) === String(uId));
        return r ? parseInt(r.cantidad, 10) || 0 : 0;
    };
    const fechaCorta = f => f ? new Date(f).toLocaleDateString('es-CL', { timeZone: 'UTC' }) : 'sin fecha';

    // --- Apertura del Modal ---
    window.openAjuste = function (btn) {
        const pId = btn.dataset.productoId;
        const uId = btn.dataset.ubicacionId;
        const controlado = btn.dataset.controlado === '1';

        el('ajuste_producto_id').value = pId;
        el('ajuste_ubicacion_id').value = uId;
        el('ajuste_subtitulo').textContent = `${btn.dataset.productoNombre || 'Producto'} · ${btn.dataset.productoCodigo || 'S/C'}`;
        el('ajuste_ubicacion_nombre').textContent = btn.dataset.ubicacionNombre || `Ubicación #${uId}`;

        el('ajuste_aviso_controlado').classList.toggle('d-none', !controlado);
        el('ajuste_confirmar_texto').textContent = controlado ? 'Solicitar ajuste' : 'Registrar ajuste';

        // Lotes activos (los que vencen primero arriba)
        const lotes = todosLosLotes
            .filter(l => String(l.producto_id) === String(pId) && (!l.estado || l.estado === 'activo'))
            .sort((a, b) => String(a.fecha_vencimiento ?? '').localeCompare(String(b.fecha_vencimiento ?? '')));

        const total = lotes.reduce((suma, l) => suma + stockEn(l.id, uId), 0);
        el('ajuste_stock_producto').textContent = `${total} uds`;

        // Generar tarjetas de lotes
        const container = el('ajuste_lotes_container');
        container.innerHTML = '';

        if (lotes.length === 0) {
            container.innerHTML = '<div class="text-muted small p-3 text-center border rounded">Este producto no tiene lotes activos.</div>';
            loteSeleccionadoId = null;
        } else {
            lotes.forEach(l => {
                const cant = stockEn(l.id, uId);
                const div = document.createElement('div');
                div.className = 'lote-option';
                div.dataset.id = l.id;
                div.innerHTML = `
                    <div class="radio-dot"></div>
                    <div class="lote-info">
                        <div class="lote-code"><i class="bi bi-upc"></i> ${l.numero_lote ?? 'Lote #'+l.id}</div>
                        <div class="lote-meta">Vence: ${fechaCorta(l.fecha_vencimiento)}</div>
                    </div>
                    <div class="lote-stock">
                        <div class="lote-stock-num">${cant}</div>
                        <div class="lote-stock-lbl">uds</div>
                    </div>
                `;
                div.onclick = () => seleccionarLote(l.id, div);
                container.appendChild(div);
            });

            // Si hay un solo lote, seleccionarlo automáticamente
            if (lotes.length === 1) {
                seleccionarLote(lotes[0].id, container.firstElementChild);
            } else {
                // Si hay varios, limpiar selección previa
                seleccionarLote(null, null);
            }
        }

        // Limpiar inputs
        el('ajuste_diferencia').value = '';
        el('ajuste_motivo').value = '';
        updateAjustePreview();

        el('modalAjuste').classList.add('active');
    };

    window.closeAjuste = function () {
        el('modalAjuste').classList.remove('active');
    };

    // --- Lógica al seleccionar lote ---
    function seleccionarLote(id, elemento) {
        // Quitar clase selected a todos
        document.querySelectorAll('#ajuste_lotes_container .lote-option').forEach(opt => {
            opt.classList.remove('selected');
        });

        loteSeleccionadoId = id;
        el('ajuste_lote_id_hidden').value = id || '';

        if (id && elemento) {
            elemento.classList.add('selected');
            const uId = el('ajuste_ubicacion_id').value;
            stockActualLote = stockEn(id, uId);
            el('ajuste_stock_actual').value = `${stockActualLote} uds`;
            el('ajuste_diferencia').focus();
        } else {
            stockActualLote = 0;
            el('ajuste_stock_actual').value = '—';
        }
        updateAjustePreview();
    }

    // --- Botones + y - de cantidad ---
    window.cambiarDiferencia = (delta) => {
        const input = el('ajuste_diferencia');
        let val = parseInt(input.value, 10) || 0;
        val += delta;
        input.value = val;
        updateAjustePreview();
    };

    // --- Vista previa ---
    function updateAjustePreview() {
        const diferencia = parseInt(el('ajuste_diferencia').value, 10) || 0;
        const motivo = el('ajuste_motivo').value.trim();
        const previewEl = el('ajuste_preview_after');
        const boton = el('ajuste_confirmar');

        if (!loteSeleccionadoId || diferencia === 0) {
            previewEl.textContent = '—';
            previewEl.className = 'preview-after';
            boton.disabled = true;
            return;
        }

        const stockFinal = stockActualLote + diferencia;
        if (stockFinal < 0) {
            previewEl.textContent = `${stockFinal} uds (no puede ser < 0)`;
            previewEl.className = 'preview-after negative';
        } else {
            previewEl.textContent = `${stockFinal} uds`;
            previewEl.className = 'preview-after ' + (diferencia > 0 ? 'positive' : 'negative');
        }

        boton.disabled = stockFinal < 0 || motivo === '';
    }

    // Eventos de inputs
    el('ajuste_diferencia').addEventListener('input', updateAjustePreview);
    el('ajuste_motivo').addEventListener('input', updateAjustePreview);

    el('form-ajuste').addEventListener('submit', () => {
        el('ajuste_confirmar').disabled = true;
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && el('modalAjuste').classList.contains('active')) closeAjuste();
    });
});
</script>
@endcan
