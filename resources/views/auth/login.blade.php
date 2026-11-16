<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Sistema de Farmacia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-left: #ffffff;
            --bg-right: #f4f7fb;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --input-bg: #ffffff;
            --shadow-card: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --shadow-focus: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        [data-bs-theme="dark"] {
            --bg-left: #1a1b1e;
            --bg-right: #1c1d21;
            --primary: #2f81f7;
            --primary-hover: #4a94fa;
            --text-main: #ffffff;
            --text-muted: #8b949e;
            --border: #363740;
            --input-bg: #232428;
            --shadow-card: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
            --shadow-focus: 0 0 0 3px rgba(47, 129, 247, 0.25);
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--text-main);
            background-color: var(--bg-right);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Layout Split 50/50 */
        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* =====================================================
           LADO IZQUIERDO (BRANDING)
        ===================================================== */
        .panel-left {
            background-color: var(--bg-left);
            padding: 4rem 5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-right: 1px solid var(--border);
        }

        /* Círculo decorativo inferior izquierdo */
        .decoration-circle {
            position: absolute;
            bottom: -150px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(191, 219, 254, 0.4) 0%, rgba(191, 219, 254, 0) 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .content-left {
            position: relative;
            z-index: 1;
            max-width: 450px;
        }

        /* Logo SVG Calcado */
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .brand-logo svg {
            width: 60px;
            height: 60px;
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.1;
            margin: 0;
            letter-spacing: -0.03em;
        }

        .brand-title .text-dark-blue {
            color: #10213f;
        }

        [data-bs-theme="dark"] .brand-title .text-dark-blue {
            color: #ffffff;
        }

        .brand-title .text-light-blue {
            color: #2563eb;
        }

        .brand-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 3rem;
        }

        /* Lista de Features */
        .features-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background-color: #eff6ff;
            color: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        [data-bs-theme="dark"] .feature-icon {
            background-color: rgba(47, 129, 247, 0.1);
            color: #2f81f7;
        }

        .feature-text h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0 0 0.2rem 0;
            color: var(--text-main);
        }

        .feature-text p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .footer-tagline {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 4rem;
        }

        /* =====================================================
           LADO DERECHO (FORMULARIO)
        ===================================================== */
        .panel-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        /* Botón Modo Oscuro */
        .btn-theme {
            position: absolute;
            top: 2rem;
            right: 2rem;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            background-color: var(--bg-left);
            z-index: 10;
        }

        .btn-theme:hover {
            color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        /* Tarjeta */
        .login-card {
            width: 100%;
            max-width: 480px;
            background: var(--bg-left);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            padding: 3.5rem;
        }

        .login-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
            line-height: 1.5;
        }

        /* Inputs */
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .input-wrap {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-wrap .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .form-control {
            height: 54px; /* Antes 50px */
            padding-left: 3rem;
            padding-right: 3rem;
            font-size: 1rem;
            font-family: inherit;
            color: var(--text-main);
            background-color: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            background-color: var(--bg-left);
            border-color: var(--primary);
            box-shadow: var(--shadow-focus);
            color: var(--text-main);
        }

        .password-toggle {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* Extras (Recordarme / Olvidé) */
        .form-extras {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            margin-top: 1rem;
            font-size: 0.85rem;
        }

        .form-check-input {
            cursor: pointer;
            border-color: #cbd5e1;
            width: 16px;
            height: 16px;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            color: var(--text-muted);
            cursor: pointer;
            margin-left: 0.4rem;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            height: 54px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-login i {
            transition: transform 0.2s;
        }

        .btn-login:hover i {
            transform: translateX(4px);
        }

        /* Alertas */
        .validation-error {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.3rem;
        }

        .session-status {
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            font-size: 0.9rem;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-wrapper {
                grid-template-columns: 1.2fr 1fr;
                display: grid;
                min-height: 100vh;

            }
            .panel-left {
                display: none; /* Ocultar el panel izquierdo en móviles */
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        <!-- =====================================================
             LADO IZQUIERDO: BRANDING
        ====================================================== -->
        <div class="panel-left">
            <div class="decoration-circle"></div>

            <div class="content-left">
                <!-- Logo SVG Recreado -->
                <div class="brand-logo">
                    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M42 10H58V42H90V58H58V90H42V58H10V42H42V10Z" fill="url(#paint0_linear)"/>
                        <path d="M50 30C61.0457 30 70 38.9543 70 50C70 61.0457 61.0457 70 50 70C38.9543 70 30 61.0457 30 50C30 38.9543 38.9543 30 50 30Z" fill="white" fill-opacity="0.2"/>
                        <path d="M50 35C58.2843 35 65 41.7157 65 50C65 58.2843 58.2843 65 50 65C41.7157 65 35 58.2843 35 50C35 41.7157 41.7157 35 50 35Z" fill="white"/>
                        <path d="M50 35V65" stroke="#2563eb" stroke-width="6" stroke-linecap="round"/>
                        <defs>
                            <linearGradient id="paint0_linear" x1="10" y1="10" x2="90" y2="90" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2dd4bf"/>
                                <stop offset="1" stop-color="#2563eb"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <h1 class="brand-title">
                        <span class="text-dark-blue">Sistema de Gestión</span><br>
                        <span class="text-light-blue">de Farmacia</span>
                    </h1>
                </div>

                <p class="brand-subtitle">
                    Control de stock, recetas y ventas para una atención segura y eficiente.
                </p>

                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="feature-text">
                            <h4>Gestión de productos</h4>
                            <p>Control de stock y lotes.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-file-earmark-medical"></i></div>
                        <div class="feature-text">
                            <h4>Recetas y convenios</h4>
                            <p>Cumplimiento normativo chileno.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-bar-chart-line"></i></div>
                        <div class="feature-text">
                            <h4>Reportes y trazabilidad</h4>
                            <p>Información en tiempo real.</p>
                        </div>
                    </div>
                </div>

                <div class="footer-tagline">
                    Tu farmacia, más simple.
                </div>
            </div>
        </div>

        <!-- =====================================================
             LADO DERECHO: FORMULARIO
        ====================================================== -->
        <div class="panel-right">

            <button class="btn-theme" id="themeToggle" aria-label="Cambiar tema">
                <i class="bi bi-moon" id="themeIcon"></i>
            </button>

            <div class="login-card">
                <div class="login-header">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para acceder al sistema de gestión de farmacia.</p>
                </div>

                @if (session('status'))
                    <div class="session-status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Correo / Usuario --}}
                    <div>
                        <label for="email" class="form-label">Correo</label>
                        <div class="input-wrap">
                            <i class="bi bi-person input-icon"></i>
                            <input
                                id="email"
                                type="text" {{-- Permite RUT o correo --}}
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Correo"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-wrap">
                            <i class="bi bi-lock input-icon"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Contraseña"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye-slash" id="passwordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Extras --}}
                    <div class="form-extras">
                        <div class="form-check m-0 d-flex align-items-center">
                            <input class="form-check-input m-0" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Mantener sesión iniciada</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    {{-- Botón --}}
                    <button type="submit" class="btn-login">
                        Iniciar sesión <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        // Toggle Password
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        const icon = document.getElementById('passwordIcon');

        toggle.addEventListener('click', () => {
            const mostrando = password.type === 'text';
            password.type = mostrando ? 'password' : 'text';
            icon.classList.toggle('bi-eye', mostrando);
            icon.classList.toggle('bi-eye-slash', !mostrando);
        });

        // Toggle Tema
        const btnTheme = document.getElementById('themeToggle');
        const iconTheme = document.getElementById('themeIcon');
        const htmlEl = document.documentElement;

        // Script Anti-Flash inicial
        (function() {
            var storedTheme = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            htmlEl.setAttribute('data-bs-theme', storedTheme || (prefersDark ? 'dark' : 'light'));
        })();

        if (htmlEl.getAttribute('data-bs-theme') === 'dark') {
            iconTheme.className = 'bi bi-sun';
        }

        btnTheme.addEventListener('click', () => {
            const next = htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            htmlEl.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            iconTheme.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        });
    </script>
</body>
</html>
