@can('gestionar_stock')

<style>
/* Estilos del modal (idénticos al de traslado) */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1050; opacity: 0; pointer-events: none; transition: opacity .2s ease; }
.modal-overlay.active { opacity: 1; pointer-events: auto; }
.modal-box { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-modal); width: 100%; max-width: 520px; max-height: calc(100vh - 3rem); display: flex; flex-direction: column; overflow: hidden; transform: scale(.95) translateY(8px); transition: transform .25s cubic-bezier(.16, 1, .3, 1); }
.modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

.modal-header { padding: 1.35rem 1.5rem 1.15rem; border-bottom: 1px solid var(--border); display: flex; align-items: flex-start; gap: .9rem; flex-shrink: 0; }
.header-icon { width: 44px; height: 44px; border-radius: 11px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(245,158,11,.3); }
.header-text { flex: 1; min-width: 0; }
.header-text h3 { margin: 0 0 .25rem; font-size: 1.05rem; font-weight: 700; letter-spacing: -.01em; }
.header-text p { margin: 0; font-size: .82rem; color: var(--text-muted); }
.modal-close { width: 32px; height: 32px; border: none; background: transparent; color: var(--text-soft); border-radius: 7px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.modal-close:hover { background: var(--danger-soft); color: var(--danger); }

.modal-body { padding: 1.35rem 1.5rem; overflow-y: auto; flex: 1; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: #fafbfd; display: flex; justify-content: flex-end; gap: .6rem; flex-shrink: 0; }

.section { margin-bottom: 1.35rem; }
.section:last-child { margin-bottom: 0; }
.section-title { display: flex; align-items: center; gap: .55rem; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .65rem; }
.step-num { width: 20px; height: 20px; border-radius: 50%; background: var(--primary); color: #fff; font-size: .68rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }

.form-control { display: block; width: 100%; padding: .65rem .85rem; font-size: .875rem; font-weight: 400; line-height: 1.5; color: var(--text); background-color: #fff; background-clip: padding-box; border: 1.5px solid var(--border-strong); border-radius: var(--radius-sm); transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out; }
.form-control:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.form-control:disabled, .form-control[readonly] { background-color: #f8fafc; opacity: 1; }

.preview-box { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid var(--border); border-radius: var(--radius); padding: 1rem 1.15rem; }
.preview-title { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: .7rem; display: flex; align-items: center; gap: .4rem; }
.preview-row { display: flex; justify-content: space-between; align-items: center; font-size: .82rem; margin-bottom: .5rem; }
.preview-row:last-child { margin-bottom: 0; }
.preview-after { font-weight: 700; color: var(--text); background: #fff; padding: .1rem .5rem; border-radius: 5px; border: 1px solid var(--border); font-size: .8rem; }
.preview-after.negative { color: var(--danger); border-color: #fecaca; background: var(--danger-soft); }
.preview-after.positive { color: var(--success); border-color: #a7f3d0; background: var(--success-soft); }

.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .65rem 1.15rem; border-radius: var(--radius-sm); font-size: .875rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all .15s; border: 1px solid transparent; white-space: nowrap; }
.btn-outline { background: #fff; border-color: var(--border-strong); color: var(--text); }
.btn-outline:hover { background: #f8fafc; border-color: var(--text-soft); }
.btn-primary { background: var(--primary); color: #fff; box-shadow: 0 1px 3px rgba(79,70,229,.3); }
.btn-primary:hover:not(:disabled) { background: var(--primary-hover); box-shadow: 0 2px 6px rgba(79,70,229,.4); }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; }
</style>

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
            <button class="modal-close" onclick="closeAjuste()" aria-label="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('inventario.ajuste') }}" id="form-ajuste">
            @csrf
            <input type="hidden" name="producto_id" id="ajuste_producto_id">
            <input type="hidden" name="ubicacion_id" id="ajuste_ubicacion_id">

            <div class="modal-body">
                {{-- Lote --}}
                <div class="section">
                    <div class="section-title">
                        <span class="step-num">1</span> Selecciona el lote / serie
                    </div>
                    <select name="lote_id" id="ajuste_lote_id" class="form-control" required>
                        <option value="">Seleccione un lote</option>
                    </select>
                </div>

                {{-- Cantidad --}}
                <div class="section">
                    <div class="section-title">
                        <span class="step-num">2</span> Diferencia de stock
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: .8rem;">Stock Actual</label>
                            <input type="text" id="ajuste_stock_actual" class="form-control" readonly value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: .8rem;">Diferencia *</label>
                            <input type="number" name="diferencia" id="ajuste_diferencia" class="form-control" placeholder="Ej: -3 o +5" required>
                        </div>
                    </div>
                    <div class="form-text mt-2" style="font-size: .75rem; color: var(--text-muted);">Use un valor positivo para aumentar el stock y un valor negativo para disminuirlo.</div>
                </div>

                {{-- Motivo --}}
                <div class="section">
                    <div class="section-title">
                        <span class="step-num">3</span> Motivo del ajuste
                    </div>
                    <textarea name="motivo" id="ajuste_motivo" class="form-control" rows="2" required placeholder="Ej. Conteo físico, merma, error de registro..."></textarea>
                </div>

                {{-- Preview --}}
                <div class="section">
                    <div class="preview-box">
                        <div class="preview-title"><i class="bi bi-eye"></i> Vista previa del resultado</div>
                        <div class="preview-row">
                            <span>Stock después del ajuste:</span>
                            <span id="ajuste_preview_after" class="preview-after">0 uds</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAjuste()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="ajuste_confirmar" disabled>
                    <i class="bi bi-check-lg"></i> Registrar ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Usamos $lotesParaJs (todos los lotes) y $stockCompleto (el stock real)
    const todosLosLotes = @json($lotesParaJs ?? []);
    const stockCompleto = @json($stockCompleto ?? []);

    let stockActualLote = 0;
    let loteSeleccionadoId = null;

    window.openAjuste = function(btn) {
        const pId = btn.dataset.productoId;
        const uId = btn.dataset.ubicacionId;

        document.getElementById('ajuste_producto_id').value = pId;
        document.getElementById('ajuste_ubicacion_id').value = uId;
        document.getElementById('ajuste_subtitulo').innerHTML = `${btn.dataset.productoNombre} · <span style="font-family:monospace; font-size:0.8rem;">${btn.dataset.productoCodigo || 'S/C'}</span>`;

        // Cargar TODOS los lotes del producto (incluso si tienen 0 stock)
        const lotesDelProducto = todosLosLotes.filter(l => l.producto_id == pId);
        const loteSelect = document.getElementById('ajuste_lote_id');

        if (lotesDelProducto.length === 0) {
            loteSelect.innerHTML = '<option value="">Este producto no tiene lotes registrados</option>';
        } else {
            loteSelect.innerHTML = '<option value="">Seleccione un lote</option>';
            lotesDelProducto.forEach(l => {
                loteSelect.innerHTML += `<option value="${l.id}">${l.numero_lote}</option>`;
            });
        }

        // Resetear formulario
        document.getElementById('ajuste_diferencia').value = '';
        document.getElementById('ajuste_motivo').value = '';
        document.getElementById('ajuste_stock_actual').value = '0';
        document.getElementById('ajuste_preview_after').textContent = '0 uds';
        document.getElementById('ajuste_preview_after').className = 'preview-after';
        document.getElementById('ajuste_confirmar').disabled = true;
        stockActualLote = 0;
        loteSeleccionadoId = null;

        document.getElementById('modalAjuste').classList.add('active');
    };

    window.closeAjuste = function() {
        document.getElementById('modalAjuste').classList.remove('active');
    };

    document.getElementById('ajuste_lote_id').addEventListener('change', function() {
        loteSeleccionadoId = this.value;
        const uId = document.getElementById('ajuste_ubicacion_id').value;

        if (loteSeleccionadoId) {
            // Buscar cuánto hay de ese lote en esa ubicación específica
            const registroStock = stockCompleto.find(s =>
                s.lote_id == loteSeleccionadoId &&
                s.ubicacion_id == uId
            );
            stockActualLote = registroStock ? parseInt(registroStock.cantidad) : 0;
            document.getElementById('ajuste_stock_actual').value = stockActualLote;
            document.getElementById('ajuste_diferencia').focus();
        } else {
            stockActualLote = 0;
            document.getElementById('ajuste_stock_actual').value = '0';
        }
        updateAjustePreview();
    });

    document.getElementById('ajuste_diferencia').addEventListener('input', updateAjustePreview);
    document.getElementById('ajuste_motivo').addEventListener('input', updateAjustePreview);

    function updateAjustePreview() {
        const diferencia = parseInt(document.getElementById('ajuste_diferencia').value) || 0;
        const motivo = document.getElementById('ajuste_motivo').value.trim();

        const previewEl = document.getElementById('ajuste_preview_after');

        if (!loteSeleccionadoId || diferencia === 0) {
            previewEl.textContent = '0 uds';
            previewEl.className = 'preview-after';
            document.getElementById('ajuste_confirmar').disabled = true;
            return;
        }

        const stockFinal = stockActualLote + diferencia;
        previewEl.textContent = stockFinal + ' uds';

        if (stockFinal < 0) {
            previewEl.className = 'preview-after negative';
            document.getElementById('ajuste_confirmar').disabled = true; // No permitir stock negativo
        } else if (diferencia > 0) {
            previewEl.className = 'preview-after positive';
        } else {
            previewEl.className = 'preview-after negative';
        }

        // El botón se habilita solo si hay motivo y el stock final no es negativo
        document.getElementById('ajuste_confirmar').disabled = (stockFinal < 0 || motivo === '');
    }
});
</script>

@endcan
