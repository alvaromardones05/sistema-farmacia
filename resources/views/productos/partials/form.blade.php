@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa los datos ingresados.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre *</label>
                <input id="nombre" name="nombre" value="{{ old('nombre', $producto?->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required>
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label for="codigo_interno" class="form-label">Código interno *</label>
                <input id="codigo_interno" name="codigo_interno" value="{{ old('codigo_interno', $producto?->codigo_interno) }}" class="form-control @error('codigo_interno') is-invalid @enderror" {{ $producto ? 'readonly' : 'required' }}>
                @error('codigo_interno')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label for="codigo_barra" class="form-label">Código de barra</label>
                <input id="codigo_barra" name="codigo_barra" value="{{ old('codigo_barra', $producto?->codigo_barra) }}" class="form-control @error('codigo_barra') is-invalid @enderror" {{ $producto ? 'readonly' : '' }}>
                @error('codigo_barra')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="principio_activo" class="form-label">Principio activo</label>
                <input id="principio_activo" name="principio_activo" value="{{ old('principio_activo', $producto?->principio_activo) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="forma_farmaceutica" class="form-label">Forma farmacéutica</label>
                <input id="forma_farmaceutica" name="forma_farmaceutica" value="{{ old('forma_farmaceutica', $producto?->forma_farmaceutica) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="concentracion" class="form-label">Concentración</label>
                <input id="concentracion" name="concentracion" value="{{ old('concentracion', $producto?->concentracion) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="categoria_id" class="form-label">Categoría *</label>
                <select id="categoria_id" name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
                    <option value="">Selecciona una categoría</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto?->categoria_id) == $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="laboratorio_id" class="form-label">Laboratorio</label>
                <select id="laboratorio_id" name="laboratorio_id" class="form-select">
                    <option value="">Sin laboratorio</option>
                    @foreach ($laboratorios as $laboratorio)
                        <option value="{{ $laboratorio->id }}" @selected(old('laboratorio_id', $producto?->laboratorio_id) == $laboratorio->id)>{{ $laboratorio->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="unidad_medida" class="form-label">Unidad de medida *</label>
                <input id="unidad_medida" name="unidad_medida" value="{{ old('unidad_medida', $producto?->unidad_medida) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="tipo_receta" class="form-label">Tipo de receta *</label>
                <select id="tipo_receta" name="tipo_receta" class="form-select" required>
                    @foreach (['ninguna', 'simple', 'retenida', 'cheque'] as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo_receta', $producto?->tipo_receta ?? 'ninguna') === $tipo)>{{ ucfirst($tipo) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="stock_minimo" class="form-label">Stock mínimo *</label>
                <input id="stock_minimo" type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', $producto?->stock_minimo ?? 0) }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="stock_critico" class="form-label">Stock crítico *</label>
                <input id="stock_critico" type="number" min="0" name="stock_critico" value="{{ old('stock_critico', $producto?->stock_critico ?? 0) }}" class="form-control" required>
            </div>
            <div class="col-12">
                <label for="registro_isp" class="form-label">Registro ISP</label>
                <input id="registro_isp" name="registro_isp" value="{{ old('registro_isp', $producto?->registro_isp) }}" class="form-control">
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input id="es_controlado" type="checkbox" name="es_controlado" value="1" class="form-check-input" @checked(old('es_controlado', $producto?->es_controlado))>
                    <label for="es_controlado" class="form-check-label">Medicamento controlado</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white border-0 p-4 pt-0">
        <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i>{{ $producto ? 'Actualizar producto' : 'Guardar producto' }}</button>
        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </div>
</form>