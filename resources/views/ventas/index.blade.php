{{-- resources/views/ventas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Ventas')

@section('content')

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-4xl font-bold text-gray-900">Ventas</h1>
        <p class="text-gray-600 mt-2">Historial de transacciones</p>
    </div>
    <a href="{{ route('ventas.create') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 font-medium">
        + Nueva Venta
    </a>
</div>

{{-- Filtros --}}
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form method="GET" action="{{ route('ventas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
            <input type="date" name="desde" value="{{ request('desde') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">RUT Cliente</label>
            <input type="text" name="rut" placeholder="12.345.678-9" value="{{ request('rut') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                Buscar
            </button>
        </div>
    </form>
</div>

{{-- Tabla de Ventas --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Boleta</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Cliente (RUT)</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Total</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Fecha</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Usuario</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $venta->numero_boleta ?? 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    <span class="font-medium">{{ $venta->paciente->nombre ?? 'Anónimo' }}</span>
                    <br>
                    <span class="text-xs text-gray-500">{{ $venta->paciente->rut ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                    ${{ number_format($venta->total, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $venta->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    {{ $venta->usuario->name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 text-sm space-x-2">
                    <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:underline">Ver</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    No hay ventas registradas
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div class="mt-6">
    {{ $ventas->links() }}
</div>

@endsection