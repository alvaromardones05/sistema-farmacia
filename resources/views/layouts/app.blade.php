<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Sistema de Farmacia') }}
    </title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- =====================================================
         GOOGLE FONTS
    ====================================================== -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

</head>


<body>


    <!-- =====================================================
         BARRA SUPERIOR
    ====================================================== -->

    <nav class="navbar navbar-dark navbar-farmacia">

        <div class="container-fluid">


            <!-- LOGO + NOMBRE -->

            <a
                href="{{ route('dashboard') }}"
                class="navbar-brand fw-bold"
            >

                <i class="bi bi-capsule"></i>

                Sistema de Farmacia

            </a>


            <!-- USUARIO + DROPDOWN DE PERFIL -->

            <div class="dropdown">

                <button
                    class="btn btn-perfil dropdown-toggle d-flex align-items-center gap-2"
                    type="button"
                    id="perfilDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >

                    <span class="avatar-circle">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>

                    <span class="d-flex flex-column text-start lh-sm">
                        <span class="fw-semibold perfil-nombre">
                            {{ Auth::user()->name }}
                        </span>
                        <small class="perfil-rol">
                            {{ Auth::user()->roles->pluck('name')->first() }}
                        </small>
                    </span>

                </button>


                <ul
                    class="dropdown-menu dropdown-menu-end dropdown-perfil"
                    aria-labelledby="perfilDropdown"
                >

                    <!-- ENCABEZADO DEL DROPDOWN -->

                    <li class="dropdown-header">

                        <div class="d-flex align-items-center gap-3">

                            <span class="avatar-circle avatar-lg">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>

                            <div class="lh-sm text-truncate">

                                <div class="fw-semibold text-dark">
                                    {{ Auth::user()->name }}
                                </div>

                                <small class="text-muted">
                                    {{ Auth::user()->email }}
                                </small>

                            </div>

                        </div>

                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <!-- OPCIÓN PERFIL -->

                    <li>

                        <a
                            class="dropdown-item d-flex align-items-center gap-2"
                            href="{{ route('profile.edit') }}"
                        >

                            <i class="bi bi-person-gear"></i>

                            <span>Mi perfil</span>

                        </a>

                    </li>

                    <!-- OPCIÓN CERRAR SESIÓN -->

                    <li>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                            class="m-0"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item d-flex align-items-center gap-2 text-danger"
                            >

                                <i class="bi bi-box-arrow-right"></i>

                                <span>Cerrar sesión</span>

                            </button>

                        </form>

                    </li>

                </ul>

            </div>

        </div>

    </nav>



    <!-- =====================================================
         CUERPO
    ====================================================== -->

    <div class="d-flex">


        <!-- =================================================
             MENÚ LATERAL
        ================================================== -->

        <aside class="sidebar p-3">


            <h6 class="sidebar-titulo mb-3">
                MENÚ
            </h6>


            <nav class="nav flex-column">


                <!-- DASHBOARD -->

                <a
                    href="{{ route('dashboard') }}"
                    class="nav-link
                    {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >

                    <i class="bi bi-speedometer2 me-2"></i>

                    Dashboard

                </a>

                <!-- INVENTARIO -->

                @hasanyrole('Técnico Farmacéutico|Químico Farmacéutico')
                    <a
                        href="{{ route('inventario.index') }}"
                        class="nav-link
                        {{ request()->routeIs('inventario.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-clipboard-data me-2"></i>

                        Inventario

                    </a>
                @endhasanyrole


                <!-- PRODUCTOS -->
                @hasanyrole('Químico Farmacéutico|Técnico Farmacéutico|Bodeguero')

                    <a
                        href="{{ route('productos.index') }}"
                        class="nav-link
                        {{ request()->routeIs('productos.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-box-seam me-2"></i>

                        Productos

                    </a>

                @endhasanyrole


                <!-- LOTES -->

                @hasanyrole('Técnico Farmacéutico|Químico Farmacéutico|Bodeguero')

                    <a
                        href="{{ route('lotes.index') }}"
                        class="nav-link
                        {{ request()->routeIs('lotes.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-boxes me-2"></i>

                        Lotes

                    </a>

                @endhasanyrole


                <!-- VENTAS -->

                @can('ver_stock')

                    <a
                        href="{{ route('ventas.index') }}"
                        class="nav-link
                        {{ request()->routeIs('ventas.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-cart-check me-2"></i>

                        Ventas

                    </a>

                @endcan


                


                <!-- USUARIOS -->

                @hasrole('Administrador')

                    <a
                        href="{{ route('usuarios.index') }}"
                        class="nav-link
                        {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-people me-2"></i>

                        Usuarios

                    </a>

                @endhasrole


            </nav>

        </aside>



        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <main class="contenido">

            @yield('content')

        </main>


    </div>


    <!-- =====================================================
         BOOTSTRAP JAVASCRIPT
    ====================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <!-- =====================================================
         ESTILOS
    ====================================================== -->

    <style>
       /* =====================================================
   VARIABLES
====================================================== */
:root {
    --mint-900: #064e3b;
    --mint-800: #065f46;
    --mint-700: #047857;
    --mint-600: #059669;
    --mint-500: #10b981;
    --mint-400: #34d399;
    --mint-300: #6ee7b7;
    --mint-100: #d1fae5;
    --mint-50: #ecfdf5;

    --azul-900: #0c4a6e;
    --azul-800: #075985;
    --azul-700: #0369a1;
    --azul-600: #0284c7;
    --azul-500: #0ea5e9;
    --azul-400: #38bdf8;
    --azul-100: #e0f2fe;
    --azul-50: #f0f9ff;

    --gris-900: #0f172a;
    --gris-800: #1e293b;
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

html, body {
    min-height: 100%;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--gris-700);
    margin: 0;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;

    /* Fondo con gradiente mint/azul suave + toques de color */
    background:
        radial-gradient(ellipse at 10% 0%, rgba(16, 185, 129, 0.18), transparent 45%),
        radial-gradient(ellipse at 90% 10%, rgba(14, 165, 233, 0.15), transparent 45%),
        radial-gradient(ellipse at 50% 100%, rgba(52, 211, 153, 0.12), transparent 50%),
        linear-gradient(180deg, #f0fdfa 0%, #f0f9ff 60%, #eef2ff 100%);
    background-attachment: fixed;
}

/* Textura de retícula clínica muy sutil global */
body::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(15, 23, 42, 0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(15, 23, 42, 0.025) 1px, transparent 1px);
    background-size: 44px 44px;
    pointer-events: none;
    z-index: 0;
    mask-image: radial-gradient(ellipse at center, black 30%, transparent 85%);
    -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 85%);
}

/* Burbujas decorativas flotantes */
body::after {
    content: "";
    position: fixed;
    top: -200px;
    right: -200px;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.35), transparent 70%);
    filter: blur(70px);
    pointer-events: none;
    z-index: 0;
    animation: floatBubble 12s ease-in-out infinite;
}

@keyframes floatBubble {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50%      { transform: translate(-40px, 40px) scale(1.1); }
}

/* =====================================================
   NAVBAR
====================================================== */
.navbar-farmacia {
    height: 64px;
    padding: 0 1.5rem;

    background:
        linear-gradient(135deg, var(--mint-700) 0%, var(--azul-700) 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);

    box-shadow:
        0 4px 16px rgba(6, 78, 59, 0.18),
        0 1px 3px rgba(15, 23, 42, 0.08);

    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-farmacia .navbar-brand {
    color: var(--blanco);
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: -0.3px;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    transition: var(--transicion);
}

.navbar-farmacia .navbar-brand i {
    font-size: 1.35rem;
    color: var(--mint-300);
}

.navbar-farmacia .navbar-brand:hover {
    color: var(--mint-100);
    transform: translateY(-1px);
}

.navbar-farmacia .navbar-brand:hover i {
    color: #a7f3d0;
    transform: rotate(-12deg);
}

/* =====================================================
   BOTÓN DE PERFIL
====================================================== */
.btn-perfil {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;

    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: var(--blanco);

    padding: 0.35rem 0.75rem 0.35rem 0.35rem;
    border-radius: 50px;

    transition:
        background 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease;

    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);

    /* 🔑 evita que el botón cambie de tamaño al abrir */
    min-width: 0;
}

.btn-perfil:hover,
.btn-perfil:focus {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.35);
    color: var(--blanco);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    /* ❌ quitamos el translateY(-1px) que movía el layout */
}

/* 🔑 Flechita estable: ancho fijo, no se sale del flujo */
.btn-perfil::after {
    content: "";
    display: inline-block;

    width: 8px;
    height: 8px;
    margin-left: 0.35rem;

    /* dibujamos la flecha manualmente para tener control total */
    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;
    transform: rotate(45deg) translate(-1px, -1px);
    transform-origin: center;

    transition: transform 0.25s ease;
    opacity: 0.85;

    /* 🔑 evita que Bootstrap meta su propio margin */
    vertical-align: middle;
}

/* Al abrir, rota sobre su propio eje sin mover nada */
.btn-perfil[aria-expanded="true"]::after {
    transform: rotate(-135deg) translate(-1px, -1px);
}

/* El avatar y el texto no deben moverse */
.btn-perfil .avatar-circle,
.btn-perfil .perfil-nombre,
.btn-perfil .perfil-rol {
    transition: none;
}

/* =====================================================
   AVATAR
====================================================== */
.avatar-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--mint-800);

    background: linear-gradient(135deg, var(--blanco) 0%, var(--mint-100) 100%);
    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.12),
        inset 0 -1px 2px rgba(16, 185, 129, 0.15);
    flex-shrink: 0;
    transition: var(--transicion);
}

.btn-perfil:hover .avatar-circle {
    transform: scale(1.05);
}

.avatar-lg {
    width: 46px;
    height: 46px;
    font-size: 1.15rem;
    color: var(--blanco);
    background: linear-gradient(135deg, var(--mint-500) 0%, var(--azul-600) 100%);
    box-shadow:
        0 4px 12px rgba(16, 185, 129, 0.3),
        inset 0 -1px 2px rgba(0, 0, 0, 0.1);
}

/* =====================================================
   DROPDOWN DE PERFIL
====================================================== */
.dropdown-perfil {
    min-width: 280px;
    background: var(--blanco);
    border: 1px solid var(--gris-200);
    border-radius: 16px;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.04),
        0 12px 32px rgba(15, 23, 42, 0.12),
        0 32px 64px rgba(15, 23, 42, 0.06);

    padding: 0.5rem;
    margin-top: 0.6rem !important;
    animation: dropdownSlide 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;

}

@keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-10px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.dropdown-perfil::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--mint-500), var(--azul-500));
}

.dropdown-perfil .dropdown-header {
    padding: 0.85rem 0.9rem 0.65rem;
    white-space: normal;
}

.dropdown-perfil .dropdown-header .fw-semibold {
    color: var(--gris-900) !important;
    font-size: 0.92rem;
}

.dropdown-perfil .dropdown-header small {
    color: var(--gris-500) !important;
    font-size: 0.78rem;
}

.dropdown-perfil .dropdown-divider {
    border-color: var(--gris-200);
    margin: 0.35rem 0.5rem;
    opacity: 0.8;
}

.dropdown-perfil .dropdown-item {
    color: var(--gris-700);
    border-radius: 10px;
    padding: 0.65rem 0.8rem;
    font-size: 0.9rem;
    font-weight: 500;
    transition: var(--transicion);
}

.dropdown-perfil .dropdown-item i {
    font-size: 1.05rem;
    width: 20px;
    text-align: center;
    color: var(--mint-600);
    transition: var(--transicion);
}

.dropdown-perfil .dropdown-item:hover {
    background: linear-gradient(90deg, var(--mint-50) 0%, var(--azul-50) 100%);
    color: var(--mint-900);
    transform: translateX(3px);
}

.dropdown-perfil .dropdown-item:hover i {
    color: var(--mint-700);
    transform: scale(1.1);
}

.dropdown-perfil .dropdown-item.text-danger {
    color: #dc2626 !important;
}

.dropdown-perfil .dropdown-item.text-danger i {
    color: #dc2626;
}

.dropdown-perfil .dropdown-item.text-danger:hover {
    background: #fef2f2;
    color: #b91c1c !important;
    transform: translateX(3px);
}

.dropdown-perfil .dropdown-item.text-danger:hover i {
    color: #b91c1c;
}

/* =====================================================
   SIDEBAR — CON TINTE MINT/AZUL
====================================================== */
.sidebar {
    width: 240px;
    min-height: calc(100vh - 64px);
    padding: 1.5rem 1rem !important;

    /* Fondo con gradiente suave + tinte mint/azul */
    background:
        radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.08), transparent 50%),
        radial-gradient(circle at 100% 100%, rgba(14, 165, 233, 0.08), transparent 50%),
        linear-gradient(180deg, #ffffff 0%, #f5fffb 50%, #f0f9ff 100%);

    border-right: 1px solid rgba(16, 185, 129, 0.15);

    box-shadow:
        4px 0 24px rgba(15, 23, 42, 0.04),
        inset -1px 0 0 rgba(255, 255, 255, 0.6);

    position: sticky;
    top: 64px;
    align-self: flex-start;
    height: calc(100vh - 64px);
    overflow-y: auto;
}

/* Barra decorativa lateral mint→azul */
.sidebar::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(
        180deg,
        var(--mint-500) 0%,
        var(--azul-500) 60%,
        var(--azul-400) 100%
    );
    opacity: 0.9;
}

/* Cruz farmacéutica de fondo muy sutil */
.sidebar::after {
    content: "✚";
    position: absolute;
    bottom: 20px;
    right: 15px;
    font-size: 5rem;
    font-weight: 900;
    color: rgba(16, 185, 129, 0.05);
    pointer-events: none;
    line-height: 1;
    z-index: 0;
}

.sidebar .nav,
.sidebar-titulo {
    position: relative;
    z-index: 1;
}

.sidebar-titulo {
    color: var(--gris-500);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding-left: 0.75rem;
    margin-top: 0.25rem;
    text-transform: uppercase;
}

.sidebar-titulo::after {
    content: "";
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--mint-500);
    margin-left: 0.5rem;
    vertical-align: middle;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    animation: dotPulse 2.5s ease-in-out infinite;
}

