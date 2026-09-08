{{-- resources/views/productos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Productos')

@section('content')

<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-900">Productos</h1>
    <p class="text-gray-600 mt-2">Gestión del catálogo de medicinas y productos</p>
</div>

{{-- Tabla de Productos --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Código</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nombre</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Categoría</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Laboratorio</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Stock</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $producto)
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $producto->codigo }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $producto->nombre }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $producto->laboratorio->nombre ?? 'N/A' }}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                        @if($producto->stock_actual > $producto->stock_minimo)
                            bg-green-100 text-green-800
                        @elseif($producto->stock_actual > 0)
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-red-100 text-red-800
                        @endif
                    ">
                        {{ $producto->stock_actual ?? 0 }} unidades
                    </span>
                </td>
                <td class="px-6 py-4 text-sm space-x-2">
                    <a href="{{ route('productos.show', $producto) }}" class="text-blue-600 hover:underline">Ver</a>
                    <a href="{{ route('productos.edit', $producto) }}" class="text-indigo-600 hover:underline">Editar</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    No hay productos registrados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="mt-6">
    {{ $productos->links() }}
</div>

@endsection