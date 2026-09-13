{{-- resources/views/components/alert.blade.php --}}

@props(['type' => 'info', 'message' => null, 'title' => null])

@if ($message || $slot->isNotEmpty())
    <div class="alert alert-{{ $type }}">
        @if ($title)
            <strong>{{ $title }}</strong>
        @endif
        
        @if ($message)
            <p>{{ $message }}</p>
        @else
            {{ $slot }}
        @endif
    </div>
@endif

{{-- 
    CÓMO USAR EN VISTAS:

    1. Con mensaje directo:
       <x-alert type="success" message="¡Producto creado!" />
       <x-alert type="danger" message="Hubo un error" title="Error" />

    2. Con slot (contenido entre etiquetas):
       <x-alert type="warning" title="Advertencia">
           Este cambio no se puede deshacer.
       </x-alert>

    3. Para mostrar errores de validación (en formularios):
       @if ($errors->any())
           <x-alert type="danger" title="Errores de validación">
               <ul>
                   @foreach ($errors->all() as $error)
                       <li>{{ $error }}</li>
                   @endforeach
               </ul>
           </x-alert>
       @endif
--}}