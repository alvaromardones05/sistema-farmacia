{{-- resources/views/usuarios/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-4xl font-bold text-gray-900">Usuarios</h1>
        <p class="text-gray-600 mt-2">Gestión de acceso y permisos</p>
    </div>
    <a href="{{ route('usuarios.create') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 font-medium">
        + Nuevo Usuario
    </a>
</div>

{{-- Tabla de Usuarios --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nombre</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Rol(es)</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Estado</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Registrado</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $usuario->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $usuario->email }}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="space-y-1">
                        @forelse($usuario->roles as $role)
                        <span class="inline-block px-2 py-1 rounded text-xs font-medium
                            @if($role->name === 'administrador')
                                bg-red-100 text-red-800
                            @elseif($role->name === 'tecnico')
                                bg-blue-100 text-blue-800
                            @elseif($role->name === 'quimico')
                                bg-purple-100 text-purple-800
                            @else
                                bg-gray-100 text-gray-800
                            @endif
                        ">
                            {{ ucfirst($role->name) }}
                        </span>
                        @empty
                        <span class="text-gray-400 text-xs">Sin roles</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-6 py-4 text-sm">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Activo
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $usuario->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 text-sm space-x-2">
                    <a href="{{ route('usuarios.edit', $usuario) }}" class="text-indigo-600 hover:underline">Editar</a>
                    <button type="button" @click="confirmarEliminar({{ $usuario->id }})" class="text-red-600 hover:underline">Eliminar</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    No hay usuarios registrados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="mt-6">
    {{ $usuarios->links() }}
</div>

@endsection