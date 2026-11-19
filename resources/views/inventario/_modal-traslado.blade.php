@can('gestionar_stock')

<style>
/* ============================================================
   TOKENS Y ESTILOS EXACTOS DEL PROTOTIPO
   ============================================================ */
:root {
    --bg: #f4f6fa;
    --surface: #fff;
    --border: #e5e9f0;
    --border-strong: #d3dae5;
    --text: #0f172a;
    --text-muted: #64748b;
    --text-soft: #94a3b8;
    --primary: #4f46e5;
    --primary-soft: #eef2ff;
    --primary-hover: #4338ca;
    --success: #059669;
    --success-soft: #ecfdf5;
    --warning: #d97706;
    --warning-soft: #fffbeb;
    --danger: #dc2626;
    --danger-soft: #fef2f2;
    --info: #0284c7;
    --info-soft: #f0f9ff;
    --violet: #7c3aed;
    --violet-soft: #f3e8ff;
    --radius-sm: 8px;
    --radius: 12px;
    --radius-lg: 20px;
    --shadow-sm: 0 1px 2px rgba(15,23,42,.05), 0 1px 3px rgba(15,23,42,.04);
    --shadow-lg: 0 20px 40px rgba(15,23,42,.15), 0 8px 16px rgba(15,23,42,.08);
    --shadow-modal: 0 30px 60px rgba(15,23,42,.22), 0 12px 24px rgba(15,23,42,.12);
}

.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1000; opacity: 0; pointer-events: none; transition: opacity .2s ease; }
.modal-overlay.active { opacity: 1; pointer-events: auto; }
.modal-box { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-modal); width: 100%; max-width: 640px; max-height: calc(100vh - 3rem); display: flex; flex-direction: column; overflow: hidden; transform: scale(.95) translateY(8px); transition: transform .25s cubic-bezier(.16, 1, .3, 1); }
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

