{{-- resources/views/components/form-input.blade.php --}}

@props(['label', 'name', 'type' => 'text', 'value' => null, 'placeholder' => null, 'required' => false, 'rows' => null])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">
            {{ $label }}
            @if ($required)
                <span class="required">*</span>
            @endif
        </label>
    @endif

    @if ($type === 'textarea')
        <textarea 
            id="{{ $name }}"
            name="{{ $name }}"
            class="form-control @error($name) is-invalid @enderror"
            rows="{{ $rows ?? 4 }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
        >{{ old($name, $value) }}</textarea>
    @elseif ($type === 'checkbox')
        <label class="checkbox-label">
            <input 
                type="checkbox"
                name="{{ $name }}"
                id="{{ $name }}"
                value="1"
                {{ old($name, $value) ? 'checked' : '' }}
            >
            {{ $label }}
        </label>
    @else
        <input 
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            class="form-control @error($name) is-invalid @enderror"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
    @endif

    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

{{-- 
    CÓMO USAR EN VISTAS:

    1. Input de texto:
       <x-form-input 
           label="Nombre" 
           name="nombre" 
           placeholder="Ej: Ibuprofeno"
           required
           :value="$producto->nombre ?? ''"
       />

    2. Input de número:
       <x-form-input 
           type="number" 
           label="Precio" 
           name="precio_base" 
           step="0.01"
           required
           :value="$producto->precio_base ?? ''"
       />

    3. Textarea:
       <x-form-input 
           type="textarea" 
           label="Descripción" 
           name="descripcion" 
           rows="5"
           :value="$producto->descripcion ?? ''"
       />

    4. Checkbox:
       <x-form-input 
           type="checkbox" 
           label="¿Requiere Receta?" 
           name="requiere_receta"
           :value="$producto->requiere_receta ?? false"
       />

    Nota: Automáticamente:
    - Muestra errores de validación
    - Recupera old() values después de validación fallida
    - Soporta atributos HTML adicionales con {{ $attributes }}
--}}