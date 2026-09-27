@extends('layouts.app')

@section('title', 'Caja')

@section('content')
<div class="container-fluid px-0">

    <div class="mb-4">
        <h1 class="h3 mb-0">Caja</h1>
        <p class="text-muted mb-0">Apertura y cierre de caja</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($apertura)
        {{-- HAY CAJA ABIERTA: mostrar estado + botón cerrar --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <span class="badge text-bg-success">Abierta</span>
                    {{ $apertura->caja?->descripcion }} ({{ $apertura->caja?->numero_caja }})
                </h5>
                <p class="mb-1"><strong>Abierta por:</strong> {{ $apertura->usuario?->name }}</p>
                <p class="mb-1"><strong>Fecha apertura:</strong> {{ $apertura->fecha_apertura->format('d/m/Y H:i') }}</p>
                <p class="mb-3"><strong>Saldo inicial:</strong> ${{ number_format($apertura->saldo_inicial, 0, ',', '.') }}</p>

                <form action="{{ route('caja.cerrar', $apertura) }}" method="POST"
                    onsubmit="return confirm('¿Confirmas cerrar la caja?')">
                    @csrf
                    <button class="btn btn-danger">Cerrar caja</button>
                </form>
            </div>
        </div>
    @else
        {{-- NO HAY CAJA ABIERTA: mostrar formulario para abrir --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <span class="badge text-bg-secondary">Sin caja abierta</span>
                </h5>
                <p class="text-muted">Para registrar ventas, primero debes abrir una caja.</p>

                <form action="{{ route('caja.abrir') }}" method="POST" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Caja *</label>
                        <select name="caja_id" class="form-select" required>
                            <option value="">Selecciona una caja</option>
                            @foreach ($cajas as $caja)
                                <option value="{{ $caja->id }}">{{ $caja->descripcion }} ({{ $caja->numero_caja }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Saldo inicial *</label>
                        <input type="number" name="saldo_inicial" class="form-control" min="0" step="1" required>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Abrir caja</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection