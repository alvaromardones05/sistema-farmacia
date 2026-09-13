<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farmacia</title>
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Estilos personalizados --}}
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .login-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    
    {{-- Header del login --}}
    <div class="login-header">
        <h1>💊 Farmacia</h1>
        <p>Sistema de Gestión</p>
    </div>

    {{-- Mostrar errores si los hay --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Errores:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Formulario de login --}}
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                id="email" 
                name="email" 
                value="{{ old('email') }}"
                placeholder="tu@email.com" 
                required
                autofocus
            >
            @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input 
                type="password" 
                class="form-control @error('password') is-invalid @enderror" 
                id="password" 
                name="password" 
                placeholder="Tu contraseña" 
                required
            >
            @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="mb-3 form-check">
            <input 
                type="checkbox" 
                class="form-check-input" 
                id="remember" 
                name="remember"
            >
            <label class="form-check-label" for="remember">
                Recuérdame
            </label>
        </div>

        {{-- Botón login --}}
        <button type="submit" class="btn btn-login btn-primary w-100">
            Iniciar Sesión
        </button>

        {{-- Enlaces footer --}}
        <div class="login-footer">
            <p>¿No tienes cuenta? <a href="{{ route('register') }}">Registrate aquí</a></p>
            <p><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
        </div>
    </form>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>