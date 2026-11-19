{{-- Campos comunes: distribuidor y número de documento.
     Variables: $proveedores, opcional $etiquetaDocumento --}}
<div class="col-md-4">
    <label class="form-label">Distribuidor</label>
    <select name="proveedor_id" class="form-select">
        <option value="">Sin especificar</option>
        @foreach($proveedores as $proveedor)
            <option value="{{ $proveedor->id }}" @selected(old('proveedor_id') == $proveedor->id)>
                {{ $proveedor->nombre_razon_social }}
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label">{{ $etiquetaDocumento ?? 'N° de documento' }}</label>
    <input type="text" name="numero_documento" value="{{ old('numero_documento') }}"
           maxlength="100" required class="form-control">
</div>
