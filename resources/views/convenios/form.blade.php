@extends('layouts.app')

@section('title', $convenio ? 'Gestionar convenio' : 'Nuevo convenio')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">
                {{ $convenio ? $convenio->nombre : 'Nuevo convenio' }}
            </h1>

            <p class="text-muted mb-0">
                Configuración general y coberturas
            </p>
        </div>

        <a href="{{ route('convenios.index') }}"
           class="btn btn-outline-secondary">
            Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- DATOS GENERALES --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h5 class="mb-0">Datos del convenio</h5>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ $convenio
                        ? route('convenios.update', $convenio)
                        : route('convenios.store') }}">

                @csrf

                @if($convenio)
                    @method('PUT')
                @endif

                <div class="row g-3">

                    <div class="col-md-5">
                        <label class="form-label">Nombre *</label>

                        <input type="text"
                               name="nombre"
                               class="form-control"
                               value="{{ old('nombre', $convenio?->nombre) }}"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Código *</label>

                        <input type="text"
                               name="codigo_convenio"
                               class="form-control"
                               value="{{ old('codigo_convenio', $convenio?->codigo_convenio) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo *</label>

                        <select name="tipo"
                                class="form-select"
                                required>

                            @foreach([
                                'isapre' => 'Isapre',
                                'fonasa' => 'Fonasa',
                                'ges' => 'GES',
                                'particular' => 'Particular',
                                'otro' => 'Otro',
                            ] as $valor => $nombre)

                                <option value="{{ $valor }}"
                                    @selected(old('tipo', $convenio?->tipo ?? 'particular') === $valor)>
                                    {{ $nombre }}
                                </option>

                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Descuento general
                        </label>

                        <div class="input-group">
                            <input type="number"
                                   name="descuento_pct"
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   value="{{ old('descuento_pct', $convenio?->descuento_pct ?? 0) }}"
                                   required>

                            <span class="input-group-text">%</span>
                        </div>

                        <small class="text-muted">
                            Se usa cuando el producto no tiene cobertura específica.
                        </small>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">
                            Observaciones
                        </label>

                        <input type="text"
                               name="observaciones"
                               class="form-control"
                               value="{{ old('observaciones', $convenio?->observaciones) }}">
                    </div>

                    <div class="col-12">

                        <div class="form-check">
                            <input type="checkbox"
                                   name="activo"
                                   value="1"
                                   class="form-check-input"
                                   id="activo"
                                   @checked(old('activo', $convenio?->activo ?? true))>

                            <label class="form-check-label"
                                   for="activo">
                                Convenio activo
                            </label>
                        </div>

                    </div>

                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary">
                        {{ $convenio ? 'Guardar cambios' : 'Crear convenio' }}
                    </button>
                </div>

            </form>

        </div>
    </div>

    @if($convenio)

        {{-- NUEVA COBERTURA --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-0 pt-3">
                <h5 class="mb-1">Agregar cobertura</h5>

                <small class="text-muted">
                    Configura condiciones especiales para un producto.
                </small>
            </div>

            <div class="card-body">

                <form method="POST"
                      action="{{ route('convenios.coberturas.store', $convenio) }}">

                    @csrf

                    <div class="row g-3 align-items-end">

                        <div class="col-md-5">
                            <label class="form-label">Producto *</label>

                            <select name="producto_id"
                                    class="form-select"
                                    required>

                                <option value="">Seleccionar producto</option>

                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}">
                                        {{ $producto->nombre }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Copago %
                            </label>

                            <div class="input-group">
                                <input type="number"
                                       name="copago_pct"
                                       class="form-control"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       value="100"
                                       required>

                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Copago fijo
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">$</span>

                                <input type="number"
                                       name="copago_fijo"
                                       class="form-control"
                                       min="0"
                                       step="1">
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Observaciones
                            </label>

                            <input type="text"
                                   name="observaciones"
                                   class="form-control"
                                   placeholder="Opcional">
                        </div>

                    </div>

                </form>

                <div class="alert alert-light border mt-3 mb-0 small">
                    <strong>Copago %:</strong>
                    indica cuánto paga el paciente.
                    0% = cobertura completa y 100% = paga el valor completo.
                    Si ingresas un copago fijo, este tendrá prioridad.
                </div>

            </div>

        </div>

        {{-- COBERTURAS EXISTENTES --}}
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-0 pt-3">
                <h5 class="mb-0">
                    Coberturas configuradas
                </h5>
            </div>

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Copago</th>
                            <th>Observación</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($convenio->coberturas as $cobertura)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $cobertura->producto?->nombre }}
                                    </strong>
                                </td>

                                <td class="text-center">

                                    @if($cobertura->copago_fijo !== null)
                                        ${{ number_format($cobertura->copago_fijo, 0, ',', '.') }}
                                    @elseif((float) $cobertura->copago_pct === 0.0)
                                        <span class="badge text-bg-success">
                                            100% cubierto
                                        </span>
                                    @else
                                        {{ number_format($cobertura->copago_pct, 0) }}%
                                    @endif

                                </td>

                                <td class="text-muted">
                                    {{ $cobertura->observaciones ?: '—' }}
                                </td>

                                <td class="text-end">

                                    <form method="POST"
                                          action="{{ route(
                                              'convenios.coberturas.destroy',
                                              [$convenio, $cobertura]
                                          ) }}"
                                          onsubmit="return confirm('¿Eliminar esta cobertura?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-outline-danger">
                                            Eliminar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4"
                                    class="text-center text-muted py-4">
                                    Este convenio todavía no tiene coberturas específicas.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    @endif

</div>
@endsection