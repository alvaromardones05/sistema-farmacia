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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>

<body>
    <!-- =====================================================
         BARRA SUPERIOR
    ====================================================== -->

    <nav class="navbar navbar-dark navbar-farmacia">
        <div class="container-fluid">

            <a href="{{ route('dashboard') }}" class="navbar-brand fw-bold">
                <i class="bi bi-capsule"></i>
                Sistema de Farmacia
            </a>

            <button type="button" class="btn-theme" id="themeToggle" aria-label="Cambiar tema">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
            </button>

            <div class="dropdown">
                <button class="btn btn-perfil dropdown-toggle d-flex align-items-center gap-2"
                        type="button" id="perfilDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar-circle">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="d-flex flex-column text-start lh-sm">
                        <span class="fw-semibold perfil-nombre">{{ Auth::user()->name }}</span>
                        <small class="perfil-rol">{{ Auth::user()->roles->pluck('name')->first() }}</small>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end dropdown-perfil" aria-labelledby="perfilDropdown">
                    <li class="dropdown-header">
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar-circle" style="width:44px;height:44px;border-radius:12px;font-size:1rem;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <div class="lh-sm text-truncate">
                                <div class="fw-semibold" style="color:var(--text);">{{ Auth::user()->name }}</div>
                                <small style="font-family:,monospace;font-size:.72rem;color:var(--text-muted);">
                                    {{ Auth::user()->email }}
                                </small>
                            </div>
                        </div>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Mi perfil</span>
                        </a>
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
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

                @hasanyrole('Administrador|Químico Farmacéutico')
                    <a href="{{ route('convenios.index') }}"
                    class="nav-link {{ request()->routeIs('convenios.*') ? 'active' : '' }}">
                        <i class="bi bi-card-checklist me-2"></i>
                        Convenios
                    </a>
                @endhasanyrole


                @role('Químico Farmacéutico')
                    <a href="{{ route('descuadres-controlados.index') }}"
                    class="nav-link {{ request()->routeIs('descuadres-controlados.*') ? 'active' : '' }}">

                        <i class="bi bi-shield-check me-2"></i>
                        Descuadres controlados

                    </a>
                @endrole

                <!-- CAJA -->

                @hasrole('Técnico Farmacéutico')
                    <a
                    
                        href="{{ route('caja.index') }}"
                        class="nav-link
                        {{ request()->routeIs('caja.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-cash-coin me-2"></i>

                        Caja

                    </a>

                @endhasrole

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

                <!-- COMPRAS / RECEPCIONES -->

                @hasanyrole('Técnico Farmacéutico|Químico Farmacéutico|Bodeguero')
                    <a
                        href="{{ route('compras.index') }}"
                        class="nav-link {{ request()->routeIs('compras.*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-truck me-2"></i>
                        Compras / Recepciones
                    </a>
                @endhasanyrole


                <!-- VENTAS -->

                @hasanyrole('Técnico Farmacéutico|Químico Farmacéutico')
                    <a href="{{ route('ventas.index') }}" class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                        <i class="bi bi-cart-check me-2"></i>Ventas
                    </a>
                @endhasanyrole


                <!-- PACIENTES -->
                @hasanyrole('Técnico Farmacéutico|Químico Farmacéutico')
                    <a
                    
                        href="{{ route('pacientes.index') }}"
                        class="nav-link
                        {{ request()->routeIs('pacientes.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-person-heart me-2"></i>

                        Pacientes

                    </a>
                @endhasanyrole


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

    <style>
    /* =====================================================
    DESIGN TOKENS
    ====================================================== */
    :root {
        --sidebar-width: 280px;
        --topbar-height: 72px;

        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: #dbeafe;
        --primary-soft: #eff6ff;
        --accent: #06b6d4;
        --accent-hover: #0891b2;
        --accent-light: #cffafe;

        --gradient: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
        --gradient-soft: linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);

        --sidebar-bg: #ffffff;
        --sidebar-bg-2: #f8fbff;
        --sidebar-border: #e2ebf5;
        --sidebar-text: #475569;
        --sidebar-text-hover: #0f172a;
        --sidebar-item-hover: #f1f7fe;
        --sidebar-item-active-bg: linear-gradient(135deg, #e0edff 0%, #d5f4ff 100%);

        --surface: #ffffff;
        --surface-2: #f8fafc;
        --surface-3: #f1f5f9;
        --page-bg: #f0f6fc;
        --border: #e2e8f0;
        --border-2: #cbd5e1;

        --text: #0f172a;
        --text-2: #475569;
        --text-muted: #64748b;
        --text-soft: #94a3b8;

        --success: #059669;
        --success-soft: #d1fae5;
        --danger: #dc2626;
        --danger-soft: #fee2e2;
        --warning: #d97706;
        --warning-soft: #fef3c7;
        --info: #2563eb;
        --info-soft: #dbeafe;

        --radius-xs: 6px;
        --radius-sm: 10px;
        --radius: 14px;
        --radius-lg: 20px;

        --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
        --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
        --shadow: 0 2px 4px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.05);
        --shadow-md: 0 4px 8px rgba(15, 23, 42, 0.05), 0 8px 24px rgba(15, 23, 42, 0.07);
        --shadow-lg: 0 12px 32px rgba(15, 23, 42, 0.1), 0 4px 8px rgba(15, 23, 42, 0.04);
        --shadow-focus: 0 0 0 4px rgba(37, 99, 235, 0.12);

        --t-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        --t: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* =====================================================
    MODO OSCURO
    ====================================================== */
    [data-bs-theme="dark"] {
        /* ---- Acento azul (botones primarios, links, focus) ---- */
        --primary: #2f81f7;
        --primary-hover: #4a94fa;
        --primary-light: rgba(47, 129, 247, 0.15);
        --primary-soft: rgba(47, 129, 247, 0.08);
        --accent: #2f81f7;
        --accent-hover: #4a94fa;
        --accent-light: rgba(47, 129, 247, 0.15);

        --gradient: #2f81f7;
        --gradient-soft: rgba(47, 129, 247, 0.08);

        /* ---- Sidebar: gris oscuro (no negro) ---- */
        --sidebar-bg: #1a1b1e;
        --sidebar-bg-2: #212226;
        --sidebar-border: #33343a;
        --sidebar-text: #a0a4ad;
        --sidebar-text-hover: #ffffff;
        --sidebar-item-hover: rgba(255, 255, 255, 0.06);
        --sidebar-item-active-bg: rgba(47, 129, 247, 0.14);

        /* ---- Superficies gris oscuro ---- */
        --surface: #232428;
        --surface-2: #2a2b30;
        --surface-3: #313238;
        --page-bg: #1c1d21;

        /* ---- Bordes visibles ---- */
        --border: #363740;
        --border-2: #454651;

        /* ---- Texto: blanco puro + grises claros ---- */
        --text: #ffffff;
        --text-2: #c9d1d9;
        --text-muted: #8b949e;
        --text-soft: #6e7681;

        /* ---- Estados: colores sólidos tipo GitHub ---- */
        --success: #2ea043;
        --success-soft: rgba(46, 160, 67, 0.15);
        --danger: #da3633;
        --danger-soft: rgba(218, 54, 51, 0.15);
        --warning: #bb8009;
        --warning-soft: rgba(187, 128, 9, 0.15);
        --info: #58a6ff;
        --info-soft: rgba(88, 166, 255, 0.15);

        /* ---- Sombras ---- */
        --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.4);
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.45);
        --shadow: 0 2px 6px rgba(0, 0, 0, 0.5);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.55);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.6);
        --shadow-focus: 0 0 0 3px rgba(47, 129, 247, 0.35);
    }

    [data-bs-theme="dark"] body { background: var(--page-bg); }
    [data-bs-theme="dark"] body::before { display: none; }

    [data-bs-theme="dark"] .sidebar::before,
    [data-bs-theme="dark"] .sidebar::after { display: none; }

    [data-bs-theme="dark"] .sidebar .nav-link.active::before {
        background: var(--primary);
        box-shadow: none;
    }

    [data-bs-theme="dark"] .btn-close { filter: invert(1) hue-rotate(180deg); }




    /* =====================================================
    BASE
    ====================================================== */
    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 16.5px;
        line-height: 1.55;
        background: var(--page-bg);
        color: var(--text);
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .tnum {
        font-feature-settings: 'tnum';
        letter-spacing: -0.02em;
        font-weight: 600;
    }

    .micro-label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-soft);
    }

    /* =====================================================
    NAVBAR SUPERIOR
    ====================================================== */
    .navbar-farmacia {
        position: sticky;
        top: 0;
        z-index: 1030;
        height: var(--topbar-height);
        padding: 0 1.75rem;
        margin: 0;
        background: color-mix(in srgb, var(--surface) 78%, transparent);
        backdrop-filter: saturate(180%) blur(18px);
        -webkit-backdrop-filter: saturate(180%) blur(18px);
        border-bottom: 1px solid var(--border);
        box-shadow: var(--shadow-xs);
        transition: background 0.4s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .navbar-farmacia .container-fluid {
        height: 100%;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        flex-wrap: nowrap;
        padding: 0;
        max-width: none;
    }

    .navbar-farmacia .navbar-brand {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-right: auto;
        padding: 0;
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--text) !important;
        white-space: nowrap;
    }

    .navbar-farmacia .navbar-brand i {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--gradient);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
        transition: var(--t);
    }

    .navbar-farmacia .navbar-brand:hover i {
        transform: rotate(-8deg) scale(1.06);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.42);
    }

    .btn-theme {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--t);
        font-size: 1.15rem;
        flex-shrink: 0;
        padding: 0;
    }

    .btn-theme:hover {
        color: var(--primary);
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .btn-perfil {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.4rem 0.95rem 0.4rem 0.4rem;
        border-radius: 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        color: var(--text);
        cursor: pointer;
        transition: var(--t);
        font-family: inherit;
    }

    .btn-perfil:hover,
    .btn-perfil.show {
        border-color: var(--primary);
        box-shadow: var(--shadow);
        transform: translateY(-1px);
        color: var(--text);
    }

    .btn-perfil::after { display: none; }

    .avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--gradient);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
        text-transform: uppercase;
    }

    .perfil-nombre {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text);
    }

    .perfil-rol {
        font-size: 0.72rem;
        color: var(--text-muted);
        letter-spacing: 0.02em;
        margin-top: 1px;
    }

    /* Dropdown de perfil */
    .dropdown-perfil {
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 0.5rem;
        min-width: 280px;
        margin-top: 0.6rem !important;
        background: var(--surface);
        animation: ddIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes ddIn {
        from { opacity: 0; transform: translateY(-6px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .dropdown-perfil .dropdown-header {
        padding: 0.8rem 0.85rem;
        white-space: normal;
    }

    .dropdown-perfil .dropdown-item {
        border-radius: 10px;
        padding: 0.7rem 0.85rem;
        font-size: 0.92rem;
        font-weight: 500;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.7rem;
        transition: var(--t-fast);
    }

    .dropdown-perfil .dropdown-item i {
        font-size: 1.1rem;
        color: var(--text-muted);
        transition: var(--t-fast);
    }

    .dropdown-perfil .dropdown-item:hover {
        background: var(--primary-light);
        color: var(--primary-hover);
    }

    .dropdown-perfil .dropdown-item:hover i { color: var(--primary); }

    .dropdown-perfil .dropdown-item.text-danger { color: var(--danger) !important; }
    .dropdown-perfil .dropdown-item.text-danger i { color: var(--danger); }
    .dropdown-perfil .dropdown-item.text-danger:hover {
        background: var(--danger-soft);
        color: var(--danger) !important;
    }

    .dropdown-perfil .dropdown-divider {
        border-color: var(--border);
        opacity: 1;
        margin: 0.4rem 0;
    }

    /* =====================================================
    SIDEBAR
    ====================================================== */
    .sidebar {
        position: sticky;
        top: var(--topbar-height);
        width: var(--sidebar-width);
        height: calc(100vh - var(--topbar-height));
        flex-shrink: 0;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        border-right: 1px solid var(--sidebar-border);
        overflow-y: auto;
        padding: 1.75rem 1rem !important;
        transition: background 0.4s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 5;
    }

    

    .sidebar > * { position: relative; z-index: 1; }

    .sidebar::-webkit-scrollbar { width: 5px; }
    .sidebar::-webkit-scrollbar-track { background: transparent; }
    .sidebar::-webkit-scrollbar-thumb {
        background: var(--sidebar-border);
        border-radius: 3px;
    }

    .sidebar-titulo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 0.75rem;
        margin: 0.6rem 0 1.1rem !important;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--sidebar-text);
        opacity: 0.75;
    }

    .sidebar-titulo::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--sidebar-border);
    }

    .sidebar .nav { gap: 3px; }

    .sidebar .nav-link {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.8rem 0.9rem;
        border-radius: 11px;
        color: var(--sidebar-text);
        font-size: 0.95rem;
        font-weight: 500;
        transition: var(--t);
        position: relative;
        border: none;
    }

    .sidebar .nav-link i {
        font-size: 1.15rem;
        width: 22px;
        text-align: center;
        transition: var(--t);
        flex-shrink: 0;
    }

    .sidebar .nav-link:hover {
        background: var(--sidebar-item-hover);
        color: var(--sidebar-text-hover);
    }

    .sidebar .nav-link:hover i {
        color: var(--accent);
        transform: scale(1.1);
    }

    .sidebar .nav-link.active {
        background: var(--sidebar-item-active-bg);
        color: var(--sidebar-text-hover);
        font-weight: 600;
    }

    [data-bs-theme="light"] .sidebar .nav-link.active {
        color: var(--primary-hover);
    }

    .sidebar .nav-link.active i { color: var(--accent); }

    .sidebar .nav-link.active::before {
        content: '';
        position: absolute;
        left: -1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 26px;
        background: var(--gradient);
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 12px rgba(37, 99, 235, 0.5);
    }

    

    /* =====================================================
    CONTENIDO
    ====================================================== */
    .contenido {
        flex: 1 1 auto;
        min-width: 0;
        min-height: calc(100vh - var(--topbar-height));
        padding: 2rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* =====================================================
    TIPOGRAFÍA
    ====================================================== */
    .contenido h1, .contenido h2, .contenido h3, .contenido h4, .contenido h5 {
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--text);
        margin-bottom: 0.6rem;
    }
    .contenido h1 { font-size: 1.85rem; }
    .contenido h2 { font-size: 1.5rem; }
    .contenido h3 { font-size: 1.2rem; }
    .contenido p { color: var(--text-2); }
    .contenido hr { border-color: var(--border); opacity: 1; margin: 1.25rem 0; }
    .contenido a { color: var(--primary); text-decoration: none; transition: var(--t-fast); }
    .contenido a:hover { color: var(--accent); }

    .micro-label, .eyebrow {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-soft);
    }

    /* =====================================================
    CARDS
    ====================================================== */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-xs);
        color: var(--text);
        overflow: hidden;
        transition: var(--t);
    }
    .card:hover { box-shadow: var(--shadow-sm); }
    .card-header {
        padding: 1.1rem 1.4rem;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        font-weight: 700;
        font-size: 1rem;
        color: var(--text);
        letter-spacing: -0.01em;
    }
    .card-body { padding: 1.4rem; color: var(--text-2); }
    .card-footer {
        padding: 1rem 1.4rem;
        background: var(--surface-2);
        border-top: 1px solid var(--border);
    }

    /* =====================================================
    TABLAS
    ====================================================== */
    .table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--text-2);
        --bs-table-border-color: var(--border);
        --bs-table-hover-bg: var(--primary-soft);
        --bs-table-hover-color: var(--text);
        --bs-table-striped-bg: var(--surface-2);
        --bs-table-striped-color: var(--text-2);
        color: var(--text-2);
        margin-bottom: 0;
        border-color: var(--border);
        vertical-align: middle;
    }

    .table > thead th {
        background: var(--surface-2) !important;
        color: var(--text-muted) !important;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.95rem 1rem;
        border-bottom: 1px solid var(--border) !important;
        white-space: nowrap;
    }

    .table > tbody td {
        padding: 0.95rem 1rem;
        border-color: var(--border);
        font-size: 0.92rem;
        color: var(--text-2);
        vertical-align: middle;
    }

    .table tbody tr { transition: var(--t-fast); }

    .table tbody tr:hover > * {
        background: var(--primary-soft) !important;
        color: var(--text) !important;
    }

    .table-bordered { border-color: var(--border) !important; }
    .table-bordered > :not(caption) > * > * { border-color: var(--border) !important; }

    .table-striped > tbody > tr:nth-of-type(odd) > * {
        background: var(--surface-2);
        color: var(--text-2);
    }

    .table-responsive { border-radius: var(--radius); }

    /* =====================================================
    BOTONES — planos, simples, con animación
    ====================================================== */
    .btn {
        font-family: inherit;
        font-weight: 600;
        font-size: 0.92rem;
        border-radius: 10px;
        padding: 0.6rem 1.1rem;
        border: 1px solid transparent;
        transition: var(--t);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        line-height: 1.3;
    }
    .btn:active { transform: scale(0.97); }
    .btn:hover  { transform: translateY(-1px); }

    .btn-primary         { background: var(--primary);  border-color: var(--primary);  color: #fff !important; }
    .btn-primary:hover   { background: var(--primary-hover); border-color: var(--primary-hover); }

    .btn-outline-primary       { background: transparent; border-color: var(--primary); color: var(--primary) !important; }
    .btn-outline-primary:hover { background: var(--primary); color: #fff !important; }

    .btn-secondary         { background: var(--surface-2); border-color: var(--border); color: var(--text); }
    .btn-secondary:hover   { background: var(--surface-3); border-color: var(--border-2); }

    .btn-success         { background: var(--success); border-color: var(--success); color: #fff; }
    .btn-success:hover   { filter: brightness(1.1); }

    .btn-danger          { background: var(--danger);  border-color: var(--danger);  color: #fff; }
    .btn-danger:hover    { filter: brightness(1.1); }

    .btn-warning         { background: var(--warning); border-color: var(--warning); color: #fff; }
    .btn-warning:hover   { filter: brightness(1.1); }

    .btn-light           { background: var(--surface); border-color: var(--border); color: var(--text); }
    .btn-light:hover     { background: var(--surface-2); border-color: var(--border-2); }

    .btn-outline-secondary       { background: transparent; border-color: var(--border); color: var(--text-2); }
    .btn-outline-secondary:hover { background: var(--surface-2); border-color: var(--border-2); color: var(--text); }

    .btn-outline-danger          { background: transparent; border-color: var(--danger); color: var(--danger); }
    .btn-outline-danger:hover    { background: var(--danger); color: #fff; }

    .btn-sm { padding: 0.45rem 0.85rem; font-size: 0.85rem; border-radius: 8px; gap: 0.35rem; }
    .btn-lg { padding: 0.8rem 1.5rem; font-size: 1rem; border-radius: 12px; }

    /* =====================================================
    FORMULARIOS
    ====================================================== */
    .form-label, .form-check-label {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-2);
        margin-bottom: 0.45rem;
    }

    .form-control, .form-select {
        padding: 0.65rem 0.95rem;
        font-size: 0.94rem;
        font-family: inherit;
        color: var(--text);
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        transition: var(--t-fast);
        outline: none;
    }

    .form-control::placeholder { color: var(--text-soft); }

    .form-control:focus, .form-select:focus {
        color: var(--text);
        background-color: var(--surface);
        border-color: var(--primary);
        box-shadow: var(--shadow-focus);
    }

    .form-control:disabled, .form-select:disabled {
        background: var(--surface-2);
        color: var(--text-muted);
        opacity: 0.7;
    }

    .input-group-text {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 10px;
    }

    .form-check-input {
        border-color: var(--border-2);
        background-color: var(--surface);
        cursor: pointer;
        transition: var(--t-fast);
    }
    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .form-check-input:focus {
        border-color: var(--primary);
        box-shadow: var(--shadow-focus);
    }

    .form-text { color: var(--text-muted); font-size: 0.82rem; }
    .invalid-feedback { color: var(--danger); font-size: 0.82rem; }

    /* =====================================================
    BADGES
    ====================================================== */
    .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.38em 0.75em;
        border-radius: 20px;
        letter-spacing: 0.02em;
        line-height: 1.3;
    }
    .badge.bg-primary    { background: var(--primary) !important; color: #fff; }
    .badge.bg-secondary  { background: var(--surface-3) !important; color: var(--text-2); }
    .badge.bg-success    { background: var(--success-soft) !important; color: var(--success); }
    .badge.bg-danger     { background: var(--danger-soft) !important; color: var(--danger); }
    .badge.bg-warning    { background: var(--warning-soft) !important; color: var(--warning); }
    .badge.bg-info       { background: var(--info-soft) !important; color: var(--info); }
    .badge.bg-light      { background: var(--surface-3) !important; color: var(--text-2); }
    .badge.bg-dark       { background: var(--text) !important; color: var(--surface); }

    /* =====================================================
    ALERTAS
    ====================================================== */
    .alert {
        padding: 1rem 1.2rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        border-left-width: 4px;
        background: var(--surface);
        color: var(--text);
        font-size: 0.92rem;
        box-shadow: var(--shadow-xs);
    }
    .alert-success { border-left-color: var(--success); background: var(--success-soft); color: var(--text); }
    .alert-danger  { border-left-color: var(--danger);  background: var(--danger-soft);  color: var(--text); }
    .alert-warning { border-left-color: var(--warning); background: var(--warning-soft); color: var(--text); }
    .alert-info    { border-left-color: var(--info);    background: var(--info-soft);    color: var(--text); }

    /* =====================================================
    MODALES
    ====================================================== */
    .modal-content {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        color: var(--text);
    }
    .modal-header {
        padding: 1.2rem 1.4rem;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
    }
    .modal-title { font-weight: 700; font-size: 1.1rem; letter-spacing: -0.02em; color: var(--text); }
    .modal-body { padding: 1.4rem; color: var(--text-2); }
    .modal-footer {
        padding: 1rem 1.4rem;
        border-top: 1px solid var(--border);
        background: var(--surface-2);
    }

    

    /* =====================================================
    DROPDOWNS GENÉRICOS
    ====================================================== */
    .dropdown-menu {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-lg);
        padding: 0.4rem;
        color: var(--text);
    }
    .dropdown-item {
        border-radius: 8px;
        padding: 0.55rem 0.75rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text);
        transition: var(--t-fast);
    }
    .dropdown-item:hover, .dropdown-item:focus {
        background: var(--primary-soft);
        color: var(--primary-hover);
    }
    .dropdown-divider { border-color: var(--border); opacity: 1; }

    /* =====================================================
    PAGINACIÓN
    ====================================================== */
    .pagination { gap: 4px; margin: 0; }
    .pagination .page-link {
        margin: 0;
        padding: 0.55rem 0.9rem;
        border-radius: 9px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-2);
        font-size: 0.9rem;
        font-weight: 600;
        transition: var(--t-fast);
    }
    .pagination .page-link:hover {
        background: var(--primary-soft);
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-1px);
    }
    .pagination .page-item.active .page-link {
        background: var(--gradient);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .pagination .page-item.disabled .page-link {
        background: var(--surface-2);
        color: var(--text-soft);
        opacity: 0.6;
    }

    /* =====================================================
    TABS / PILLS
    ====================================================== */
    .nav-tabs { border-bottom: 1px solid var(--border); gap: 2px; }
    .nav-tabs .nav-link {
        color: var(--text-muted);
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 10px 10px 0 0;
        font-weight: 600;
        font-size: 0.92rem;
        padding: 0.65rem 1.1rem;
        transition: var(--t-fast);
    }
    .nav-tabs .nav-link:hover {
        background: var(--surface-2);
        color: var(--primary);
        border-color: transparent;
    }
    .nav-tabs .nav-link.active {
        color: var(--primary);
        background: var(--surface);
        border-color: var(--border) var(--border) transparent;
        border-bottom: 2px solid var(--primary);
        margin-bottom: -1px;
    }

    .nav-pills .nav-link {
        color: var(--text-2);
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.55rem 1rem;
        transition: var(--t-fast);
    }
    .nav-pills .nav-link:hover { background: var(--surface-2); color: var(--primary); }
    .nav-pills .nav-link.active {
        background: var(--gradient);
        color: #fff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* =====================================================
    LIST GROUP
    ====================================================== */
    .list-group-item {
        background: var(--surface);
        border-color: var(--border);
        color: var(--text);
        padding: 0.95rem 1.2rem;
        font-size: 0.92rem;
        transition: var(--t-fast);
    }
    .list-group-item:hover { background: var(--surface-2); }
    .list-group-item.active {
        background: var(--gradient);
        border-color: transparent;
        color: #fff;
    }

    /* =====================================================
    PROGRESS / ACCORDION
    ====================================================== */
    .progress {
        background: var(--surface-3);
        border-radius: 20px;
        height: 8px;
        overflow: hidden;
    }
    .progress-bar {
        background: var(--gradient);
        border-radius: 20px;
        transition: width 0.6s ease;
    }

    .accordion-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm) !important;
        margin-bottom: 0.5rem;
        overflow: hidden;
    }
    .accordion-button {
        background: var(--surface);
        color: var(--text);
        font-weight: 600;
        font-size: 0.95rem;
        padding: 1.1rem 1.2rem;
    }
    .accordion-button:not(.collapsed) {
        background: var(--primary-soft);
        color: var(--primary);
        box-shadow: none;
    }
    .accordion-button:focus { box-shadow: var(--shadow-focus); }
    .accordion-body { color: var(--text-2); }

    /* =====================================================
    RESPONSIVE
    ====================================================== */
    @media (max-width: 991.98px) {
        body > .d-flex { flex-direction: column; }

        .sidebar {
            position: static;
            width: 100%;
            height: auto;
            max-height: 44vh;
            border-right: none;
            border-bottom: 1px solid var(--sidebar-border);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
        }

        .sidebar .nav-link.active::before { display: none; }

        .contenido { padding: 1.4rem; }

        .navbar-farmacia { padding: 0 1.2rem; }
        .perfil-nombre, .perfil-rol { display: none; }
    }

    @media (max-width: 575.98px) {
        .navbar-farmacia .navbar-brand { font-size: 1rem; }
        .navbar-farmacia .navbar-brand i { width: 38px; height: 38px; font-size: 1.15rem; }
        .contenido { padding: 1.1rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.001ms !important;
            transition-duration: 0.001ms !important;
        }
    }

    /* =====================================================
    GRID DE STATS
    ====================================================== */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 10px;
    }

    @media (max-width: 1199.98px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 575.98px) {
        .stats-row { grid-template-columns: 1fr; }
    }

    /* =====================================================
    AJUSTES MENORES DE ESPACIADO
    ====================================================== */
    .contenido h1 {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.02em;
        margin-bottom: 0.4rem;
    }

    .contenido > p,
    .contenido .page-subtitle {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin-bottom: 1.9rem;
    }
    </style>


    <script>
        // ===== TEMA CLARO / OSCURO =====
        (function () {
            const htmlEl = document.documentElement;
            const btn    = document.getElementById('themeToggle');
            const icon   = document.getElementById('themeIcon');

            // Cargar preferencia
            const saved = localStorage.getItem('theme')
                || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            apply(saved);

            btn && btn.addEventListener('click', () => {
                const next = htmlEl.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                apply(next);
                localStorage.setItem('theme', next);
            });

            function apply(theme) {
                htmlEl.setAttribute('data-bs-theme', theme);
                if (icon) {
                    icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
                }
            }
        })();
    </script>

    @yield('scripts')

</body>

</html>

