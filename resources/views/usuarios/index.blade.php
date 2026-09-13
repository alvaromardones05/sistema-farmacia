@extends('layouts.app')

@section('content')
<div class="container">
    <div class="header-section">
        <h1>Usuarios</h1>
        <a href="{{ route('usuarios.create') }}" class="btn-primary">
            + Nuevo Usuario
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-section">
        @if ($usuarios->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>RUT</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>{{ $usuario->name }} {{ $usuario->apellidos }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->rut }}</td>
                            <td>{{ $usuario->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</td>
                            <td>
                                <span class="badge {{ $usuario->activo ? 'badge-success' : 'badge-danger' }}">
                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="form-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger" onclick="return confirm('¿Está seguro?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination-section">
                {{ $usuarios->links() }}
            </div>
        @else
            <div class="empty-state">
                <p>No hay usuarios registrados.</p>
                <a href="{{ route('usuarios.create') }}" class="btn-primary">Crear primer usuario</a>
            </div>
        @endif
    </div>
</div>
@endsection