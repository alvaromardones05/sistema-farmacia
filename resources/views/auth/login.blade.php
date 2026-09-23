<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farmacia</title>
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Estilos personalizados --}}
    <style>
        /* =====================================================
   VARIABLES
====================================================== */
:root {
    --mint-900: #064e3b;
    --mint-700: #047857;
    --mint-600: #059669;
    --mint-500: #10b981;
    --mint-400: #34d399;
    --mint-300: #6ee7b7;
    --mint-100: #d1fae5;
    --mint-50: #ecfdf5;

    --azul-900: #0c4a6e;
    --azul-700: #0369a1;
    --azul-600: #0284c7;
    --azul-500: #0ea5e9;
    --azul-100: #e0f2fe;

    --gris-900: #0f172a;
    --gris-700: #334155;
    --gris-600: #475569;
    --gris-500: #64748b;
    --gris-400: #94a3b8;
    --gris-300: #cbd5e1;
    --gris-200: #e2e8f0;
    --gris-100: #f1f5f9;
    --gris-50: #f8fafc;

    --blanco: #ffffff;
    --transicion: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}

/* =====================================================
   BASE
====================================================== */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    margin: 0;
    color: var(--gris-700);
    position: relative;
    overflow-x: hidden;

    /* Fondo limpio con gradiente suave mint/azul */
    background:
        radial-gradient(ellipse at top left, rgba(16, 185, 129, 0.15), transparent 50%),
        radial-gradient(ellipse at bottom right, rgba(14, 165, 233, 0.15), transparent 50%),
        linear-gradient(180deg, #f0fdfa 0%, #f0f9ff 100%);
    background-attachment: fixed;
}

/* Retícula suave de fondo (estilo clínico) */
body::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(15, 23, 42, 0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
    mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
    -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
}

/* Círculo decorativo mint flotante arriba */
body::after {
    content: "";
    position: fixed;
    top: -180px;
    right: -180px;
    width: 450px;
    height: 450px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.35), transparent 70%);
    filter: blur(60px);
    pointer-events: none;
    z-index: 0;
    animation: pulseMint 8s ease-in-out infinite;
}

@keyframes pulseMint {
    0%, 100% { transform: scale(1); opacity: 0.9; }
    50%      { transform: scale(1.15); opacity: 0.7; }
}

/* =====================================================
   TARJETA DE LOGIN
====================================================== */
.login-container {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 420px;
    padding: 50px 42px 42px;

    background: var(--blanco);
    border-radius: 24px;
    border: 1px solid rgba(226, 232, 240, 0.8);

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.04),
        0 12px 32px rgba(15, 23, 42, 0.08),
        0 32px 64px rgba(15, 23, 42, 0.06);

    animation: cardRise 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes cardRise {
    from {
        opacity: 0;
        transform: translateY(24px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Icono circular flotante arriba del header (estilo pastilla) */
.login-container::before {
    content: "💊";
    position: absolute;
    top: -34px;
    left: 50%;
    transform: translateX(-50%);

    width: 68px;
    height: 68px;
    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;

    background: linear-gradient(135deg, var(--mint-500) 0%, var(--azul-500) 100%);
    box-shadow:
        0 8px 24px rgba(16, 185, 129, 0.35),
        0 0 0 6px var(--blanco),
        0 0 0 8px rgba(16, 185, 129, 0.12);
    animation: pillFloat 3.5s ease-in-out infinite;
}

@keyframes pillFloat {
    0%, 100% { transform: translateX(-50%) translateY(0) rotate(-8deg); }
    50%      { transform: translateX(-50%) translateY(-6px) rotate(8deg); }
}

/* =====================================================
   HEADER
====================================================== */
.login-header {
    text-align: center;
    margin-bottom: 34px;
    padding-top: 6px;
}

.login-header h1 {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.6px;
    margin-bottom: 8px;
    color: var(--gris-900);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.login-header h1::after {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--mint-500);
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    animation: dotPulse 2s ease-in-out infinite;
}

@keyframes dotPulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }
    50%      { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.08); }
}

.login-header p {
    color: var(--gris-500);
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin: 0;
}

/* =====================================================
   ALERTAS
====================================================== */
.alert-danger {
    background: #fef2f2;
    border: none;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    border-radius: 10px;
    font-size: 0.85rem;
    padding: 12px 14px;
    margin-bottom: 20px;
}

.alert-danger strong {
    color: #7f1d1d;
    font-size: 0.87rem;
}

.alert-danger ul {
    padding-left: 1.1rem;
    margin-bottom: 0;
    font-size: 0.83rem;
}

