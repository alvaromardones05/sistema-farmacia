@extends('layouts.app')

@section('title', 'Nuevo producto')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary small fw-semibold mb-1">Catálogo</p>
            <h1 class="h3 mb-0">Nuevo producto</h1>
        </div>
        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Volver</a>
    </div>

    @include('productos.partials.form', ['producto' => null, 'action' => route('productos.store'), 'method' => 'POST'])
</div>
@endsection