@keyframes dotPulse {
    0%, 100% { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
    50%      { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.08); }
}

/* =====================================================
   LINKS DEL SIDEBAR
====================================================== */
.sidebar .nav-link {
    color: var(--gris-600);
    padding: 11px 14px;
    border-radius: 10px;
    margin-bottom: 4px;
    font-weight: 500;
    font-size: 0.92rem;
    display: flex;
    align-items: center;
    transition: var(--transicion);
    position: relative;
    overflow: hidden;
}

.sidebar .nav-link i {
    font-size: 1.05rem;
    color: var(--gris-500);
    transition: var(--transicion);
}

.sidebar .nav-link:hover {
    background: linear-gradient(90deg, var(--mint-50) 0%, var(--azul-50) 100%);
    color: var(--mint-800);
    transform: translateX(3px);
    box-shadow:
        inset 3px 0 0 var(--mint-500),
        0 3px 10px rgba(16, 185, 129, 0.1);
}

.sidebar .nav-link:hover i {
    color: var(--mint-600);
    transform: scale(1.12);
}

.sidebar .nav-link.active {
    background: linear-gradient(
        135deg,
        var(--mint-600) 0%,
        var(--mint-500) 40%,
        var(--azul-600) 100%
    );
    color: var(--blanco);
    box-shadow:
        0 6px 18px rgba(16, 185, 129, 0.32),
        0 2px 5px rgba(16, 185, 129, 0.18);
}

