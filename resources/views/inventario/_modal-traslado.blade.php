@can('gestionar_stock')

<style>
.traslado-dialog{border:0;padding:0;width:min(520px,calc(100vw - 24px));max-width:520px;border-radius:18px;background:transparent;position:fixed;inset:0;margin:auto;overflow:visible}
.traslado-dialog::backdrop{background:rgba(15,23,42,.35);backdrop-filter:blur(2px)}
.traslado-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 30px 60px rgba(15,23,42,.18)}
.traslado-header{padding:20px 24px 12px;border-bottom:1px solid #edf0f2}
.traslado-title{font-size:1.05rem;font-weight:700;color:#1f2937;margin-bottom:2px}
.traslado-subtitle{font-size:.95rem;color:#7b8a8b;margin:0}
.traslado-body{padding:24px}
.traslado-footer{padding:16px 24px;border-top:1px solid #edf0f2;display:flex;justify-content:flex-end;gap:12px;background:#fff}
.traslado-producto{border:1px solid #e4e8eb;border-radius:14px;padding:14px;display:flex;align-items:center;gap:14px;margin-bottom:18px}
.traslado-avatar{width:42px;height:42px;border-radius:12px;background:#e6f0ea;color:#2d7a59;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0}
.traslado-producto-nombre{font-weight:700;color:#24312f;line-height:1.2;margin-bottom:3px}
.traslado-producto-detalle{font-size:.92rem;color:#7b8a8b;line-height:1.2}
.traslado-label{font-size:.88rem;font-weight:700;color:#5f6f6c;margin-bottom:8px}
.traslado-select,.traslado-input{height:58px;border-radius:14px;border:1px solid #d8e0dd;box-shadow:none!important}
.traslado-hint{font-size:.88rem;color:#2d7a59;margin-top:8px}
.traslado-grid{display:grid;grid-template-columns:1fr 28px 1fr;gap:8px;align-items:center;margin:18px 0}
.traslado-box{border:1px solid #d8e0dd;border-radius:14px;min-height:60px;padding:12px 14px;background:#fbfcfc}
.traslado-box-title{font-weight:700;color:#31413e;line-height:1.2}
.traslado-box-sub{font-size:.82rem;color:#93a09c;margin-top:4px;line-height:1.2}
.traslado-arrow{text-align:center;color:#5aa07f;font-size:1.1rem;font-weight:700}
.traslado-cantidad-wrap{position:relative}
.traslado-cantidad-suffix{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#8a9794;pointer-events:none}
.btn-traslado-cancelar{min-width:92px;border-radius:12px}
.btn-traslado-confirmar{min-width:144px;border-radius:12px;background:#1f7a53;border-color:#1f7a53}
.btn-traslado-confirmar:hover{background:#176543;border-color:#176543}
</style>

<dialog id="modalTraslado" class="traslado-dialog">
    <div class="traslado-card">

        <div class="traslado-header d-flex justify-content-between align-items-start">
            <div>
                <div class="traslado-title">Trasladar existencias</div>
                <p class="traslado-subtitle">Mueve unidades entre ubicaciones</p>
            </div>
            <button type="button" class="btn-close" id="cerrarModalTraslado"></button>
        </div>

        <form method="POST" action="{{ route('inventario.transferir') }}">
            @csrf

            <input type="hidden" name="producto_id" id="traslado_producto_id">
            <input type="hidden" name="ubicacion_origen_id" id="traslado_origen_id">
            <input type="hidden" name="motivo" value="Traslado interno de stock">

            <div class="traslado-body">

                <div class="traslado-producto">
                    <div class="traslado-avatar" id="traslado_producto_avatar">PR</div>
                    <div>
                        <div class="traslado-producto-nombre" id="traslado_producto_nombre">Producto</div>
                        <div class="traslado-producto-detalle" id="traslado_producto_detalle"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="traslado_lote_id" class="traslado-label">
                        Lote a trasladar
                    </label>

                    <select name="lote_id"
                            id="traslado_lote_id"
                            class="form-select traslado-select"
                            required>
                        <option value="">Seleccione un lote</option>
                    </select>

                    <div class="traslado-hint" id="traslado_hint_lote"></div>
                </div>

                <div class="traslado-grid">

                    <div>
                        <div class="traslado-label">Origen</div>

                        <div class="traslado-box">
                            <div class="traslado-box-title" id="traslado_origen_nombre"></div>
                            <div class="traslado-box-sub" id="traslado_origen_disponible"></div>
                        </div>
                    </div>

                    <div class="traslado-arrow">
                        <i class="bi bi-chevron-right"></i>
                    </div>

                    <div>
                        <label for="traslado_destino_id" class="traslado-label">
                            Destino
                        </label>

                        <select name="ubicacion_destino_id"
                                id="traslado_destino_id"
                                class="form-select traslado-select"
                                required>

                            <option value="">Seleccione</option>

                            @foreach($ubicaciones as $ubicacion)
                                <option value="{{ $ubicacion->id }}">
                                    {{ $ubicacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div>
                    <label for="traslado_cantidad" class="traslado-label">
                        Cantidad a trasladar
                    </label>

                    <div class="traslado-cantidad-wrap">
                        <input type="number"
                               name="cantidad"
                               id="traslado_cantidad"
                               class="form-control traslado-input"
                               min="1"
                               required>

                        <span class="traslado-cantidad-suffix">unidades</span>
                    </div>
                </div>

            </div>

            <div class="traslado-footer">
                <button type="button"
                        class="btn btn-outline-secondary btn-traslado-cancelar"
                        id="cancelarModalTraslado">
                    Cancelar
                </button>

                <button type="submit"
                        class="btn btn-success btn-traslado-confirmar">
                    Confirmar traslado
                </button>
            </div>

        </form>
    </div>
</dialog>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const dialog=document.getElementById('modalTraslado');
    if(!dialog)return;

    const stock=@json($stockCompleto);
    const productoId=document.getElementById('traslado_producto_id');
    const origenId=document.getElementById('traslado_origen_id');
    const nombre=document.getElementById('traslado_producto_nombre');
    const detalle=document.getElementById('traslado_producto_detalle');
    const avatar=document.getElementById('traslado_producto_avatar');
    const lote=document.getElementById('traslado_lote_id');
    const origen=document.getElementById('traslado_origen_nombre');
    const disponible=document.getElementById('traslado_origen_disponible');
    const destino=document.getElementById('traslado_destino_id');
    const cantidad=document.getElementById('traslado_cantidad');
    const hint=document.getElementById('traslado_hint_lote');

    const iniciales=n=>(n||'PR').split(' ').filter(Boolean).slice(0,2).map(p=>p[0].toUpperCase()).join('');

    function actualizarLote(){
        const op=lote.options[lote.selectedIndex];

        if(!op?.dataset.cantidad){
            disponible.textContent='0 disponibles';
            cantidad.value='';
            cantidad.removeAttribute('max');
            return;
        }

        const max=Number(op.dataset.cantidad);
        disponible.textContent=max+' disponibles en este lote';
        cantidad.max=max;

        if(Number(cantidad.value)>max) cantidad.value=max;
    }

    document.addEventListener('click',e=>{
        const btn=e.target.closest('.btn-trasladar');
        if(!btn)return;

        e.preventDefault();

        const p=Number(btn.dataset.productoId);
        const u=Number(btn.dataset.ubicacionId);

        productoId.value=p;
        origenId.value=u;
        nombre.textContent=btn.dataset.productoNombre||'Producto';
        detalle.textContent=btn.dataset.productoDetalle||btn.dataset.productoCodigo||'';
        avatar.textContent=iniciales(btn.dataset.productoNombre);
        origen.textContent=btn.dataset.ubicacionNombre||'Origen';

        cantidad.value='';
        cantidad.removeAttribute('max');
        disponible.textContent='';
        hint.textContent='';
        lote.innerHTML='<option value="">Seleccione un lote</option>';
        destino.value='';

        destino.querySelectorAll('option').forEach(op=>{
            op.disabled=op.value!==''&&Number(op.value)===u;
        });

        const registros=stock.filter(s=>
            Number(s.producto_id)===p &&
            Number(s.ubicacion_id)===u &&
            Number(s.cantidad)>0
        );

        const lotes={};

        registros.forEach(s=>{
            const id=Number(s.lote_id);

            if(!lotes[id]){
                lotes[id]={
                    id,
                    numero:s.lote_numero||('Lote '+id),
                    cantidad:0
                };
            }

            lotes[id].cantidad+=Number(s.cantidad);
        });

        const lista=Object.values(lotes);

        lista.forEach(l=>{
            const op=document.createElement('option');
            op.value=l.id;
            op.dataset.cantidad=l.cantidad;
            op.textContent=`${l.numero} · ${l.cantidad} uds.`;
            lote.appendChild(op);
        });

        if(lista.length===1){
            lote.value=lista[0].id;
            hint.innerHTML='<i class="bi bi-check2 me-1"></i>Lote seleccionado automáticamente.';
            actualizarLote();
        }

        dialog.showModal();
    });

    lote.addEventListener('change',actualizarLote);
    document.getElementById('cerrarModalTraslado')?.addEventListener('click',()=>dialog.close());
    document.getElementById('cancelarModalTraslado')?.addEventListener('click',()=>dialog.close());
});
</script>

@endcan