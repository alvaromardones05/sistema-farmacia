@extends('layouts.app')
@section('title', 'Registrar receta')
@section('content')
<div class="d-flex justify-content-between mb-4"><h1 class="h3">Registrar receta</h1><a href="{{ route('recetas.index') }}" class="btn btn-outline-secondary">Volver</a></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<p class="text-muted">¿Falta el médico? <a href="{{ route('medicos.create') }}">Registrar médico</a> antes de completar este formulario.</p>
<form action="{{ route('recetas.store') }}" method="POST" enctype="multipart/form-data" id="form-receta">
    @csrf
    <div class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label for="paciente_id" class="form-label">Paciente *</label><select id="paciente_id" name="paciente_id" class="form-select" required>
            <option value="">Selecciona un paciente</option>@foreach($pacientes as $paciente)<option value="{{ $paciente->id }}" @selected(old('paciente_id', request()->integer('paciente_id')) == $paciente->id)>{{ $paciente->nombreCompleto() }} ({{ $paciente->rut }})</option>@endforeach
        </select></div>
        <div class="col-md-6"><label for="medico_id" class="form-label">Médico *</label><select id="medico_id" name="medico_id" class="form-select" required>
            <option value="">Selecciona un médico</option>@foreach($medicos as $medico)<option value="{{ $medico->id }}" @selected(old('medico_id', request()->integer('medico_id')) == $medico->id)>{{ $medico->nombreCompleto() }} ({{ $medico->rut }})</option>@endforeach
        </select></div>
        <div class="col-md-4"><label for="fecha_emision" class="form-label">Fecha de emisión *</label><input id="fecha_emision" type="date" name="fecha_emision" class="form-control" value="{{ old('fecha_emision') }}" max="{{ today()->toDateString() }}" required></div>
        <div class="col-md-4"><label for="fecha_vencimiento" class="form-label">Fecha de vencimiento *</label><input id="fecha_vencimiento" type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento') }}" min="{{ today()->toDateString() }}" required></div>
        <div class="col-md-4"><label for="tipo" class="form-label">Tipo *</label><select id="tipo" name="tipo" class="form-select" required>@foreach(['simple', 'retenida', 'cheque'] as $tipo)<option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
        <div class="col-12"><label for="archivo" class="form-label">Imagen o PDF de la receta *</label><input id="archivo" name="archivo" type="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required><div class="form-text">JPG, JPEG, PNG o PDF. Máximo 5 MB. Si hay errores debes volver a seleccionar el archivo.</div></div>
        <div class="col-12"><label for="observaciones" class="form-label">Observaciones (opcional)</label><textarea id="observaciones" name="observaciones" class="form-control" rows="3" maxlength="5000">{{ old('observaciones') }}</textarea></div>
    </div></div></div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header"><h2 class="h5 mb-0">Medicamentos indicados</h2></div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-5"><label for="medicamento" class="form-label">Producto</label><select id="medicamento" class="form-select"><option value="">Selecciona un producto</option>@foreach($productos as $producto)<option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}" data-unidad="{{ $producto->unidad_medida }}" @selected(request()->integer('producto_id') == $producto->id)>{{ $producto->nombre }} · {{ ucfirst($producto->tipo_receta) }}</option>@endforeach</select></div>
                <div class="col-md-2"><label for="cantidad" class="form-label">Cantidad</label><input id="cantidad" type="number" min="1" max="2147483647" value="1" class="form-control"></div>
                <div class="col-md-4"><label for="indicaciones" class="form-label">Indicaciones (opcional)</label><input id="indicaciones" maxlength="255" class="form-control" placeholder="Dosis, frecuencia y duración"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" id="agregar-detalle" class="btn btn-outline-primary" aria-label="Agregar medicamento">+</button></div>
            </div>
            <p class="form-text">Usa + para agregar varios medicamentos. Si dejas uno seleccionado, se agregará al registrar la receta.</p>
            <p id="mensaje-detalles" class="form-text" role="status" aria-live="polite"></p>
            <ul id="detalles-receta" class="list-group"></ul>
            <div id="campos-detalles"></div>
        </div>
    </div>
    <button class="btn btn-primary">Registrar para revisión</button> <a href="{{ route('recetas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</form>
@endsection
@section('scripts')
@php
    $detallesAnteriores = array_values(old('detalles', []));
    $tipoAnterior = old('tipo');
@endphp
<script>
    const productosReceta = @json($productos);
    let detalles = @json($detallesAnteriores);
    const tipoAnterior = @json($tipoAnterior);
    const productoInicial = productosReceta.find(p => String(p.id) === document.getElementById('medicamento').value);
    if (!tipoAnterior && productoInicial && productoInicial.tipo_receta !== 'ninguna') {
        document.getElementById('tipo').value = productoInicial.tipo_receta;
    }
    const lista = document.getElementById('detalles-receta');
    const campos = document.getElementById('campos-detalles');
    const mensaje = document.getElementById('mensaje-detalles');
    function renderDetalles() {
        lista.replaceChildren(); campos.replaceChildren();
        detalles.forEach((detalle, index) => {
            const producto = productosReceta.find(p => String(p.id) === String(detalle.producto_id));
            const fila = document.createElement('li'); fila.className = 'list-group-item d-flex justify-content-between align-items-center gap-2';
            const texto = document.createElement('span'); texto.textContent = `${producto?.nombre ?? 'Producto no disponible'} · ${detalle.cantidad_prescrita} ${producto?.unidad_medida ?? ''} · ${detalle.instrucciones ?? ''}`;
            const quitar = document.createElement('button'); quitar.type = 'button'; quitar.className = 'btn btn-sm btn-outline-danger'; quitar.textContent = 'Quitar';
            quitar.addEventListener('click', () => { detalles.splice(index, 1); renderDetalles(); });
            fila.append(texto, quitar); lista.appendChild(fila);
            ['producto_id', 'cantidad_prescrita', 'instrucciones'].forEach(nombre => {
                const input = document.createElement('input'); input.type = 'hidden'; input.name = `detalles[${index}][${nombre}]`; input.value = detalle[nombre] ?? ''; campos.appendChild(input);
            });
        });
        mensaje.textContent = detalles.length ? '' : 'Agrega al menos un medicamento.';
    }
    function agregarDetalle() {
        const select = document.getElementById('medicamento');
        const cantidad = Number(document.getElementById('cantidad').value);
        if (!select.value || !Number.isInteger(cantidad) || cantidad < 1 || cantidad > 2147483647) { mensaje.textContent = 'Selecciona un producto e indica una cantidad entera entre 1 y 2147483647.'; return false; }
        if (detalles.some(d => String(d.producto_id) === select.value)) { mensaje.textContent = 'Este producto ya está agregado. Quítalo para corregir su cantidad.'; return false; }
        detalles.push({producto_id: select.value, cantidad_prescrita: cantidad, instrucciones: document.getElementById('indicaciones').value});
        renderDetalles(); select.value = ''; document.getElementById('cantidad').value = 1; document.getElementById('indicaciones').value = '';
        return true;
    }
    document.getElementById('agregar-detalle').addEventListener('click', agregarDetalle);
    document.getElementById('form-receta').addEventListener('submit', event => {
        const select = document.getElementById('medicamento');
        if (select.value && !agregarDetalle()) {
            event.preventDefault();
            select.focus();
            return;
        }
        if (!detalles.length) {
            event.preventDefault();
            mensaje.textContent = 'Agrega al menos un medicamento antes de registrar la receta.';
            select.focus();
        }
    });
    renderDetalles();
</script>
@endsection
