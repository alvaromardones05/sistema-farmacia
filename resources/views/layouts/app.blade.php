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

    

</head>


<body>


    <!-- =====================================================
         BARRA SUPERIOR
    ====================================================== -->

    <nav class="navbar navbar-dark bg-primary navbar-farmacia">

        <div class="container-fluid">


            <!-- LOGO + NOMBRE -->

            <a
                href="{{ route('dashboard') }}"
                class="navbar-brand fw-bold"
            >

                <i class="bi bi-capsule"></i>

                Sistema de Farmacia

            </a>


            <!-- USUARIO + SALIR -->

            <div class="d-flex align-items-center text-white">

                <span class="me-4">

                    <i class="bi bi-person-circle"></i>

                    {{ Auth::user()->roles->pluck('name')->first() }}: {{ Auth::user()->name }}

                </span>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-outline-light btn-sm"
                    >

                        Salir

                    </button>

                </form>

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


            <h6 class="text-muted mb-3">
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


                <!-- VENTAS -->

                @hasanyrole('Técnico Farmacéutico|Químico Farmacéutico')

                    <a
                        href="{{ route('ventas.index') }}"
                        class="nav-link
                        {{ request()->routeIs('ventas.*') ? 'active' : '' }}"
                    >

                        <i class="bi bi-cart-check me-2"></i>

                        Ventas

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

    <style>

        body {
    background:
        radial-gradient(circle at 15% 20%, rgba(99, 102, 241, 0.18), transparent 30%),
        radial-gradient(circle at 85% 80%, rgba(139, 92, 246, 0.16), transparent 30%),
        linear-gradient(135deg, #111827 0%, #1e1b4b 50%, #312e81 100%);
    color: #1f2937;
}

.navbar-farmacia {
    height: 60px;
    background: rgba(255, 255, 255, 0.96);
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    box-shadow:
        0 4px 12px rgba(15, 23, 42, 0.06),
        0 1px 3px rgba(15, 23, 42, 0.04);
    position: relative;
    z-index: 10;
}

.sidebar {
    width: 220px;
    min-height: calc(100vh - 60px);

    background:
        linear-gradient(
            180deg,
            #ffffff 0%,
            #f8fafc 100%
        );

    border-right: 1px solid #e2e8f0;

    box-shadow:
        4px 0 20px rgba(15, 23, 42, 0.05);

    position: relative;
}


/* Pequeño detalle decorativo */
.sidebar::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;

    background: linear-gradient(
        180deg,
        #6366f1,
        #8b5cf6,
        #a855f7
    );

    opacity: 0.85;
}

.sidebar .nav-link {
    color: #64748b;

    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 5px;

    font-weight: 500;

    transition:
        background-color 0.25s ease,
        color 0.25s ease,
        box-shadow 0.25s ease,
        transform 0.25s ease;
}


/* Hover */

.sidebar .nav-link:hover {
    background:
        linear-gradient(
            90deg,
            #eef2ff 0%,
            #f5f3ff 100%
        );

    color: #4338ca;

    box-shadow:
        inset 3px 0 0 #6366f1,
        0 3px 10px rgba(99, 102, 241, 0.06);

    transform: translateX(2px);
}

/* Elemento activo */

.sidebar .nav-link.active {
    background:
        linear-gradient(
            135deg,
            #4f46e5 0%,
            #6366f1 55%,
            #7c3aed 100%
        );

    color: #ffffff;

    box-shadow:
        0 6px 16px rgba(79, 70, 229, 0.25),
        0 2px 5px rgba(79, 70, 229, 0.15);

    position: relative;
}


/* Brillo sutil del elemento activo */

.sidebar .nav-link.active::after {
    content: "";

    position: absolute;

    top: 0;
    right: 0;

    width: 35%;
    height: 100%;

    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.12)
    );

    pointer-events: none;
}


/* ================================
   CONTENIDO
   ================================ */

.contenido {
    flex: 1;
    padding: 30px;

    background:
        linear-gradient(
            135deg,
            rgba(248, 250, 252, 0.96),
            rgba(241, 245, 249, 0.96)
        );

    min-height: calc(100vh - 60px);
}

.lista-dashboard {
    background:
        linear-gradient(
            145deg,
            #ffffff 0%,
            #fdfdff 100%
        );

    border: 1px solid rgba(226, 232, 240, 0.9);

    border-radius: 6px;

    box-shadow:
        0 10px 30px rgba(15, 23, 42, 0.06),
        0 2px 8px rgba(15, 23, 42, 0.04);

    overflow: hidden;

    transition:
        box-shadow 0.3s ease,
        transform 0.3s ease;
}


/* Al pasar por encima de la tarjeta */

.lista-dashboard:hover {
    box-shadow:
        0 15px 35px rgba(15, 23, 42, 0.09),
        0 4px 12px rgba(15, 23, 42, 0.05);
}

.item-dashboard {
    padding: 12px 15px;

    border-bottom: 1px solid #edf0f5;

    color: #475569;

    transition:
        background-color 0.2s ease,
        padding-left 0.2s ease;
}


/* Hover de cada elemento */

.item-dashboard:hover {
    background:
        linear-gradient(
            90deg,
            #f8faff 0%,
            #ffffff 100%
        );

    color: #334155;

    padding-left: 19px;
}


/* Último elemento */

.item-dashboard:last-child {
    border-bottom: none;
}

    </style>


    <!-- =====================================================
         BOOTSTRAP JAVASCRIPT
    ====================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>