/* Header */
.modal-header { padding: 1.35rem 1.5rem 1.15rem; border-bottom: 1px solid var(--border); display: flex; align-items: flex-start; gap: .9rem; flex-shrink: 0; }
.header-icon { width: 44px; height: 44px; border-radius: 11px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(99,102,241,.3); }
.header-text { flex: 1; min-width: 0; }
.header-text h3 { margin: 0 0 .25rem; font-size: 1.05rem; font-weight: 700; letter-spacing: -.01em; }
.header-text p { margin: 0; font-size: .82rem; color: var(--text-muted); }
.header-text p .mono { font-family: 'SF Mono', Menlo, monospace; color: var(--text); font-weight: 500; background: #f1f5f9; padding: .05rem .35rem; border-radius: 4px; font-size: .78rem; }
.modal-close { width: 32px; height: 32px; border: none; background: transparent; color: var(--text-soft); border-radius: 7px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.modal-close:hover { background: var(--danger-soft); color: var(--danger); }

/* Body */
.modal-body { padding: 1.35rem 1.5rem; overflow-y: auto; flex: 1; }
.modal-body::-webkit-scrollbar { width: 6px; }
.modal-body::-webkit-scrollbar-thumb { background: #d3dae5; border-radius: 3px; }

.alert-banner { display: flex; align-items: flex-start; gap: .65rem; padding: .8rem 1rem; border-radius: 10px; font-size: .82rem; line-height: 1.4; margin-bottom: 1.25rem; border: 1px solid; }
.alert-banner.warning { background: var(--warning-soft); border-color: #fde68a; color: #92400e; }
.alert-banner i { font-size: 1rem; flex-shrink: 0; margin-top: .05rem; }

/* Steps */
.section { margin-bottom: 1.35rem; }
.section:last-child { margin-bottom: 0; }
.section-title { display: flex; align-items: center; gap: .55rem; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .65rem; }
.step-num { width: 20px; height: 20px; border-radius: 50%; background: var(--primary); color: #fff; font-size: .68rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }

/* Lote */
.lote-options { display: flex; flex-direction: column; gap: .55rem; }
.lote-option { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border: 1.5px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all .15s ease; background: #fff; position: relative; }
.lote-option:hover { border-color: var(--primary); background: #fafbff; }
.lote-option.selected { border-color: var(--primary); background: var(--primary-soft); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
.lote-option input[type="radio"] { display: none; }
.radio-dot { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--border-strong); flex-shrink: 0; position: relative; transition: all .15s; }
.lote-option.selected .radio-dot { border-color: var(--primary); }
.lote-option.selected .radio-dot::after { content: ''; position: absolute; inset: 3px; background: var(--primary); border-radius: 50%; }
.lote-info { flex: 1; min-width: 0; }
.lote-code { font-family: 'SF Mono', Menlo, monospace; font-weight: 700; font-size: .85rem; color: var(--text); display: flex; align-items: center; gap: .4rem; margin-bottom: .15rem; }
.lote-meta { font-size: .76rem; color: var(--text-muted); display: flex; align-items: center; gap: .5rem; }
.lote-meta .venc { display: inline-flex; align-items: center; gap: .2rem; font-weight: 600; }
.lote-meta .venc.ok { color: var(--success); }
.lote-meta .venc.warn { color: var(--warning); }
.lote-meta .venc.urgent { color: var(--danger); }
.fefo-star { display: inline-flex; align-items: center; gap: .2rem; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #fff; padding: .12rem .45rem; border-radius: 4px; font-size: .62rem; font-weight: 700; letter-spacing: .04em; }
.lote-stock { text-align: right; flex-shrink: 0; }
.lote-stock-num { font-size: 1.05rem; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; line-height: 1.1; }
.lote-stock-lbl { font-size: .68rem; color: var(--text-soft); font-weight: 500; }

/* Destinos */
.dest-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; }
@media (max-width: 560px) { .dest-grid { grid-template-columns: 1fr; } }
.dest-card { padding: .85rem .85rem; border: 1.5px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all .15s ease; background: #fff; text-align: left; position: relative; display: flex; flex-direction: column; gap: .3rem; }
.dest-card:hover { border-color: var(--primary); background: #fafbff; }
.dest-card.selected { border-color: var(--primary); background: var(--primary-soft); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
.dest-card.selected.danger { border-color: var(--danger); background: var(--danger-soft); box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
.dest-icon { width: 30px; height: 30px; border-radius: 7px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: .9rem; margin-bottom: .15rem; }
.dest-card.selected .dest-icon { background: var(--primary); color: #fff; }
.dest-card.danger .dest-icon { background: var(--danger-soft); color: var(--danger); }
.dest-card.selected.danger .dest-icon { background: var(--danger); color: #fff; }
.dest-name { font-size: .82rem; font-weight: 600; color: var(--text); line-height: 1.2; }
.dest-desc { font-size: .7rem; color: var(--text-muted); line-height: 1.3; }
.dest-blocked { font-size: .68rem; color: var(--text-soft); font-weight: 500; display: flex; align-items: center; gap: .2rem; margin-top: .15rem; }

/* Cantidad */
.qty-row { display: flex; align-items: center; gap: .9rem; }
.qty-control { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid var(--border-strong); border-radius: var(--radius-sm); overflow: hidden; transition: all .15s; }
.qty-control:focus-within { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 3px var(--primary-soft); }
.qty-btn { width: 42px; height: 44px; border: none; background: transparent; color: var(--text-muted); font-size: 1.1rem; cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center; }
.qty-btn:hover:not(:disabled) { background: var(--primary-soft); color: var(--primary); }
.qty-btn:disabled { color: var(--text-soft); cursor: not-allowed; opacity: .5; }
.qty-input { width: 70px; height: 44px; border: none; background: transparent; text-align: center; font-size: 1.05rem; font-weight: 700; color: var(--text); font-family: inherit; font-variant-numeric: tabular-nums; }
.qty-input:focus { outline: none; }
.qty-input::-webkit-outer-spin-button, .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.qty-input[type=number] { -moz-appearance: textfield; }
.qty-info { flex: 1; font-size: .78rem; color: var(--text-muted); line-height: 1.4; }
.qty-info strong { color: var(--text); font-weight: 600; }
.qty-max-btn { background: transparent; border: none; color: var(--primary); font-size: .78rem; font-weight: 600; cursor: pointer; padding: 0; text-decoration: underline; text-decoration-color: transparent; transition: text-decoration-color .15s; font-family: inherit; }
.qty-max-btn:hover { text-decoration-color: var(--primary); }

/* Motivo */
.motivo-field { width: 100%; padding: .65rem .85rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius-sm); font-size: .85rem; font-family: inherit; background: #fff; color: var(--text); transition: all .15s; resize: vertical; min-height: 44px; }
.motivo-field:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.motivo-field.required { border-color: var(--warning); background: #fffef8; }
.motivo-hint { font-size: .72rem; color: var(--text-muted); margin-top: .35rem; display: flex; align-items: center; gap: .3rem; }
.motivo-hint.error { color: var(--danger); font-weight: 600; }

/* Preview */
.preview-box { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.15rem; }
.preview-title { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .7rem; display: flex; align-items: center; gap: .4rem; }
.preview-title i { color: var(--primary); }
.preview-rows { display: flex; flex-direction: column; gap: .5rem; }
.preview-row { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: .75rem; font-size: .82rem; }
.preview-loc { display: flex; align-items: center; gap: .35rem; font-weight: 600; color: var(--text); }
.preview-loc i { font-size: .85rem; color: var(--text-muted); }
.preview-values { display: flex; align-items: center; gap: .5rem; font-variant-numeric: tabular-nums; }
.preview-before { color: var(--text-muted); text-decoration: line-through; text-decoration-color: var(--text-soft); font-weight: 500; }
.preview-arrow { color: var(--text-soft); font-size: .75rem; }
.preview-after { font-weight: 700; color: var(--text); background: #fff; padding: .1rem .5rem; border-radius: 5px; border: 1px solid var(--border); font-size: .8rem; }
.preview-after.negative { color: var(--danger); border-color: #fecaca; background: var(--danger-soft); }
.preview-after.positive { color: var(--success); border-color: #a7f3d0; background: var(--success-soft); }

/* Footer */
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: #fafbfd; display: flex; justify-content: flex-end; gap: .6rem; flex-shrink: 0; }
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .65rem 1.15rem; border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all .15s; border: 1px solid transparent; white-space: nowrap; }
.btn-outline { background: #fff; border-color: var(--border-strong); color: var(--text); }
.btn-outline:hover { background: #f8fafc; border-color: var(--text-soft); }
.btn-primary { background: var(--primary); color: #fff; box-shadow: 0 1px 3px rgba(79,70,229,.3); }
.btn-primary:hover:not(:disabled) { background: var(--primary-hover); box-shadow: 0 2px 6px rgba(79,70,229,.4); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; }
</style>

<div class="modal-overlay" id="modalTraslado" onclick="if(event.target===this)closeTraslado()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="header-icon">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div class="header-text">
                <h3>Nuevo traslado de stock</h3>
                <p id="traslado_subtitulo">Cargando producto...</p>
            </div>
            <button class="modal-close" onclick="closeTraslado()" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('inventario.transferir') }}" id="form-traslado">
            @csrf
            <input type="hidden" name="producto_id" id="traslado_producto_id">
            <input type="hidden" name="ubicacion_origen_id" id="traslado_origen_id">
            <input type="hidden" name="motivo" id="traslado_motivo" value="Traslado interno de stock">
            <input type="hidden" name="ubicacion_destino_id" id="traslado_destino_id">

            <div class="modal-body">
                <div class="alert-banner warning" id="alertControlado" style="display:none;">
                    <i class="bi bi-shield-lock-fill"></i>
                    <div>
                        <strong>Medicamento controlado.</strong>
                        El traslado quedará en estado "pendiente" hasta que el Químico Farmacéutico lo apruebe.
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">
                        <span class="step-num">1</span> Selecciona el lote / serie
                    </div>
                    <div class="lote-options" id="traslado_lote_options">
                        <!-- JS -->
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">
                        <span class="step-num">2</span> Destino del traslado
                    </div>
                    <div class="dest-grid" id="traslado_dest_grid">
                        <!-- JS -->
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">
                        <span class="step-num">3</span> Cantidad a trasladar
                    </div>
                    <div class="qty-row">
                        <div class="qty-control">
                            <button type="button" class="qty-btn" id="qtyMinus" onclick="adjustQty(-1)">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" class="qty-input" name="cantidad" id="qtyInput" value="1" min="1">
                            <button type="button" class="qty-btn" id="qtyPlus" onclick="adjustQty(1)">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <div class="qty-info">
                            Disponible en el lote: <strong id="qtyMax">0 uds</strong><br>
                            <button type="button" class="qty-max-btn" onclick="setMaxQty()">Usar todo el stock</button>
                        </div>
                    </div>
                </div>

                <div class="section" id="motivoSection" style="display:none;">
                    <div class="section-title">
                        <span class="step-num">4</span> Motivo del traslado
                    </div>
                    <textarea class="motivo-field required" id="motivoInput" rows="2" placeholder="Ej. Lote vencido detectado en vitrina, 2 unidades con empaque dañado..."></textarea>
                    <div class="motivo-hint error" id="motivoHint">
                        <i class="bi bi-exclamation-circle-fill"></i> El motivo es obligatorio para Cuarentena
                    </div>
                </div>

                <div class="section">
                    <div class="preview-box">
                        <div class="preview-title">
                            <i class="bi bi-eye"></i> Vista previa del resultado
                        </div>
                        <div class="preview-rows">
                            <div class="preview-row">
                                <div class="preview-loc">
                                    <i class="bi bi-shop"></i> <span id="prev_origen_name">Origen</span>
                                </div>
                                <div class="preview-values">
                                    <span class="preview-before" id="prev_origen_before">0</span>
                                    <span class="preview-arrow">→</span>
                                    <span class="preview-after" id="prev_origen_after">0</span>
                                </div>
                            </div>
                            <div class="preview-row">
                                <div class="preview-loc">
                                    <i class="bi bi-box-seam"></i> <span id="prev_dest_name">Destino</span>
                                </div>
                                <div class="preview-values">
                                    <span class="preview-before" id="prev_dest_before">0</span>
                                    <span class="preview-arrow">→</span>
                                    <span class="preview-after positive" id="prev_dest_after">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeTraslado()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="confirmBtn" disabled>
                    <i class="bi bi-check-lg"></i> Confirmar traslado
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const stock = @json($stockCompleto);
    const ubicaciones = @json($ubicaciones);
    let currentLoteStock = 0;
    let currentDestinoId = null;
    let currentDestinoTipo = null;

    window.openTraslado = function(btn) {
        const pId = btn.dataset.productoId;
        const uId = btn.dataset.ubicacionId;
        const condicion = btn.dataset.productoCondicion;
        const tipoUbicacion = btn.dataset.ubicacionTipo;

        document.getElementById('traslado_producto_id').value = pId;
        document.getElementById('traslado_origen_id').value = uId;
        document.getElementById('traslado_subtitulo').innerHTML = `${btn.dataset.productoNombre} · <span class="mono">${btn.dataset.productoCodigo || 'S/C'}</span>`;

        // Alert controlado
        document.getElementById('alertControlado').style.display = (btn.dataset.productoControlado === '1') ? 'flex' : 'none';

        // 1. Cargar Lotes
        const registros = stock.filter(s => s.producto_id == pId && s.ubicacion_id == uId && s.cantidad > 0);
        const lotesMap = {};
        registros.forEach(s => {
            if(!lotesMap[s.lote_id]) lotesMap[s.lote_id] = { id: s.lote_id, numero: s.lote_numero, cantidad: 0 };
            lotesMap[s.lote_id].cantidad += s.cantidad;
        });
        const lotes = Object.values(lotesMap);

        const loteHtml = lotes.map((l, i) => `
            <label class="lote-option ${i === 0 ? 'selected' : ''}" data-lote="${l.id}" data-stock="${l.cantidad}">
                <input type="radio" name="lote_id" value="${l.id}" ${i === 0 ? 'checked' : ''}>
                <span class="radio-dot"></span>
                <div class="lote-info">
                    <div class="lote-code">${l.numero}</div>
                </div>
                <div class="lote-stock">
                    <div class="lote-stock-num">${l.cantidad}</div>
                    <div class="lote-stock-lbl">uds</div>
                </div>
            </label>
        `).join('');
        document.getElementById('traslado_lote_options').innerHTML = loteHtml || '<div class="text-muted small">No hay lotes disponibles.</div>';

        if(lotes.length > 0) {
            currentLoteStock = lotes[0].cantidad;
            document.getElementById('qtyMax').textContent = currentLoteStock + ' uds';
            document.getElementById('qtyInput').max = currentLoteStock;
        }

        // 2. Cargar Destinos
        let destinosValidos = [];
        if (condicion === 'refrigerado') {
            destinosValidos = ubicaciones.filter(u => ['vitrina_refrigerada', 'frigorifica'].includes(u.tipo) && u.id != uId);
        } else {
            destinosValidos = ubicaciones.filter(u => ['vitrina', 'bodega'].includes(u.tipo) && u.id != uId);
        }
        const cuarentena = ubicaciones.find(u => u.tipo === 'cuarentena');
        if (cuarentena) destinosValidos.push(cuarentena);

        const destHtml = destinosValidos.map((d, i) => {
            const isCuarentena = d.tipo === 'cuarentena';
            return `
                <button type="button" class="dest-card ${i === 0 && !isCuarentena ? 'selected' : ''} ${isCuarentena ? 'danger' : ''}" data-dest="${d.id}" data-tipo="${d.tipo}">
                    <div class="dest-icon"><i class="bi ${isCuarentena ? 'bi-exclamation-triangle' : 'bi-box-seam'}"></i></div>
                    <div class="dest-name">${d.nombre}</div>
                    <div class="dest-desc">${isCuarentena ? 'Vencido o deteriorado' : 'Traslado normal'}</div>
                </button>
            `;
        }).join('');
        document.getElementById('traslado_dest_grid').innerHTML = destHtml;

        // Resetear UI
        // Resetear UI
        document.getElementById('qtyInput').value = 1;
        document.getElementById('motivoInput').value = '';
        document.getElementById('motivoSection').style.display = 'none';
        document.getElementById('prev_origen_name').textContent = btn.dataset.ubicacionNombre;

        // Reiniciar destino
        currentDestinoId = null;
        currentDestinoTipo = null;
        document.getElementById('traslado_destino_id').value = '';

        // Configurar eventos
        setupEventosTraslado();

        // Seleccionar automáticamente el primer destino válido
        const primerDestino = document.querySelector(
            '#traslado_dest_grid .dest-card:not(.danger)'
        );

        if (primerDestino) {
            primerDestino.click();
        } else {
            updatePreviewTraslado();
        }

        document.getElementById('modalTraslado').classList.add('active');
    };

    window.closeTraslado = function() {
        document.getElementById('modalTraslado').classList.remove('active');
    };

    function setupEventosTraslado() {
        document.querySelectorAll('#traslado_lote_options .lote-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('#traslado_lote_options .lote-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                opt.querySelector('input').checked = true;

                currentLoteStock = parseInt(opt.dataset.stock);
                document.getElementById('qtyMax').textContent = currentLoteStock + ' uds';
                document.getElementById('qtyInput').max = currentLoteStock;

                if (parseInt(document.getElementById('qtyInput').value) > currentLoteStock) {
                    document.getElementById('qtyInput').value = currentLoteStock;
                }
                updatePreviewTraslado();
            });
        });

        document.querySelectorAll('#traslado_dest_grid .dest-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('#traslado_dest_grid .dest-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');

                currentDestinoId = card.dataset.dest;
                currentDestinoTipo = card.dataset.tipo;

                document.getElementById('traslado_destino_id').value = currentDestinoId;

                // Mostrar motivo si es cuarentena
                if (currentDestinoTipo === 'cuarentena') {
                    document.getElementById('motivoSection').style.display = 'block';
                } else {
                    document.getElementById('motivoSection').style.display = 'none';
                    document.getElementById('motivoInput').value = '';
                }
                updatePreviewTraslado();
            });
        });
    }

    window.adjustQty = function(delta) {
        const input = document.getElementById('qtyInput');
        const max = parseInt(input.max) || 1;
        let v = (parseInt(input.value) || 1) + delta;
        input.value = Math.max(1, Math.min(max, v));
        updatePreviewTraslado();
    };

    window.setMaxQty = function() {
        const input = document.getElementById('qtyInput');
        input.value = input.max;
        updatePreviewTraslado();
    };

    function updatePreviewTraslado() {
        const qty = parseInt(document.getElementById('qtyInput').value) || 0;
        const destSel = document.querySelector('#traslado_dest_grid .dest-card.selected');
        const pId = document.getElementById('traslado_producto_id').value;

        // Actualizar origen
        document.getElementById('prev_origen_before').textContent = currentLoteStock;
        const afterOrigen = currentLoteStock - qty;
        document.getElementById('prev_origen_after').textContent = afterOrigen;
        document.getElementById('prev_origen_after').className = 'preview-after ' + (afterOrigen === 0 ? 'negative' : '');

        // Actualizar destino (Calculando el stock real que hay ahí)
        if (destSel) {
            const dId = destSel.dataset.dest;
            const destName = destSel.querySelector('.dest-name').textContent;

            const destBefore = stock.filter(s => s.producto_id == pId && s.ubicacion_id == dId)
                                    .reduce((sum, s) => sum + parseInt(s.cantidad), 0);

            document.getElementById('prev_dest_name').textContent = destName;
            document.getElementById('prev_dest_before').textContent = destBefore;
            document.getElementById('prev_dest_after').textContent = destBefore + qty;
        }

        // Validar motivo
        const isCuarentena = currentDestinoTipo === 'cuarentena';
        const motivo = document.getElementById('motivoInput').value.trim();

        if (isCuarentena && !motivo) {
            document.getElementById('motivoHint').style.display = 'flex';
            document.getElementById('confirmBtn').disabled = true;
            document.getElementById('traslado_motivo').value = '';
        } else {
            document.getElementById('motivoHint').style.display = 'none';
            document.getElementById('confirmBtn').disabled = (qty <= 0 || qty > currentLoteStock);
            document.getElementById('traslado_motivo').value = isCuarentena ? motivo : 'Traslado interno de stock';
        }

        document.getElementById('qtyMinus').disabled = qty <= 1;
        document.getElementById('qtyPlus').disabled = qty >= currentLoteStock;
    }

    document.getElementById('qtyInput')?.addEventListener('input', updatePreviewTraslado);
    document.getElementById('motivoInput')?.addEventListener('input', updatePreviewTraslado);
});
</script>
@endcan