/* =====================================================
   FORMULARIO
====================================================== */
.form-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--gris-700);
    font-weight: 600;
    font-size: 0.82rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.form-control {
    background: var(--gris-50);
    border: 1.5px solid transparent;
    color: var(--gris-900);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 0.92rem;
    font-weight: 500;
    transition: var(--transicion);
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
}

.form-control::placeholder {
    color: var(--gris-400);
    font-weight: 400;
}

.form-control:hover {
    background: var(--gris-100);
}

.form-control:focus {
    background: var(--blanco);
    border-color: var(--mint-500);
    color: var(--gris-900);
    box-shadow:
        0 0 0 4px rgba(16, 185, 129, 0.12),
        inset 0 1px 2px rgba(15, 23, 42, 0.03);
    outline: none;
}

.form-control.is-invalid {
    background: #fef2f2;
    border-color: #ef4444;
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
}

.invalid-feedback {
    color: #dc2626;
    font-size: 0.78rem;
    font-weight: 500;
    margin-top: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.invalid-feedback::before {
    content: "⚠";
    font-size: 0.85rem;
}

/* =====================================================
   CHECKBOX "Recuérdame" — estilo switch suave
====================================================== */
.form-check {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding-left: 0;
    margin-bottom: 1.3rem;
}

.form-check-input {
    width: 1.1rem;
    height: 1.1rem;
    margin: 0;
    border: 1.5px solid var(--gris-300);
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transicion);
    background-color: var(--blanco);
}

.form-check-input:hover {
    border-color: var(--mint-500);
    transform: scale(1.05);
}

.form-check-input:checked {
    background-color: var(--mint-500);
    border-color: var(--mint-500);
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35);
}

.form-check-input:focus {
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    border-color: var(--mint-500);
}

.form-check-label {
    color: var(--gris-600);
    font-size: 0.86rem;
    font-weight: 500;
    cursor: pointer;
    user-select: none;
    transition: color 0.2s ease;
}

.form-check-label:hover {
    color: var(--gris-900);
}

/* =====================================================
   BOTÓN LOGIN
====================================================== */
.btn-login {
    position: relative;
    background: linear-gradient(135deg, var(--mint-600) 0%, var(--azul-600) 100%);
    color: var(--blanco);
    border: none;
    padding: 0.9rem 1rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.92rem;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-top: 6px;
    overflow: hidden;
    transition: var(--transicion);
    box-shadow:
        0 6px 18px rgba(16, 185, 129, 0.35),
        0 2px 4px rgba(15, 23, 42, 0.08);
}

/* Efecto de resplandor interior al hover */
.btn-login::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--mint-500) 0%, var(--azul-500) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
}

/* Barrido de luz */
.btn-login::after {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.35),
        transparent
    );
    transform: skewX(-20deg);
    transition: left 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-login:hover {
    color: var(--blanco);
    transform: translateY(-2px);
    box-shadow:
        0 12px 28px rgba(16, 185, 129, 0.45),
        0 4px 8px rgba(15, 23, 42, 0.1);
}

.btn-login:hover::before {
    opacity: 1;
}

.btn-login:hover::after {
    left: 120%;
}

.btn-login:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

.btn-login:focus-visible {
    outline: none;
    box-shadow:
        0 0 0 4px rgba(16, 185, 129, 0.3),
        0 6px 18px rgba(16, 185, 129, 0.35);
}

/* =====================================================
   FOOTER DEL LOGIN
====================================================== */
.login-footer {
    text-align: center;
    margin-top: 26px;
    padding-top: 22px;
    font-size: 13.5px;
    border-top: 1px solid var(--gris-100);
    position: relative;
}

/* Pequeño punto mint sobre la línea divisoria */
.login-footer::before {
    content: "";
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--mint-500);
    box-shadow: 0 0 0 4px var(--blanco);
}

.login-footer p {
    margin-bottom: 8px;
    color: var(--gris-500);
    font-weight: 500;
}

.login-footer p:last-child {
    margin-bottom: 0;
}

.login-footer a {
    color: var(--mint-700);
    text-decoration: none;
    font-weight: 700;
    transition: var(--transicion);
    position: relative;
    padding-bottom: 1px;
}

.login-footer a::after {
    content: "";
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--mint-500), var(--azul-500));
    border-radius: 2px;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.login-footer a:hover {
    color: var(--azul-700);
}

.login-footer a:hover::after {
    transform: scaleX(1);
}

/* =====================================================
   RESPONSIVE
====================================================== */
@media (max-width: 480px) {
    .login-container {
        padding: 44px 26px 32px;
        border-radius: 20px;
    }

    .login-container::before {
        width: 58px;
        height: 58px;
        font-size: 26px;
        top: -28px;
    }

    .login-header h1 {
        font-size: 22px;
    }

    .login-header p {
        font-size: 12px;
    }
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