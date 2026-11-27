@extends('layouts.app')

@section('content')
<style>
    /* ================= ESTILOS DEL MÓDULO USUARIOS ================= */
    .usuarios-modulo {
        padding: 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
        font-family: 'Inter', system-ui, sans-serif;
    }

    /* Header */
    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .header-section h1 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin: 0;
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .btn-primary:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
        color: white;
    }

    /* Alertas */
    .alert {
        border-radius: 10px;
        border: none;
        padding: 1rem 1.25rem;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .alert-success { background: #ecfdf5; color: #059669; border-left: 4px solid #059669; }
    .alert-danger { background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626; }

    /* Tarjeta de la Tabla */
    .table-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #334155;
    }

    .table tbody tr {
        transition: background-color 0.15s;
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Columnas específicas */
    .table td:nth-child(1) { font-weight: 600; color: #94a3b8; }
    .table td:nth-child(2) { font-weight: 600; color: #0f172a; }
    .table td:nth-child(3) { color: #64748b; }

    /* Badges de Estado */
    .badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .badge-success { background: #d1fae5; color: #047857; }
    .badge-danger { background: #fee2e2; color: #b91c1c; }

    /* Acciones (Botones) */
    .actions-cell {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .actions-cell form {
        margin: 0;
        display: flex;
    }

    .btn-sm {
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
    }

    .btn-outline-primary {
        background: transparent;
        border-color: #cbd5e1;
        color: #475569;
    }
    .btn-outline-primary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .btn-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .btn-danger:hover {
        background: #dc2626;
        border-color: #dc2626;
        color: white;
    }

    /* Paginación */
    .pagination-section {
        padding: 1rem 1.25rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }
    .pagination-section nav {
        margin: 0;
    }
    .pagination-section .pagination {
        margin: 0;
    }
    .pagination-section .page-link {
        border: 1px solid #e2e8f0;
        color: #64748b;
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
        border-radius: 6px;
        margin: 0 2px;
    }
    .pagination-section .page-item.active .page-link {
        background: #4f46e5;
        border-color: #4f46e5;
        color: white;
    }

    /* Estado Vacío */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: #ffffff;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
    }
    .empty-state p {
        color: #64748b;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
    }
</style>

<div class="usuarios-modulo">
    <!-- Header -->
    <div class="header-section">
        <h1>Usuarios</h1>
        <a href="{{ route('usuarios.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Mensajes de Sesión / Errores -->
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i> {{ $message }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Contenido Principal -->
    <div class="table-section">
        @if ($usuarios->count() > 0)
            <div class="table-card">
                <div class="table-responsive">
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
                                    <td>#{{ $usuario->id }}</td>
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
                                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">
                                            Editar
                                        </a>
                                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="form-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Paginación integrada en la tarjeta -->
                <div class="pagination-section">
                    {{ $usuarios->links() }}
                </div>
            </div>
        @else
            <!-- Estado Vacío -->
            <div class="empty-state">
                <i class="bi bi-people text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                <p class="mt-3">No hay usuarios registrados actualmente.</p>
                <a href="{{ route('usuarios.create') }}" class="btn-primary">
                    Crear primer usuario
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
