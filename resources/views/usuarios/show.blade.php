@extends('layouts.app')

@section('content')
<div class="container">
    <div class="form-header">
        <h1>{{ $usuario->nombre }}</h1>
        <a href="{{ route('usuarios.index') }}" class="btn-secondary">← Volver</a>
    </div>

    <div class="form-section">
        <table class="table">
            <tr>
                <th>Email</th>
                <td>{{ $usuario->email }}</td>
            </tr>
            <tr>
                <th>RUT</th>
                <td>{{ $usuario->rut }}</td>
            </tr>
            <tr>
                <th>Rol</th>
                <td>
                    <span class="badge badge-success">{{ ucfirst($usuario->rol) }}</span>
                </td>
            </tr>
            <tr>
                <th>Número de Colegiatura</th>
                <td>{{ $usuario->numero_colegiatura ?? '-' }}</td>
            </tr>
            <tr>
                <th>Teléfono</th>
                <td>{{ $usuario->telefono ?? '-' }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <span class="badge {{ $usuario->activo ? 'badge-success' : 'badge-danger' }}">
                        {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Creado</th>
                <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <div class="form-actions">
            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn-warning">Editar</a>
            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="form-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" onclick="return confirm('¿Está seguro?')">Eliminar</button>
            </form>
        </div>
    </div>
</div>
@endsection