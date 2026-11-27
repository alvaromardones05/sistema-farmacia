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
        border-top: 1px solid var(--border);
    }

    .inventario-modulo table thead th {
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }

    .inventario-modulo table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .inventario-modulo table tbody tr:hover {
        background-color: var(--surface-2);
    }

    .inventario-modulo table td,
    .inventario-modulo table th {
        vertical-align: middle;
    }


    /* Fila separadora de producto */

    .inventario-modulo tr.table-light td {
        background-color: var(--surface-3) !important;
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
        color: var(--text-2);
        margin-bottom: 0.3rem;
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
    color: var(--text-muted);
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
    box-shadow: var(--shadow-md);
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
    background-color: var(--warning-soft);
    border: 1px solid var(--warning);
    color: var(--bs-warning-text-emphasis);
}

.inventario-modulo .btn-operacion-ajuste:hover {
    background-color: var(--warning-soft);
    border-color: var(--warning);
    color: var(--bs-warning-text-emphasis);
}


/* =========================================================
   TABLA DE STOCK — más legible
   ========================================================= */

.inventario-modulo .tabla-stock thead th {
    background-color: var(--surface-2);
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
    color: var(--text);
}

.inventario-modulo .producto-codigo {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-top: 0.15rem;
}

/* Chip de lote */
.inventario-modulo .lote-chip {
    display: inline-flex;
    align-items: center;
    background: var(--surface-3);
    color: var(--text-2);
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
    color: var(--text-2);
}

.inventario-modulo .ubicacion-cell i {
    color: var(--text-muted);
    font-size: 0.85rem;
}

/* Cantidad */
.inventario-modulo .cantidad-valor {
    font-weight: 700;
    font-size: 1rem;
    color: var(--text);
}

.inventario-modulo .cantidad-unidad {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-left: 0.2rem;
}

/* Badges de estado más compactos */
.inventario-modulo .tabla-stock .badge {
    font-size: 0.72rem;
    padding: 0.35em 0.75em;
    border-radius: 0.45rem;
    min-width: 70px;
}


/* =====================================================
   EVITAR SALTOS DE DISEÑO (LAYOUT SHIFT)
   ====================================================== */

/* 1. Evitar que los iconos empujen el texto cuando carga la fuente */
.bi::before {
    display: inline-block;
    width: 1em;
    text-align: center;
}

/* 2. Fijar altura mínima de la tabla para que el paginador no salte */
.table-responsive {
    min-height: 450px; /* Ajusta este valor según el alto de tu tabla */
}

/* 3. Fijar altura de las tarjetas KPI para que no salten */
.tarjeta-resumen {
    min-height: 100px;
}

/* 4. Fijar altura de los tabs de ubicación */
.nav-tabs {
    min-height: 42px;
}

.tabla-stock {
    width: 100%;
}

.pagination svg {
    width: 16px !important;
    height: 16px !important;
}

.cantidad-valor,
.tabla-stock td {
    font-variant-numeric: tabular-nums;
}

/* 4. Animación de aparición suave en lugar de salto brusco */
.inventario-modulo {
    animation: fadeInInventario 0.3s ease-in-out;
}

@keyframes fadeInInventario {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}


</style>
