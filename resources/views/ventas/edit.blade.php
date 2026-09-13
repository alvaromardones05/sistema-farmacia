@extends('layouts.app')

@section('content')
<div class="container">
    <div class="form-header">
        <h1>Editar Venta #{{ $venta->id }}</h1>
        <a href="{{ route('ventas.index') }}" class="btn-secondary">← Volver</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error al actualizar la venta:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ventas.update', $venta->id) }}" method="POST" class="form-section">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="fecha_venta">Fecha de Venta *</label>
            <input type="datetime-local" id="fecha_venta" name="fecha_venta" 
                   class="form-control @error('fecha_venta') is-invalid @enderror"
                   value="{{ old('fecha_venta', $venta->fecha_venta->format('Y-m-d\TH:i')) }}" required>
            @error('fecha_venta')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="cliente_nombre">Nombre Cliente *</label>
            <input type="text" id="cliente_nombre" name="cliente_nombre"
                   class="form-control @error('cliente_nombre') is-invalid @enderror"
                   value="{{ old('cliente_nombre', $venta->cliente_nombre) }}" required>
            @error('cliente_nombre')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="cliente_rut">RUT Cliente</label>
            <input type="text" id="cliente_rut" name="cliente_rut"
                   class="form-control @error('cliente_rut') is-invalid @enderror"
                   value="{{ old('cliente_rut', $venta->cliente_rut) }}" placeholder="Ej: 12.345.678-9">
            @error('cliente_rut')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="tipo_venta">Tipo de Venta *</label>
            <select id="tipo_venta" name="tipo_venta" class="form-control @error('tipo_venta') is-invalid @enderror" required>
                <option value="">-- Seleccionar --</option>
                <option value="efectivo" {{ old('tipo_venta', $venta->tipo_venta) === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                <option value="tarjeta" {{ old('tipo_venta', $venta->tipo_venta) === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                <option value="cheque" {{ old('tipo_venta', $venta->tipo_venta) === 'cheque' ? 'selected' : '' }}>Cheque</option>
                <option value="transferencia" {{ old('tipo_venta', $venta->tipo_venta) === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
            </select>
            @error('tipo_venta')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="total">Total (CLP) *</label>
            <input type="number" id="total" name="total"
                   class="form-control @error('total') is-invalid @enderror"
                   value="{{ old('total', $venta->total) }}" step="0.01" min="0" required>
            @error('total')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="descuento">Descuento (CLP)</label>
            <input type="number" id="descuento" name="descuento"
                   class="form-control @error('descuento') is-invalid @enderror"
                   value="{{ old('descuento', $venta->descuento) }}" step="0.01" min="0">
            @error('descuento')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="notas">Notas</label>
            <textarea id="notas" name="notas" class="form-control @error('notas') is-invalid @enderror" rows="3">{{ old('notas', $venta->notas) }}</textarea>
            @error('notas')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="estado">
                <input type="checkbox" id="estado" name="estado" value="completada" {{ old('estado', $venta->estado === 'completada') ? 'checked' : '' }}>
                Marcar como Completada
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Actualizar Venta</button>
            <a href="{{ route('ventas.index') }}" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection