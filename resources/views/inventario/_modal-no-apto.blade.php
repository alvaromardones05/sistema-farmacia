@can('gestionar_stock')

<div class="modal-overlay" id="modalNoApto" onclick="if(event.target===this)closeNoApto()">
    <div class="modal-box">
        <div class="modal-header">
            <div class="header-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="header-text">
                <h3>Marcar como no apto</h3>
                <p id="no_apto_subtitulo">Trasladar stock a cuarentena</p>
            </div>
            <button class="modal-close" onclick="closeNoApto()" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('inventario.marcar-no-apto') }}" id="form-no-apto">
            @csrf
            <input type="hidden" name="producto_id" id="no_apto_producto_id">
            <input type="hidden" name="ubicacion_origen_id" id="no_apto_origen_id">

            <div class="modal-body">
                <div class="section">
                    <div class="section-title">
                        <span class="step-num">1</span> Selecciona el lote / serie
                    </div>
                    <div class="lote-options" id="no_apto_lote_options">
                        <!-- JS -->
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">
                        <span class="step-num">2</span> Motivo del descarte
                    </div>
                    <select name="motivo" id="no_apto_motivo" class="motivo-field" required style="height: 48px; padding-top: 0; padding-bottom: 0;">
                        <option value="">Selecciona un motivo</option>
                        <option value="vencido">Vencido</option>
                        <option value="deteriorado">Deteriorado</option>
                        <option value="envase_danado">Envase dañado</option>
                        <option value="contaminado">Contaminado</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="section">
                    <div class="section-title">
                        <span class="step-num">3</span> Observación (opcional)
                    </div>
                    <textarea name="observacion" class="motivo-field" rows="2" placeholder="Describe el motivo si seleccionaste Otro..."></textarea>
                </div>

                <div class="section">
                    <div class="section-title">
                        <span class="step-num">4</span> Cantidad a retirar
                    </div>
                    <div class="qty-row">
                        <div class="qty-control">
                            <button type="button" class="qty-btn" onclick="adjustQtyNoApto(-1)">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" name="cantidad" class="qty-input" id="no_apto_qty" value="1" min="1">
                            <button type="button" class="qty-btn" onclick="adjustQtyNoApto(1)">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <div class="qty-info">
                            Disponible: <strong id="no_apto_max">0 uds</strong>
                        </div>
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
                                    <i class="bi bi-shop"></i> <span id="prev_noapto_origen_name">Origen</span>
                                </div>
                                <div class="preview-values">
                                    <span class="preview-before" id="prev_noapto_origen_before">0</span>
                                    <span class="preview-arrow">→</span>
                                    <span class="preview-after" id="prev_noapto_origen_after">0</span>
                                </div>
                            </div>
                            <div class="preview-row">
                                <div class="preview-loc">
                                    <i class="bi bi-shield-exclamation"></i> Cuarentena
                                </div>
                                <div class="preview-values">
                                    <span class="preview-before" id="prev_noapto_dest_before">0</span>
                                    <span class="preview-arrow">→</span>
                                    <span class="preview-after positive" id="prev_noapto_dest_after">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeNoApto()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="no_apto_confirmar" disabled style="background:var(--warning); border-color:var(--warning);">
                    <i class="bi bi-check-lg"></i> Mover a cuarentena
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const stock = @json($stockCompleto);
    const ubicaciones = @json($ubicaciones);
    let currentNoAptoStock = 0;

    window.openNoApto = function(btn) {
        const pId = btn.dataset.productoId;
        const uId = btn.dataset.ubicacionId;

        document.getElementById('no_apto_producto_id').value = pId;
        document.getElementById('no_apto_origen_id').value = uId;
        document.getElementById('no_apto_subtitulo').innerHTML = `${btn.dataset.productoNombre} · <span class="mono">${btn.dataset.productoCodigo || 'S/C'}</span>`;
        document.getElementById('prev_noapto_origen_name').textContent = btn.dataset.ubicacionNombre;

        // Cargar Lotes
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
        document.getElementById('no_apto_lote_options').innerHTML = loteHtml || '<div class="text-muted small">No hay lotes disponibles.</div>';

        if(lotes.length > 0) {
            currentNoAptoStock = lotes[0].cantidad;
            document.getElementById('no_apto_max').textContent = currentNoAptoStock + ' uds';
            document.getElementById('no_apto_qty').max = currentNoAptoStock;
        }

        // Reiniciar UI
        document.getElementById('no_apto_qty').value = 1;
        document.getElementById('no_apto_motivo').value = '';
        document.getElementById('no_apto_confirmar').disabled = true;
        
        setupEventosNoApto();
        updatePreviewNoApto();

        document.getElementById('modalNoApto').classList.add('active');
    };

    window.closeNoApto = function() {
        document.getElementById('modalNoApto').classList.remove('active');
    };

    function setupEventosNoApto() {
        document.querySelectorAll('#no_apto_lote_options .lote-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('#no_apto_lote_options .lote-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                opt.querySelector('input').checked = true;
                
                currentNoAptoStock = parseInt(opt.dataset.stock);
                document.getElementById('no_apto_max').textContent = currentNoAptoStock + ' uds';
                document.getElementById('no_apto_qty').max = currentNoAptoStock;
                
                if (parseInt(document.getElementById('no_apto_qty').value) > currentNoAptoStock) {
                    document.getElementById('no_apto_qty').value = currentNoAptoStock;
                }
                updatePreviewNoApto();
            });
        });
    }

    window.adjustQtyNoApto = function(delta) {
        const input = document.getElementById('no_apto_qty');
        const max = parseInt(input.max) || 1;
        let v = (parseInt(input.value) || 1) + delta;
        input.value = Math.max(1, Math.min(max, v));
        updatePreviewNoApto();
    };

    function updatePreviewNoApto() {
        const qty = parseInt(document.getElementById('no_apto_qty').value) || 0;
        const pId = document.getElementById('no_apto_producto_id').value;
        const motivo = document.getElementById('no_apto_motivo').value;

        // Actualizar origen
        document.getElementById('prev_noapto_origen_before').textContent = currentNoAptoStock;
        const afterOrigen = currentNoAptoStock - qty;
        document.getElementById('prev_noapto_origen_after').textContent = afterOrigen;
        document.getElementById('prev_noapto_origen_after').className = 'preview-after ' + (afterOrigen === 0 ? 'negative' : '');

        // Actualizar destino (Cuarentena)
        const cuarentenaId = ubicaciones.find(u => u.tipo === 'cuarentena')?.id;
        if (cuarentenaId) {
            const destBefore = stock.filter(s => s.producto_id == pId && s.ubicacion_id == cuarentenaId)
                                    .reduce((sum, s) => sum + parseInt(s.cantidad), 0);
            
            document.getElementById('prev_noapto_dest_before').textContent = destBefore;
            document.getElementById('prev_noapto_dest_after').textContent = destBefore + qty;
        }

        // Validar
        document.getElementById('no_apto_confirmar').disabled = (qty <= 0 || qty > currentNoAptoStock || !motivo);
    }

    document.getElementById('no_apto_qty')?.addEventListener('input', updatePreviewNoApto);
    document.getElementById('no_apto_motivo')?.addEventListener('change', updatePreviewNoApto);
});
</script>
@endcan