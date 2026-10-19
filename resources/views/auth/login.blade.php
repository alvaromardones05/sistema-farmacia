<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Iniciar sesión | Sistema de Farmacia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #10213f;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 15% 15%, rgba(191, 219, 254, .65), transparent 32%),
                radial-gradient(circle at 82% 75%, rgba(219, 234, 254, .8), transparent 35%),
                linear-gradient(135deg, #f8fbff 0%, #eef6ff 48%, #f8fbff 100%);
        }

        /* Fondo difuminado estilo farmacia */
        .login-page::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .6;
            background:
                linear-gradient(90deg,
                    transparent 0 8%,
                    rgba(140, 190, 230, .14) 8% 9%,
                    transparent 9% 23%,
                    rgba(140, 190, 230, .12) 23% 24%,
                    transparent 24% 78%,
                    rgba(140, 190, 230, .12) 78% 79%,
                    transparent 79% 100%
                ),
                repeating-linear-gradient(
                    0deg,
                    transparent 0 95px,
                    rgba(108, 164, 211, .08) 95px 101px
                );
            filter: blur(2px);
        }

        .login-page::after {
            content: "";
            position: absolute;
            width: 850px;
            height: 850px;
            border-radius: 50%;
            top: -560px;
            right: -230px;
            background: rgba(191, 219, 254, .32);
        }

        .login-card {
            width: 100%;
            max-width: 610px;
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, .97);
            border-radius: 22px;
            overflow: hidden;
            box-shadow:
                0 28px 80px rgba(37, 99, 235, .13),
                0 4px 20px rgba(15, 23, 42, .05);
            border: 1px solid rgba(226, 232, 240, .8);
        }

        .login-content {
            padding: 52px 52px 30px;
        }

        .brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            width: 96px;
            height: 96px;
            margin: 0 auto 20px;
            position: relative;
        }

        .cross {
            position: absolute;
            width: 92px;
            height: 34px;
            background: linear-gradient(135deg, #08a4e8, #1479e9);
            border-radius: 9px;
            top: 29px;
            left: 2px;
        }

        .cross::before {
            content: "";
            position: absolute;
            width: 34px;
            height: 92px;
            background: linear-gradient(135deg, #08a4e8, #1479e9);
            border-radius: 9px;
            top: -29px;
            left: 29px;
        }

        .leaf {
            position: absolute;
            width: 56px;
            height: 33px;
            right: 0;
            bottom: 8px;
            background: linear-gradient(135deg, #16c8b4, #00a7c7);
            border-radius: 100% 0 100% 0;
            transform: rotate(-15deg);
            box-shadow: 0 3px 8px rgba(16, 185, 170, .18);
        }

        .leaf::after {
            content: "";
            position: absolute;
            width: 48px;
            height: 2px;
            background: rgba(255,255,255,.95);
            left: 6px;
            top: 17px;
            transform: rotate(-25deg);
            transform-origin: left;
        }

        .brand h1 {
            margin: 0;
            font-size: 2.05rem;
            line-height: 1.15;
            font-weight: 750;
            letter-spacing: -.035em;
            color: #10213f;
        }

        .brand h1 span {
            color: #1477e8;
        }

        .brand p {
            margin: 10px 0 0;
            font-size: 1.05rem;
            color: #7686a1;
        }

        .divider {
            height: 1px;
            background: #e7edf5;
            margin: 28px 0 30px;
        }

        .form-label {
            font-weight: 650;
            margin-bottom: 9px;
            color: #13213d;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .input-icon {
            position: absolute;
            left: 17px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b8da9;
            font-size: 1.15rem;
            pointer-events: none;
        }

        .form-control {
            height: 58px;
            border-radius: 10px;
            border: 1px solid #cfd9e7;
            padding-left: 52px;
            padding-right: 48px;
            font-size: 1rem;
            color: #17233d;
            box-shadow: none;
            transition: .18s ease;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #2681ed;
            box-shadow: 0 0 0 4px rgba(37, 128, 237, .10);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #7587a3;
            font-size: 1.2rem;
            padding: 4px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 21px 0 25px;
        }

        .form-check {
            margin: 0;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: .1em;
            border: 1px solid #8da0bb;
        }

        .form-check-label {
            margin-left: 5px;
            color: #13213d;
        }

        .forgot-link,
        .back-link {
            color: #0875ee;
            text-decoration: none;
            font-weight: 550;
        }

        .forgot-link:hover,
        .back-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 62px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #147cf3, #0769e8);
            color: white;
            font-size: 1.16rem;
            font-weight: 650;
            box-shadow: 0 9px 24px rgba(7, 105, 232, .18);
            transition: .18s ease;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #0c72e7, #055ecf);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .back-area {
            text-align: center;
            border-top: 1px solid #e7edf5;
            margin-top: 27px;
            padding-top: 27px;
        }

        .back-link i {
            margin-right: 8px;
        }

        .login-footer {
            background: #f8fafc;
            border-top: 1px solid #edf1f6;
            text-align: center;
            color: #7586a0;
            padding: 20px;
            font-size: .92rem;
        }

        .login-footer i {
            margin-right: 8px;
        }

        .validation-error {
            margin-top: 7px;
            color: #dc3545;
            font-size: .875rem;
        }

        .session-status {
            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #ecfdf5;
            color: #047857;
            font-size: .92rem;
        }

        @media (max-width: 650px) {
            .login-page {
                padding: 20px 14px;
            }

            .login-content {
                padding: 38px 25px 25px;
            }

            .login-card {
                border-radius: 17px;
            }

            .brand h1 {
                font-size: 1.7rem;
            }

            .brand-logo {
                transform: scale(.85);
                margin-bottom: 12px;
            }

            .remember-row {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }
        }

        @media (prefers-color-scheme: dark) {
            body:not([data-theme="light"]) .login-page {
                background:
                    radial-gradient(circle at 15% 15%, rgba(30, 64, 175, .22), transparent 32%),
                    radial-gradient(circle at 82% 75%, rgba(14, 116, 144, .14), transparent 35%),
                    #0f172a;
            }
        }
    </style>
</head>

<body>
<div class="login-page">

    <div class="login-card">

        <div class="login-content">

            <div class="brand">

                <div class="brand-logo" aria-hidden="true">
                    <div class="cross"></div>
                    <div class="leaf"></div>
                </div>

                <h1>
                    Sistema de <span>Farmacia</span>
                </h1>

                <p>Inicia sesión para continuar</p>
            </div>

            <div class="divider"></div>

            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Correo --}}
                <div class="mb-4">
                    <label for="email" class="form-label">
                        Correo electrónico
                    </label>

                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="tu@correo.com"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    @error('email')
                        <div class="validation-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="form-label">
                        Contraseña
                    </label>

                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Mostrar u ocultar contraseña"
                        >
                            <i class="bi bi-eye-slash" id="passwordIcon"></i>
                        </button>
                    </div>

                    @error('password')
                        <div class="validation-error">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Recordarme / contraseña --}}
                <div class="remember-row">

                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                        >

                        <label class="form-check-label" for="remember">
                            Recordarme
                        </label>
                    </div>
                </div>

                {{-- Login --}}
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar sesión
                </button>
            </form>
        </div>

        <div class="login-footer">
            <i class="bi bi-shield-check"></i>
            Acceso para personal autorizado
        </div>

    </div>

</div>

<script>
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');
    const icon = document.getElementById('passwordIcon');

    toggle.addEventListener('click', () => {
        const mostrando = password.type === 'text';

        password.type = mostrando ? 'password' : 'text';

        icon.classList.toggle('bi-eye', mostrando);
        icon.classList.toggle('bi-eye-slash', !mostrando);
    });
</script>
</body>
</html>