.sidebar .nav-link.active i {
    color: var(--blanco);
}

.sidebar .nav-link.active::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    width: 40%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.18));
    border-radius: 0 10px 10px 0;
    pointer-events: none;
}

/* =====================================================
   CONTENIDO
====================================================== */
.contenido {
    flex: 1;
    padding: 32px;
    min-height: calc(100vh - 64px);
    position: relative;
    /* Fondo semitransparente para dejar ver el fondo global */
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

.contenido > * {
    position: relative;
    z-index: 1;
}

/* =====================================================
   TARJETAS (lista-dashboard / item-dashboard)
====================================================== */
.lista-dashboard {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);

    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 16px;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.04),
        0 8px 24px rgba(15, 23, 42, 0.06),
        0 24px 48px rgba(15, 23, 42, 0.04);

    overflow: hidden;
    transition: var(--transicion);
    position: relative;
}

.lista-dashboard::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--mint-500), var(--azul-500));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 16px 16px 0 0;
}

.lista-dashboard:hover::before {
    transform: scaleX(1);
}

.lista-dashboard:hover {
    box-shadow:
        0 2px 4px rgba(15, 23, 42, 0.05),
        0 16px 40px rgba(15, 23, 42, 0.1),
        0 32px 64px rgba(15, 23, 42, 0.06);
    transform: translateY(-3px);
    border-color: var(--mint-300);
    background: rgba(255, 255, 255, 1);
}

.item-dashboard {
    padding: 14px 18px;
    border-bottom: 1px solid var(--gris-100);
    color: var(--gris-600);
    font-size: 0.92rem;
    font-weight: 500;
    transition: var(--transicion);
    display: flex;
    align-items: center;
    gap: 0.6rem;
    position: relative;
}

.item-dashboard::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%) scaleY(0);
    width: 3px;
    height: 60%;
    background: linear-gradient(180deg, var(--mint-500), var(--azul-500));
    border-radius: 0 3px 3px 0;
    transition: transform 0.3s ease;
}

.item-dashboard:hover {
    background: linear-gradient(90deg, var(--mint-50) 0%, transparent 100%);
    color: var(--mint-800);
    padding-left: 22px;
}

.item-dashboard:hover::before {
    transform: translateY(-50%) scaleY(1);
}

.item-dashboard:last-child {
    border-bottom: none;
}

/* =====================================================
   SCROLLBAR
====================================================== */
.sidebar::-webkit-scrollbar { width: 6px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb {
    background: var(--gris-300);
    border-radius: 10px;
}
.sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--mint-400);
}

body::-webkit-scrollbar { width: 10px; }
body::-webkit-scrollbar-track { background: rgba(240, 253, 250, 0.5); }
body::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, var(--mint-500), var(--azul-500));
    border-radius: 10px;
    border: 2px solid rgba(240, 253, 250, 0.8);
}
body::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, var(--mint-600), var(--azul-600));
}

/* =====================================================
   RESPONSIVE
====================================================== */
@media (max-width: 768px) {
    .sidebar {
        width: 72px;
        padding: 1rem 0.5rem !important;
    }

    .sidebar .nav-link {
        justify-content: center;
        padding: 12px 8px;
    }

    .sidebar .nav-link i {
        margin-right: 0 !important;
        font-size: 1.2rem;
    }

    .sidebar .nav-link span,
    .sidebar-titulo {
        display: none;
    }

    .sidebar::after {
        font-size: 3rem;
        bottom: 10px;
        right: 8px;
    }

    .perfil-nombre,
    .perfil-rol {
        display: none;
    }

    .btn-perfil {
        padding: 0.3rem;
    }

    .btn-perfil::after {
        display: none;
    }

    .contenido {
        padding: 18px;
    }

    body::before {
        display: none;
    }
}
        
    </style>


</body>

</html>

