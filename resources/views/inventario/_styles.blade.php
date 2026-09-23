<style>
    /* =========================================================
       INVENTARIO
       ========================================================= */

    .inventario-modulo .card {
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .inventario-modulo .card-header {
        font-weight: 600;
        letter-spacing: 0.02em;
        border-bottom: none;
    }

    .inventario-modulo .card-footer {
        border-top: 1px solid #eef0f2;
    }


    /* =========================================================
       TABLAS
       ========================================================= */

    .inventario-modulo table thead th {
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
        white-space: nowrap;
    }

    .inventario-modulo table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .inventario-modulo table tbody tr:hover {
        background-color: #f8f9fc;
    }

    .inventario-modulo table td,
    .inventario-modulo table th {
        vertical-align: middle;
    }


    /* Fila separadora de producto */

    .inventario-modulo tr.table-light td {
        background-color: #f4f6f9 !important;
        font-size: 0.92rem;
    }


    /* =========================================================
       BADGES
       ========================================================= */

    .inventario-modulo .badge {
        font-weight: 500;
        padding: 0.42em 0.7em;
        border-radius: 0.5rem;
    }


    /* =========================================================
       BOTONES
       ========================================================= */

    .inventario-modulo .accesos-rapidos .btn {
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .inventario-modulo .btn-sm {
        border-radius: 0.5rem;
    }


    /* =========================================================
       FORMULARIOS DE FILTRO
       ========================================================= */

    .inventario-modulo form .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #495057;
        margin-bottom: 0.3rem;
    }


    /* =========================================================
       MODALES DE INVENTARIO
       ========================================================= */

    /*
     * El modal debe ocupar toda la pantalla cuando está abierto.
     * No depende de .inventario-modulo porque normalmente los
     * modales pueden estar fuera de ese contenedor en el DOM.
     */

    .modal-custom {
        position: fixed;
        inset: 0;
        z-index: 1050;

        display: none;

        align-items: center;
        justify-content: center;

        padding: 1rem;

        background-color: rgba(0, 0, 0, 0.45);
    }

    /*
     * Esta clase es la que debe agregarse mediante JavaScript
     * cuando se abre el modal.
     */

    .modal-custom.is-open {
        display: flex;
    }


    /*
     * Contenedor visual del modal
     */

    .modal-dialog-custom {
        position: relative;

        width: 100%;
        max-width: 600px;
        max-height: calc(100vh - 2rem);

        margin: 0 auto;

        background: #ffffff;
        border-radius: 1rem;

        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.20);

        display: flex;
        flex-direction: column;

        overflow: hidden;
    }


    /*
     * Header / body / footer
     */

    .modal-dialog-custom .modal-header,
    .modal-dialog-custom .modal-body,
    .modal-dialog-custom .modal-footer {
        width: 100%;
        box-sizing: border-box;
    }

    .modal-dialog-custom .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        padding: 1rem 1.25rem;

        border-bottom: 1px solid #eef0f2;
    }

    .modal-dialog-custom .modal-body {
        padding: 1.25rem;

        overflow-y: auto;
    }

    .modal-dialog-custom .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;

        padding: 1rem 1.25rem;

        border-top: 1px solid #eef0f2;
    }


    /*
     * Botón de cerrar
     */

    .modal-cerrar {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 32px;
        height: 32px;

        padding: 0;

        border: none;
        border-radius: 0.5rem;

        background: transparent;

        color: #6c757d;

        font-size: 1.4rem;
        line-height: 1;

        cursor: pointer;

        transition:
            background-color 0.15s ease-in-out,
            color 0.15s ease-in-out;
    }

    .modal-cerrar:hover {
        background-color: #f1f3f5;
        color: #212529;
    }


    /* =========================================================
       ALERTAS
       ========================================================= */

    .inventario-modulo .alert {
        border-radius: 0.75rem;
        border: none;
        border-left: 4px solid currentColor;
    }


    /* =========================================================
       SUBTÍTULO
       ========================================================= */

    .inventario-modulo .texto-subtitulo {
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.05em;
    }


    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 576px) {

        .modal-custom {
            padding: 0.5rem;
        }

        .modal-dialog-custom {
            max-height: calc(100vh - 1rem);
            border-radius: 0.75rem;
        }

        .modal-dialog-custom .modal-header,
        .modal-dialog-custom .modal-body,
        .modal-dialog-custom .modal-footer {
            padding: 1rem;
        }

        .modal-dialog-custom .modal-footer {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .modal-dialog-custom .modal-footer .btn {
            width: 100%;
        }
    }
</style>