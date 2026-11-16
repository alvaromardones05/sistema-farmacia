@extends('layouts.app')

@section('title', 'Nueva venta')

@section('content')
@include('inventario._styles')

<style>
    /* ================= ESTILOS EXACTOS DEL POS ================= */
    :root {
        /* Mapeamos las variables locales a las globales (con fallback al color claro) */
        --bg: var(--page-bg, #f4f6fa);
        --surface: var(--surface, #fff);
        --border: var(--border, #e5e9f0);
        --border-strong: var(--border-2, #d3dae5);
        --text: var(--text, #0f172a);
        --text-muted: var(--text-muted, #64748b);
        --text-soft: var(--text-soft, #94a3b8);
        --primary: var(--primary, #4f46e5);
        --primary-soft: var(--primary-soft, #eef2ff);
        --primary-hover: var(--primary-hover, #4338ca);
        --success: var(--success, #059669);
        --success-soft: var(--success-soft, #ecfdf5);
        --warning: var(--warning, #d97706);
        --warning-soft: var(--warning-soft, #fffbeb);
        --danger: var(--danger, #dc2626);
        --danger-soft: var(--danger-soft, #fef2f2);
        
        /* Estos se mantienen igual */
        --radius-sm: 10px;
        --radius: 14px;
        --radius-lg: 18px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .pos-container { background: var(--bg); font-family: 'Inter', system-ui, sans-serif; font-size: 16px; color: var(--text); margin: -1.5rem; padding: 1.5rem; min-height: calc(100vh - 60px); display: flex; flex-direction: column;}
    
    .pos-layout { display: grid; grid-template-columns: 1fr 460px; gap: 1.5rem; flex: 1; overflow: hidden; }
    .left-col { display: flex; flex-direction: column; gap: 1.5rem; height: 100%; overflow: hidden; }
    .right-col { display: flex; flex-direction: column; gap: 1.25rem; height: 100%; }

    /* Paneles Izquierda */
    .products-panel { flex: 1; min-height: 0; display: flex; flex-direction: column; background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; }
    .search-area { padding: 1.25rem 1.25rem 0.75rem; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 1rem; }
    .search-bar { position: relative; }
    .search-bar i { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.3rem; }
    .search-bar input { width: 100%; padding: 1rem 1rem 1rem 3.25rem; font-size: 1.05rem; border: 1.5px solid var(--border-strong); border-radius: var(--radius); background: var(--bg); transition: all 0.15s; font-family: inherit; font-weight: 500; }
    .search-bar input:focus { outline: none; border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 4px var(--primary-soft); }

    .product-list { flex: 1; overflow-y: auto; padding-bottom: 1rem; }
    .product-row { display: flex; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.15s; }
    .product-row:hover { background: var(--bg); }
    .prod-main { flex: 1; display: flex; align-items: center; gap: 1rem; }
    .prod-name { font-weight: 600; font-size: 1.05rem; color: var(--text); }
    .prod-badge { font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; background: var(--warning-soft); color: var(--warning); font-weight: 700; margin-left: 0.5rem; }
    .prod-meta { font-size: 0.85rem; color: var(--text-muted); font-family: monospace; width: 100px; }
    .prod-price-col { text-align: right; margin-right: 1.5rem; min-width: 100px; }
    .prod-price { font-weight: 700; font-size: 1.15rem; }
    .prod-stock { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;}
    .prod-stock.low { color: var(--danger); font-weight: 600; }
    .btn-add-row { width: 42px; height: 42px; border-radius: 10px; background: var(--primary-soft); color: var(--primary); border: none; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; cursor: pointer; transition: all 0.15s; flex-shrink: 0; }
    .product-row:hover .btn-add-row { background: var(--primary); color: white; transform: scale(1.05); }

    /* Carrito */
    .cart-panel { flex: 0 0 320px; background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; overflow: hidden; }
    .cart-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg); }
    .cart-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; color: var(--text); }
    .cart-items { flex: 1; overflow-y: auto; padding: 0 1.5rem; }
    .cart-row { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px dashed var(--border-strong); }
    .ci-info { flex: 1; }
    .ci-name { font-weight: 600; font-size: 1rem; }
    .ci-price { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.15rem; }
    .ci-controls { display: flex; align-items: center; gap: 0.75rem; }
    .qty-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border-strong); background: var(--surface); color: var(--text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; font-size: 1rem; }
    .qty-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-soft); }
    .ci-qty { font-weight: 700; font-size: 1rem; width: 24px; text-align: center; }
    .ci-total { font-weight: 700; font-size: 1.1rem; width: 90px; text-align: right; }
    .btn-del { background: transparent; border: none; color: var(--text-soft); cursor: pointer; font-size: 1.3rem; margin-left: 0.75rem; transition: color 0.15s;}
    .btn-del:hover { color: var(--danger); }
    .empty-cart { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); gap: 0.5rem; }
    .empty-cart i { font-size: 2.5rem; opacity: 0.4; }

    /* Paneles Derecha */
    .card-pos { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); }
    .client-box { display: flex; align-items: center; gap: 1rem; }
    .client-avatar { width: 50px; height: 50px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
    .client-info { flex: 1; }
    .client-name { font-weight: 700; font-size: 1.15rem; margin-bottom: 0.1rem; }
    .client-meta { font-size: 0.85rem; color: var(--text-muted); }
    .client-conv { display: inline-flex; align-items: center; gap: 0.3rem; background: var(--success-soft); color: var(--success); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; margin-top: 0.3rem; }
    .btn-text { background: transparent; border: none; color: var(--primary); font-size: 0.95rem; font-weight: 600; cursor: pointer; padding: 0; }
    .btn-text:hover { text-decoration: underline; }

    .prescription-box { display: flex; align-items: center; gap: 1rem; }
    .prescription-icon { width: 50px; height: 50px; border-radius: 12px; background: var(--bg); border: 1px solid var(--border-strong); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    .prescription-info { flex: 1; }
    .prescription-title { font-size: 1.05rem; font-weight: 600; margin-bottom: 0.1rem; }
    .prescription-desc { font-size: 0.85rem; color: var(--text-muted); }

    .checkout-panel { margin-top: auto; display: flex; flex-direction: column; padding: 1.5rem; }
    .totals-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 1.05rem; color: var(--text-muted); font-weight: 500; }
    .totals-row.total { margin-top: 0.5rem; padding-top: 1.25rem; border-top: 2px solid var(--border-strong); font-size: 2.2rem; font-weight: 800; color: var(--text); margin-bottom: 1.5rem; } 
    .payment-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem; }
    .payment-btn { padding: 0.85rem; border: 1.5px solid var(--border); background: var(--surface); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; transition: all 0.15s; font-size: 0.95rem; font-weight: 600; color: var(--text-muted); font-family: inherit; }
    .payment-btn i { font-size: 1.2rem; }
    .payment-btn:hover { border-color: var(--primary); color: var(--primary); }
    .payment-btn.active { border-color: var(--primary); background: var(--primary-soft); color: var(--primary); font-weight: 700; }
    .btn-checkout { width: 100%; padding: 1.25rem; background: var(--primary); color: white; border: none; border-radius: var(--radius); font-size: 1.15rem; font-weight: 700; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 0.75rem; transition: all 0.15s; font-family: inherit; box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
    .btn-checkout:hover:not(:disabled) { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79,70,229,0.4); }
    .btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
    
    /* Clases utilitarias adaptadas */
    .d-none { display: none !important; }
</style>

<div class="container-fluid px-0 inventario-modulo">
    <div class="pos-container">
        
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if (!$aperturaCaja)
            <div class="card-pos mx-auto mt-5" style="max-width: 600px;">
                <h5 class="card-title mb-3"><span class="badge text-bg-secondary">Sin caja abierta</span></h5>
                <p class="text-muted mb-4">Debes abrir una caja antes de registrar una venta.</p>
                <form action="{{ route('caja.abrir') }}" method="POST" class="row g-3">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('ventas.create') }}">
                    <div class="col-md-6">
                        <label class="form-label">Caja *</label>
                        <select name="caja_id" class="form-select" required>
                            <option value="">Selecciona una caja</option>
                            @foreach (\App\Models\Caja::activas()->get() as $caja)
                                <option value="{{ $caja->id }}">{{ $caja->descripcion }} ({{ $caja->numero_caja }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Saldo inicial *</label>
                        <input type="number" name="saldo_inicial" class="form-control" min="0" step="1" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Abrir caja</button>
                    </div>
                </form>
            </div>
        @elseif ($ubicacionesVenta->isEmpty())
            <div class="alert alert-danger">No existe una Vitrina ni una Vitrina Refrigerada activa configurada.</div>
        @else

            @if (!$ubicacionesVenta->has('vitrina') || !$ubicacionesVenta->has('vitrina_refrigerada'))
                <div class="alert alert-warning mb-3">
                    No existe una {{ !$ubicacionesVenta->has('vitrina') ? 'Vitrina' : 'Vitrina Refrigerada' }} activa configurada.
                    Solo se ofrecen productos con stock en su ubicación de venta correspondiente.
                </div>
            @endif

            {{-- 1. DEFINICIÓN DE VARIABLES PARA JS --}}
            @php
                $pacientesParaJs = $pacientes->map(fn ($paciente) => [
                    'id' => $paciente->id,
                    'rut' => $paciente->rut,
                    'nombre' => $paciente->nombreCompleto(),
                    'convenio_id' => $paciente->convenio_id,
                ])->values();

                $pacienteSeleccionado = $pacientes->firstWhere(
                    'id',
                    (int) old('paciente_id', request()->integer('paciente_id'))
                );
            @endphp

            <form action="{{ route('ventas.store') }}" method="POST" id="form-venta" style="display: contents;">
                @csrf

                <div class="pos-layout">
                    <!-- COLUMNA IZQUIERDA -->
                    <div class="left-col">
                        <!-- Búsqueda y Lista de Productos -->
                        <section class="products-panel">
                            <div class="search-area">
                                <div class="search-bar">
                                    <i class="bi bi-upc-scan"></i>
                                    <input type="text" id="searchInput" placeholder="Escanear código o buscar por nombre... (F2)" autocomplete="off">
                                </div>
                                <select id="producto-select" class="d-none">
                                    <option value="">Selecciona un producto</option>
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto['id'] }}"
                                            data-nombre="{{ $producto['nombre'] }}"
                                            data-codigo-barra="{{ $producto['codigo_barra'] }}"
                                            data-precio="{{ $producto['precio_neto'] }}"
                                            data-disponible="{{ $producto['disponible'] }}"
                                            data-requiere-receta="{{ $producto['requiere_receta'] ? '1' : '0' }}"
                                            data-tipo-receta="{{ $producto['tipo_receta'] }}">
                                            {{ $producto['nombre'] }} ({{ $producto['codigo'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="product-list" id="productList"></div>
                        </section>

                        <!-- Carrito -->
                        <section class="cart-panel">
                            <div class="cart-header">
                                <h3><i class="bi bi-cart3 text-primary"></i> Carro de Venta</h3>
                                <span id="cartCount" style="font-size: 0.9rem; background: var(--primary-soft); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 12px; font-weight: 700;">0 ítems</span>
                            </div>
                            <div class="cart-items" id="cartItems"></div>
                            <div class="empty-cart" id="emptyCart">
                                <i class="bi bi-cart-x"></i>
                                <span>Aún no hay productos agregados.</span>
                            </div>
                        </section>
                    </div>

                    <!-- COLUMNA DERECHA -->
                    <aside class="right-col">
                        <!-- Cliente -->
                        <div class="card-pos">
                            <div class="client-box" id="clientBox">
                                <div class="client-avatar"><i class="bi bi-person"></i></div>
                                <div class="client-info">
                                    <div class="client-name" id="display-paciente-nombre">Consumidor Final</div>
                                    <div class="client-meta" id="paciente-resultado">Sin registro</div>
                                    <div id="convenio-actual" class="client-conv" style="display:none;"></div>
                                </div>
                                <button type="button" class="btn-text" id="btn-toggle-rut">Buscar (F3)</button>
                            </div>

                            <div id="wrapper-buscar-rut" class="mt-3 d-none">
                                <div class="d-flex gap-2">
                                    <input type="text" id="paciente-rut" class="form-control form-control-sm" placeholder="12.345.678-9" autocomplete="off">
                                    <button type="button" id="buscar-paciente" class="btn btn-primary btn-sm">Buscar</button>
                                </div>
                                @can('gestionar_pacientes')
                                    <a href="{{ route('pacientes.create', ['origen' => 'venta']) }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">+ Nuevo Cliente</a>
                                @endcan
                            </div>
                            <input type="hidden" name="paciente_id" id="paciente_id" value="{{ $pacienteSeleccionado?->id }}">
                        </div>

                        <!-- Recetas -->
                        <div class="card-pos" id="recetas-tarjeta" @can('gestionar_recetas') data-registro-url="{{ route('recetas.create') }}" @endcan>
                            <div class="prescription-box">
                                <div class="prescription-icon" id="presc-icon"><i class="bi bi-file-medical"></i></div>
                                <div class="prescription-info">
                                    <div class="prescription-title" id="presc-title">Sin receta</div>
                                    <div class="prescription-desc" id="recetas-resumen">No requerida</div>
                                </div>
                                <button type="button" class="btn-text" id="btn-toggle-recetas">Gestionar (F4)</button>
                            </div>

                            <div id="panel-recetas-detalle" class="mt-3 border-top pt-3 d-none">
                                <p id="recetas-paciente" class="small text-muted mb-2" role="status" aria-live="polite">Selecciona un paciente para consultar o registrar sus recetas.</p>
                                @can('gestionar_recetas')
                                    <div id="recetas-acciones" class="d-flex flex-wrap gap-2 mb-2 d-none">
                                        <button type="button" id="ver-recetas-paciente" class="btn btn-sm btn-outline-primary" aria-expanded="false">Ver recetas</button>
                                        <a id="registrar-receta-paciente" class="btn btn-sm btn-outline-secondary">+ Registrar receta</a>
                                    </div>
                                    <div id="recetas-paciente-panel" class="mb-3" hidden>
                                        <h2 id="recetas-paciente-titulo" class="h6" tabindex="-1">Recetas registradas</h2>
                                        <div id="recetas-paciente-lista"></div>
                                    </div>
                                @endcan
                                <div id="recetas-mensaje" class="small text-warning" role="status" aria-live="polite"></div>
                                <div id="recetas-productos"></div>
                                
                                <div id="receta-producto-bloque" class="mt-3 p-3 bg-light rounded" hidden>
                                    <h2 id="receta-producto-titulo" class="h6">Buscar receta</h2>
                                    <p id="receta-producto-aviso" class="small text-muted" role="status"></p>
                                    <select id="receta-producto" class="form-select form-select-sm mb-2"></select>
                                    <p id="receta-producto-info" class="small text-muted"></p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" id="asociar-receta" class="btn btn-primary btn-sm">Seleccionar</button>
                                        @can('gestionar_recetas')
                                            <a id="registrar-receta" href="{{ route('recetas.create') }}" class="btn btn-outline-secondary btn-sm">Registrar nueva</a>
                                        @endcan
                                        <button type="button" id="cerrar-busqueda-receta" class="btn btn-link btn-sm text-muted p-0 ms-auto">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout -->
                        <div class="card-pos checkout-panel">
                            <div>
                                <div class="totals-row">
                                    <span>Subtotal</span>
                                    <span id="totSubtotal">$0</span>
                                </div>
                                <div class="totals-row total">
                                    <span>Total</span>
                                    <span>$<span id="total-aproximado">0</span></span>
                                </div>
                                <small class="text-muted d-block text-end" style="margin-top:-1rem; margin-bottom:1.5rem;">Incluye IVA 19%.</small>
                            </div>

                            <div class="payment-grid">
                                <button type="button" class="payment-btn" onclick="selectPayment('efectivo', this)"><i class="bi bi-cash"></i> Efectivo</button>
                                <button type="button" class="payment-btn" onclick="selectPayment('debito', this)"><i class="bi bi-credit-card"></i> Débito</button>
                                <button type="button" class="payment-btn" onclick="selectPayment('credito', this)"><i class="bi bi-credit-card-2-front"></i> Crédito</button>
                                <button type="button" class="payment-btn" onclick="selectPayment('transferencia', this)"><i class="bi bi-bank"></i> Transf.</button>
                            </div>

                            <!-- AGREGAR ESTO EN EL BLOQUE DE PAGO -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Documento *</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_documento" id="doc_boleta" value="boleta" checked onchange="toggleFactura()">
                                        <label class="form-check-label" for="doc_boleta">Boleta</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_documento" id="doc_factura" value="factura" onchange="toggleFactura()">
                                        <label class="form-check-label" for="doc_factura">Factura (Empresas)</label>
                                    </div>
                                </div>
                            </div>

                            <!-- CAMPOS DE FACTURA (Ocultos por defecto) -->
                            <div id="campos_factura" class="row g-3 mb-3 d-none p-3 bg-light rounded border">
                                <div class="col-md-4">
                                    <label class="form-label">RUT Empresa *</label>
                                    <input type="text" name="rut_empresa" id="rut_empresa" class="form-control" placeholder="76.543.210-K">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Razón Social *</label>
                                    <input type="text" name="razon_social" id="razon_social" class="form-control" placeholder="Supermercados Unidos S.A.">
                                </div>
                            </div>
                            
                            <select name="metodo_pago" id="metodo_pago_real" class="d-none" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                            </select>

                            <input type="text" name="referencia_transaccion" class="form-control form-control-sm mb-3" maxlength="100" placeholder="Ref. Transacción (Opcional)">

                            <div id="detalles-ocultos"></div>

                            <button type="submit" class="btn-checkout" id="btn-confirmar" disabled>
                                <i class="bi bi-check-circle-fill"></i> Confirmar venta
                            </button>
                        </div>
                    </aside>
                </div>
            </form>

            {{-- 2. SCRIPT DENTRO DEL @ELSE PARA QUE LAS VARIABLES EXISTAN --}}
            <script>
                let carrito = [];
                const convenios = @json($conveniosParaJs);
                const pacientes = @json($pacientesParaJs);
                const recetasDisponibles = @json($recetasParaJs);
                const recetasPaciente = @json($recetasPacienteParaJs);

                const pacienteInput = document.getElementById('paciente_id');
                const pacienteRutInput = document.getElementById('paciente-rut');
                const pacienteResultado = document.getElementById('paciente-resultado');
                const convenioActual = document.getElementById('convenio-actual');
                const productoSelect = document.getElementById('producto-select');
                const searchInput = document.getElementById('searchInput');
                const productList = document.getElementById('productList');
                
                const recetaProducto = document.getElementById('receta-producto');
                const bloqueReceta = document.getElementById('receta-producto-bloque');
                const recetasMensaje = document.getElementById('recetas-mensaje');
                const recetasResumen = document.getElementById('recetas-resumen');
                const recetasTarjeta = document.getElementById('recetas-tarjeta');
                const recetasProductos = document.getElementById('recetas-productos');
                const recetasPacientePanel = document.getElementById('recetas-paciente-panel');
                const verRecetasPaciente = document.getElementById('ver-recetas-paciente');
                const asociarReceta = document.getElementById('asociar-receta');
                
                let indiceReceta = null;
                let pacienteRecetas = pacienteInput.value;
                let selectedPaymentMethod = null;

                const carritoItems = document.getElementById('cartItems');
                const emptyCart = document.getElementById('emptyCart');
                const detallesOcultos = document.getElementById('detalles-ocultos');
                const btnConfirmar = document.getElementById('btn-confirmar');
                const totalAproximado = document.getElementById('total-aproximado');
                const totSubtotal = document.getElementById('totSubtotal');

                // === FUNCIÓN CLAVE PARA LA REDIRECCIÓN DE RECETAS ===
                function urlRegistroReceta(item = null) {
                    if (!recetasTarjeta.dataset.registroUrl) return '#';
                    const url = new URL(recetasTarjeta.dataset.registroUrl, window.location.origin);
                    if (pacienteInput.value) url.searchParams.set('paciente_id', pacienteInput.value);
                    if (item) url.searchParams.set('producto_id', item.producto_id);
                    url.searchParams.set('origen', 'venta');
                    return url.toString();
                }

                function renderCatalog() {
                    const query = searchInput.value.toLowerCase().trim();
                    let filtered = Array.from(productoSelect.options).slice(1);

                    if (query) {
                        filtered = filtered.filter(opt => 
                            opt.dataset.nombre.toLowerCase().includes(query) || 
                            (opt.dataset.codigoBarra && opt.dataset.codigoBarra.includes(query))
                        );
                    }

                    if (filtered.length === 0) {
                        productList.innerHTML = `<div style="padding: 3rem; text-align: center; color: var(--text-muted);"><i class="bi bi-search" style="display:block; font-size:2rem; margin-bottom:0.5rem;"></i>No se encontraron productos</div>`;
                        return;
                    }

                    productList.innerHTML = filtered.map(opt => {
                        const isLow = Number(opt.dataset.disponible) <= 10;
                        const req = opt.dataset.requiereReceta === '1' ? '<span class="prod-badge">RECETA</span>' : '';
                        return `
                        <div class="product-row" onclick="agregarDesdeLista('${opt.value}')">
                            <div class="prod-main">
                                <div class="prod-name">${opt.dataset.nombre} ${req}</div>
                                <div class="prod-meta">${opt.dataset.codigoBarra || 'S/C'}</div>
                            </div>
                            <div class="prod-price-col">
                                <div class="prod-price">$${Number(opt.dataset.precio).toLocaleString('es-CL')}</div>
                                <div class="prod-stock ${isLow ? 'low' : ''}">Stock: ${opt.dataset.disponible}</div>
                            </div>
                            <button class="btn-add-row"><i class="bi bi-plus-lg"></i></button>
                        </div>
                        `;
                    }).join('');
                }
                searchInput.addEventListener('input', renderCatalog);

                function normalizarRut(rut) { return String(rut).replace(/[.\-\s]/g, '').toUpperCase(); }
                function obtenerPacienteActual() { return pacientes.find(p => String(p.id) === pacienteInput.value) ?? null; }
                
                function recetasParaProducto(productoId, tipo) {
                    return recetasDisponibles.filter(r => String(r.paciente_id) === pacienteInput.value
                        && String(r.producto_id) === String(productoId)
                        && (tipo === 'ninguna' || r.tipo === tipo));
                }
                function recetaDeItem(item) { return recetasParaProducto(item.producto_id, item.tipo_receta).find(r => String(r.id) === String(item.receta_detalle_id)); }
                function recetaValidaItem(item) { const receta = recetaDeItem(item); return !!receta && item.cantidad <= receta.disponible; }

                function obtenerConvenioActual() {
                    const paciente = obtenerPacienteActual();
                    return paciente?.convenio_id ? convenios[paciente.convenio_id] ?? null : null;
                }

                function calcularEstimacion(productoId, precio) {
                    const convenio = obtenerConvenioActual();
                    if (!convenio) return { precioFinal: precio, texto: 'Sin convenio' };
                    
                    const cobertura = convenio.coberturas[String(productoId)] ?? null;
                    if (cobertura?.copago_fijo > 0) {
                        const copago = Math.min(precio, Number(cobertura.copago_fijo));
                        return { precioFinal: copago, texto: `Copago fijo $${Math.round(copago).toLocaleString('es-CL')}` };
                    }
                    if (cobertura?.copago_pct !== null && cobertura?.copago_pct !== undefined) {
                        const copagoPct = Math.max(0, Math.min(100, Number(cobertura.copago_pct)));
                        return { precioFinal: precio * (copagoPct / 100), texto: `Copago ${copagoPct}%` };
                    }
                    const descuento = Math.max(0, Math.min(100, Number(convenio.descuento_pct ?? 0)));
                    return { precioFinal: precio * (1 - descuento / 100), texto: descuento > 0 ? `${descuento}% desc.` : 'Sin desc.' };
                }

                function mostrarPaciente(mensaje = 'Venta libre') {
                    const paciente = obtenerPacienteActual();
                    const convenio = obtenerConvenioActual();

                    if (pacienteRecetas !== pacienteInput.value) {
                        if (recetasPacientePanel) recetasPacientePanel.hidden = true;
                        const teniaRecetas = carrito.some(item => item.receta_detalle_id);
                        carrito.forEach(item => { item.receta_detalle_id = null; });
                        if (teniaRecetas) recetasMensaje.textContent = 'Se quitaron las recetas porque cambió el paciente.';
                        pacienteRecetas = pacienteInput.value;
                    }

                    if (paciente) {
                        document.getElementById('display-paciente-nombre').textContent = paciente.nombre;
                        pacienteResultado.textContent = `RUT: ${paciente.rut}`;
                        if (convenio) {
                            convenioActual.style.display = 'inline-flex';
                            convenioActual.innerHTML = `<i class="bi bi-tag-fill"></i> ${convenio.nombre}`;
                        } else {
                            convenioActual.style.display = 'none';
                        }
                    } else {
                        document.getElementById('display-paciente-nombre').textContent = 'Consumidor Final';
                        pacienteResultado.textContent = mensaje;
                        convenioActual.style.display = 'none';
                    }

                    actualizarRecetasPaciente();
                    renderCarrito();
                }

                function buscarPaciente() {
                    const rut = normalizarRut(pacienteRutInput.value);
                    const paciente = rut ? pacientes.find(p => normalizarRut(p.rut) === rut) : null;
                    pacienteInput.value = paciente ? String(paciente.id) : '';
                    mostrarPaciente(rut ? 'No encontrado. Puedes registrar uno nuevo.' : 'Ingresa un RUT.');
                }

                document.getElementById('btn-toggle-rut').addEventListener('click', () => {
                    const wrapper = document.getElementById('wrapper-buscar-rut');
                    wrapper.classList.toggle('d-none');
                    if (!wrapper.classList.contains('d-none')) pacienteRutInput.focus();
                });
                document.getElementById('buscar-paciente').addEventListener('click', buscarPaciente);
                pacienteRutInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscarPaciente(); } });
                pacienteRutInput.addEventListener('input', () => { pacienteInput.value = ''; mostrarPaciente('Presiona Buscar.'); });

                function agregarDesdeLista(prodId) {
                    const opcion = Array.from(productoSelect.options).find(o => o.value === prodId);
                    if (!opcion) return;

                    const precio = Number(opcion.dataset.precio);
                    const disponible = Number(opcion.dataset.disponible);
                    const cantidad = 1;

                    const existente = carrito.find(item => item.producto_id === prodId);
                    const cantidadTotal = cantidad + (existente?.cantidad ?? 0);

                    if (cantidadTotal > disponible) { alert('Stock insuficiente.'); return; }

                    if (existente) {
                        existente.cantidad = cantidadTotal;
                    } else {
                        carrito.push({
                            producto_id: prodId,
                            nombre: opcion.dataset.nombre,
                            cantidad,
                            precio,
                            requiere_receta: opcion.dataset.requiereReceta === '1',
                            tipo_receta: opcion.dataset.tipoReceta,
                            receta_detalle_id: null
                        });
                    }
                    renderCarrito();
                }

                function procesarCodigoBarra() {
                    const codigo = searchInput.value.trim();
                    if (!codigo) return;
                    const opcion = Array.from(productoSelect.options).find(opt => String(opt.dataset.codigoBarra || '').trim() === codigo);
                    
                    if (!opcion) { alert(`Código ${codigo} no encontrado.`); return; }
                    
                    agregarDesdeLista(opcion.value);
                    searchInput.value = '';
                    renderCatalog();
                }

                function renderCarrito() {
                    indiceReceta = null;
                    bloqueReceta.hidden = true;
                    carritoItems.innerHTML = '';
                    detallesOcultos.innerHTML = '';
                    renderRecetas();

                    if (!carrito.length) {
                        carritoItems.style.display = 'none';
                        emptyCart.style.display = 'flex';
                        document.getElementById('cartCount').textContent = '0 ítems';
                        totalAproximado.textContent = '0';
                        totSubtotal.textContent = '$0';
                        btnConfirmar.disabled = true;
                        return;
                    }

                    emptyCart.style.display = 'none';
                    carritoItems.style.display = 'block';
                    document.getElementById('cartCount').textContent = `${carrito.reduce((s, i) => s + i.cantidad, 0)} ítems`;

                    let totalConIva = 0;
                    let recetasValidas = true;

                    carrito.forEach((item, index) => {
                        const recetaValida = item.requiere_receta && recetaValidaItem(item);
                        if (item.requiere_receta && !recetaValida) recetasValidas = false;
                        
                        const estimacion = calcularEstimacion(item.producto_id, item.precio);
                        const subtotalNeto = estimacion.precioFinal * item.cantidad;
                        totalConIva += subtotalNeto * 1.19;

                        carritoItems.insertAdjacentHTML('beforeend', `
                            <div class="cart-row">
                                <div class="ci-info">
                                    <div class="ci-name">${item.nombre}</div>
                                    <div class="ci-price">$${Math.round(item.precio).toLocaleString('es-CL')} c/u 
                                        ${item.requiere_receta ? `<span class="badge text-bg-${recetaValida ? 'success' : 'warning'}" style="font-size:0.65rem; cursor:pointer;" data-buscar-receta="${index}">${recetaValida ? 'OK Receta' : 'Falta Receta'}</span>` : ''}
                                    </div>
                                </div>
                                <div class="ci-controls">
                                    <button type="button" class="qty-btn" onclick="updateQty(${index}, -1)"><i class="bi bi-dash"></i></button>
                                    <span class="ci-qty">${item.cantidad}</span>
                                    <button type="button" class="qty-btn" onclick="updateQty(${index}, 1)"><i class="bi bi-plus"></i></button>
                                </div>
                                <div class="ci-total">$${Math.round(subtotalNeto).toLocaleString('es-CL')}</div>
                                <button type="button" class="btn-del" onclick="removeFromCart(${index})"><i class="bi bi-x-circle-fill"></i></button>
                            </div>
                        `);

                        detallesOcultos.insertAdjacentHTML('beforeend', `
                            <input type="hidden" name="detalles[${index}][producto_id]" value="${item.producto_id}">
                            <input type="hidden" name="detalles[${index}][cantidad]" value="${item.cantidad}">
                            ${item.receta_detalle_id ? `<input type="hidden" name="detalles[${index}][receta_detalle_id]" value="${Number(item.receta_detalle_id)}">` : ''}
                        `);
                    });

                    const subtotalNetoTotal = totalConIva / 1.19;
                    totSubtotal.textContent = '$' + Math.round(subtotalNetoTotal).toLocaleString('es-CL');
                    totalAproximado.textContent = Math.round(totalConIva).toLocaleString('es-CL');

                    btnConfirmar.disabled = !recetasValidas || !selectedPaymentMethod;
                }

                function updateQty(index, delta) {
                    const item = carrito[index];
                    const opcion = Array.from(productoSelect.options).find(o => o.value === item.producto_id);
                    const max = Number(opcion.dataset.disponible);
                    const newQty = item.cantidad + delta;
                    
                    if (newQty < 1) { removeFromCart(index); return; }
                    if (newQty > max) { alert('Límite de stock alcanzado'); return; }
                    
                    item.cantidad = newQty;
                    renderCarrito();
                }

                function removeFromCart(index) {
                    carrito.splice(index, 1);
                    renderCarrito();
                }

                function selectPayment(method, btn) {
                    document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    selectedPaymentMethod = method;
                    document.getElementById('metodo_pago_real').value = method;
                    renderCarrito();
                }

                function requerirPacienteRecetas() {
                    if (!obtenerPacienteActual()) {
                        recetasMensaje.textContent = 'Selecciona primero un paciente.';
                        pacienteRutInput.focus(); return false;
                    }
                    return true;
                }

                document.getElementById('btn-toggle-recetas').addEventListener('click', () => {
                    const panel = document.getElementById('panel-recetas-detalle');
                    panel.classList.toggle('d-none');
                    if (!panel.classList.contains('d-none')) abrirRecetas();
                });

                function abrirRecetas(index = null) {
                    if (!requerirPacienteRecetas()) return;
                    document.getElementById('panel-recetas-detalle').classList.remove('d-none');
                    
                    const pendiente = carrito.findIndex(item => item.requiere_receta && !recetaValidaItem(item));
                    
                    // Si no hay recetas pendientes por asociar, mostramos la lista del paciente
                    if (index === null && pendiente < 0) {
                        abrirRecetasPaciente();
                        return;
                    }
                    
                    indiceReceta = index ?? pendiente;
                    const item = carrito[indiceReceta];
                    if (!item?.requiere_receta) return;

                    const recetas = recetasParaProducto(item.producto_id, item.tipo_receta).filter(r => r.disponible >= item.cantidad);
                    document.getElementById('receta-producto-titulo').textContent = `Recetas para ${item.nombre}`;
                    document.getElementById('receta-producto-aviso').textContent = recetas.length
                        ? `Paciente: ${obtenerPacienteActual().nombre}. Cantidad: ${item.cantidad}.`
                        : 'No existe una receta válida para este producto y cantidad.';
                    
                    recetaProducto.replaceChildren(new Option(recetas.length ? 'Selecciona una receta' : 'Sin recetas válidas', ''));
                    recetas.forEach(r => recetaProducto.add(new Option(`#${r.numero} · disp ${r.disponible}`, String(r.id))));
                    recetaProducto.value = String(item.receta_detalle_id || (recetas.length === 1 ? recetas[0].id : ''));

                    const registrar = document.getElementById('registrar-receta');
                    if (registrar) registrar.href = urlRegistroReceta(item);

                    bloqueReceta.hidden = false;
                    actualizarInformacionReceta();
                }

                function abrirRecetasPaciente() {
                        if (!requerirPacienteRecetas()) return;
                        if (!recetasPacientePanel) {
                            recetasMensaje.textContent = 'No tienes permiso para consultar el módulo de recetas.';
                            return;
                        }
                        recetasPacientePanel.hidden = false;
                        verRecetasPaciente.setAttribute('aria-expanded', 'true');
                        document.getElementById('recetas-paciente-titulo').focus();
                    }

                    verRecetasPaciente?.addEventListener('click', () => {
                        if (!requerirPacienteRecetas()) return;
                        if (recetasPacientePanel.hidden) {
                            abrirRecetasPaciente();
                        } else {
                            recetasPacientePanel.hidden = true;
                            verRecetasPaciente.setAttribute('aria-expanded', 'false');
                        }
                    });

                function actualizarRecetasPaciente() {
                    const paciente = obtenerPacienteActual();
                    const recetas = recetasPaciente.filter(r => String(r.paciente_id) === pacienteInput.value);
                    document.getElementById('recetas-paciente').textContent = paciente ? `${paciente.nombre}` : 'Selecciona paciente.';
                    
                    if (verRecetasPaciente) {
                        document.getElementById('recetas-acciones').classList.toggle('d-none', !paciente);
                    }

                    const registrarBtn = document.getElementById('registrar-receta-paciente');
                    if (registrarBtn) {
                        if (paciente) registrarBtn.href = urlRegistroReceta();
                        else registrarBtn.removeAttribute('href');
                    }
                    
                    const lista = document.getElementById('recetas-paciente-lista');
                    lista.innerHTML = '';
                    recetas.forEach(receta => {
                        lista.insertAdjacentHTML('beforeend', `
                            <div class="border-top py-1 small">
                                <strong>${receta.numero}</strong> - ${receta.disponible ? '<span class="text-success">Disponible</span>' : '<span class="text-muted">Sin saldo</span>'}
                            </div>
                        `);
                    });
                }

                function renderRecetas() {
                    const conReceta = carrito.filter(item => item.requiere_receta);
                    recetasResumen.textContent = conReceta.length
                        ? (conReceta.every(recetaValidaItem) ? 'Receta validada' : `${conReceta.filter(item => !recetaValidaItem(item)).length} requieren receta`)
                        : 'No requerida';
                    
                    recetasResumen.className = conReceta.length && !conReceta.every(recetaValidaItem) ? 'prescription-desc text-warning fw-bold' : 'prescription-desc text-success fw-bold';
                    
                    if (conReceta.length > 0 && conReceta.every(recetaValidaItem)) {
                        document.getElementById('presc-icon').style.background = 'var(--success-soft)';
                        document.getElementById('presc-icon').style.color = 'var(--success)';
                        document.getElementById('presc-icon').innerHTML = '<i class="bi bi-check-circle-fill"></i>';
                        document.getElementById('presc-title').textContent = 'Receta adjunta';
                    } else if (conReceta.length > 0) {
                        document.getElementById('presc-icon').style.background = 'var(--warning-soft)';
                        document.getElementById('presc-icon').style.color = 'var(--warning)';
                        document.getElementById('presc-icon').innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
                        document.getElementById('presc-title').textContent = 'Receta pendiente';
                    } else {
                        document.getElementById('presc-icon').style.background = 'var(--bg)';
                        document.getElementById('presc-icon').style.color = 'var(--text-muted)';
                        document.getElementById('presc-icon').innerHTML = '<i class="bi bi-file-medical"></i>';
                        document.getElementById('presc-title').textContent = 'Sin receta';
                    }

                    recetasProductos.innerHTML = '';
                    carrito.forEach((item, index) => {
                        if (!item.requiere_receta) return;
                        const valida = recetaValidaItem(item);
                        recetasProductos.insertAdjacentHTML('beforeend', `
                            <div class="border-top pt-2 mt-2">
                                <strong class="d-block small">${item.nombre}</strong>
                                <span class="badge text-bg-${valida ? 'success' : 'warning'} mt-1">${valida ? 'OK' : 'Falta'}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-1 w-100" data-buscar-receta="${index}">${valida ? 'Cambiar' : 'Buscar'}</button>
                            </div>
                        `);
                    });
                }

                function actualizarInformacionReceta() {
                    const item = carrito[indiceReceta];
                    const receta = item ? recetasParaProducto(item.producto_id, item.tipo_receta).find(r => String(r.id) === recetaProducto.value) : null;
                    document.getElementById('receta-producto-info').textContent = receta ? `Disp: ${receta.disponible} · Requiere: ${item.cantidad}` : '';
                    asociarReceta.disabled = !receta || receta.disponible < item.cantidad;
                }

                carritoItems.addEventListener('click', e => {
                    const btnReceta = e.target.closest('span[data-buscar-receta]');
                    if (btnReceta) abrirRecetas(Number(btnReceta.dataset.buscarReceta));
                });
                recetasProductos.addEventListener('click', e => {
                    const btnReceta = e.target.closest('button[data-buscar-receta]');
                    if (btnReceta) abrirRecetas(Number(btnReceta.dataset.buscarReceta));
                });
                recetaProducto.addEventListener('change', actualizarInformacionReceta);
                document.getElementById('cerrar-busqueda-receta').addEventListener('click', () => { bloqueReceta.hidden = true; });
                
                asociarReceta.addEventListener('click', () => {
                    const item = carrito[indiceReceta];
                    const receta = recetasParaProducto(item.producto_id, item.tipo_receta).find(r => String(r.id) === recetaProducto.value);
                    if (receta && receta.disponible >= item.cantidad) {
                        item.receta_detalle_id = receta.id;
                        recetasMensaje.textContent = '';
                        renderCarrito();
                    }
                });

                document.addEventListener('keydown', e => {
                    if (e.key === 'F2') { e.preventDefault(); searchInput.focus(); searchInput.select(); }
                    if (e.key === 'F3') { e.preventDefault(); document.getElementById('btn-toggle-rut').click(); }
                    if (e.key === 'F4') { e.preventDefault(); document.getElementById('btn-toggle-recetas').click(); }
                });

                searchInput.addEventListener('keydown', e => {
                    if (e.key === 'Enter') { e.preventDefault(); procesarCodigoBarra(); }
                });

                document.getElementById('form-venta').addEventListener('submit', e => {
                    const pendiente = carrito.find(item => item.requiere_receta && !recetaValidaItem(item));
                    if (pendiente) {
                        e.preventDefault();
                        recetasMensaje.textContent = `Falta receta para ${pendiente.nombre}.`;
                        abrirRecetas();
                    }
                });

                const pacienteInicial = obtenerPacienteActual();
                pacienteRutInput.value = pacienteInicial?.rut ?? '';
                mostrarPaciente();
                renderCatalog();
                renderCarrito();
                searchInput.focus();

                function toggleFactura() {
                    const esFactura = document.getElementById('doc_factura').checked;
                    const campos = document.getElementById('campos_factura');
                    const rut = document.getElementById('rut_empresa');
                    const razon = document.getElementById('razon_social');

                    if (esFactura) {
                        campos.classList.remove('d-none');
                        rut.required = true;
                        razon.required = true;
                    } else {
                        campos.classList.add('d-none');
                        rut.required = false;
                        razon.required = false;
                    }
                }
            </script>

        @endif
    </div>
</div>
@endsection