<style>
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


    /* =========================================================
   TARJETAS DE RESUMEN — mejoradas
   ========================================================= */

.inventario-modulo .tarjeta-resumen {
    transition: transform 0.15s ease-in-out,
                box-shadow 0.15s ease-in-out;
}

.inventario-modulo .tarjeta-resumen .card-body {
    padding: 1rem 1.15rem;
}

.inventario-modulo .tarjeta-icon {
    width: 42px;
    height: 42px;
    border-radius: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.inventario-modulo .tarjeta-resumen .etiqueta {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    margin-bottom: 0.15rem;
}

.inventario-modulo .tarjeta-resumen .valor {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.1;
}

.inventario-modulo .tarjeta-clickable {
    cursor: pointer;
}

.inventario-modulo .tarjeta-clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
}


/* =========================================================
   BOTONES DE OPERACIÓN — más grandes y claros
   ========================================================= */

.inventario-modulo .btn-operacion {
    padding: 0.65rem 1.35rem;
    font-weight: 600;
    font-size: 0.92rem;
    border-radius: 0.6rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* Ajustar: tono ámbar suave para no competir con el azul principal */
.inventario-modulo .btn-operacion-ajuste {
    background-color: #fef3c7;
    border: 1px solid #fcd34d;
    color: #92400e;
}

.inventario-modulo .btn-operacion-ajuste:hover {
    background-color: #fde68a;
    border-color: #f59e0b;
    color: #78350f;
}


/* =========================================================
   TABLA DE STOCK — más legible
   ========================================================= */

.inventario-modulo .tabla-stock thead th {
    background-color: #f8fafc;
}

.inventario-modulo .tabla-stock tbody td {
    padding-top: 0.9rem;
    padding-bottom: 0.9rem;
}

/* Celda producto: nombre + código apilados */
.inventario-modulo .producto-cell {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.inventario-modulo .producto-nombre {
    font-weight: 600;
    color: #212529;
}

.inventario-modulo .producto-codigo {
    font-size: 0.78rem;
    color: #94a3b8;
    margin-top: 0.15rem;
}

/* Chip de lote */
.inventario-modulo .lote-chip {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    color: #475569;
    padding: 0.25rem 0.6rem;
    border-radius: 0.4rem;
    font-size: 0.78rem;
    font-weight: 500;
    font-family: 'SFMono-Regular', Consolas, monospace;
}

/* Ubicación con ícono */
.inventario-modulo .ubicacion-cell {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #495057;
}

.inventario-modulo .ubicacion-cell i {
    color: #94a3b8;
    font-size: 0.85rem;
}

/* Cantidad */
.inventario-modulo .cantidad-valor {
    font-weight: 700;
    font-size: 1rem;
    color: #212529;
}

.inventario-modulo .cantidad-unidad {
    font-size: 0.72rem;
    color: #94a3b8;
    margin-left: 0.2rem;
}

/* Badges de estado más compactos */
.inventario-modulo .tabla-stock .badge {
    font-size: 0.72rem;
    padding: 0.35em 0.75em;
    border-radius: 0.45rem;
    min-width: 70px;
}
</style>