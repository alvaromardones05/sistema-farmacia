@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Catálogo</p>
            <h1 class="h3 mb-0">Editar producto</h1>
        </div>
        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>

    @include('productos.partials.form', ['producto' => $producto, 'action' => route('productos.update', $producto), 'method' => 'PUT'])
</div>
@endsection