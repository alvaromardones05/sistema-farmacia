<div class="campana" id="campana-notificaciones">
    <button type="button" class="campana-boton" id="campana-boton" aria-label="Notificaciones" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <span class="campana-contador" id="campana-contador" hidden>0</span>
    </button>

    <div class="campana-panel" id="campana-panel" hidden>
        <div class="campana-cabecera">
            <strong>Notificaciones</strong>
            <button type="button" class="campana-leer-todas" id="campana-leer-todas">Marcar todas como leídas</button>
        </div>
        <div class="campana-lista" id="campana-lista"></div>
    </div>
</div>

<style>
    .campana { position: relative; display: inline-block; }
    .campana-boton {
        position: relative; background: transparent; border: 0; padding: .25rem .5rem;
        font-size: 1.25rem; color: inherit; cursor: pointer;
    }
    .campana-contador {
        position: absolute; top: -2px; right: -2px; min-width: 18px; height: 18px; padding: 0 5px;
        border-radius: 9px; background: #da3633; color: #fff; font-size: .7rem; line-height: 18px; text-align: center;
    }
    .campana-panel {
        position: absolute; right: 0; top: calc(100% + 8px); width: 340px; max-width: 92vw; z-index: 1050;
        background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #212529);
        border: 1px solid var(--bs-border-color, #dee2e6); border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
    }
    .campana-cabecera {
        display: flex; justify-content: space-between; align-items: center; gap: .5rem;
        padding: .6rem .8rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6); font-size: .85rem;
    }
    .campana-leer-todas {
        background: none; border: 0; padding: 0; font-size: .75rem; cursor: pointer;
        color: var(--bs-link-color, #0d6efd);
    }
    .campana-lista { max-height: 380px; overflow-y: auto; }
    .campana-item {
        display: flex; gap: .6rem; padding: .65rem .8rem; text-decoration: none; color: inherit;
        border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0, 0, 0, .08));
    }
    .campana-item:hover { background: var(--bs-tertiary-bg, #f8f9fa); color: inherit; }
    .campana-item.no-leida { background: var(--bs-primary-bg-subtle, #e7f1ff); }
    .campana-item .titulo { font-weight: 600; font-size: .85rem; }
    .campana-item .mensaje { font-size: .8rem; opacity: .85; }
    .campana-item .fecha { font-size: .7rem; opacity: .6; }
    .campana-vacia { padding: 1.2rem; text-align: center; opacity: .6; font-size: .85rem; }
</style>

<script>
    (function () {
        var urlLista = @json(route('notificaciones.index'));
        var urlLeerTodas = @json(route('notificaciones.leer-todas'));
        var csrf = @json(csrf_token());
        var INTERVALO = 30000; // 30 segundos

        var boton = document.getElementById('campana-boton');
        var panel = document.getElementById('campana-panel');
        var contador = document.getElementById('campana-contador');
        var lista = document.getElementById('campana-lista');
        var leerTodas = document.getElementById('campana-leer-todas');

        function pintar(datos) {
            contador.textContent = datos.no_leidas > 99 ? '99+' : datos.no_leidas;
            contador.hidden = datos.no_leidas < 1;

            lista.textContent = '';
            if (!datos.items.length) {
                var vacia = document.createElement('div');
                vacia.className = 'campana-vacia';
                vacia.textContent = 'No tienes notificaciones.';
                lista.appendChild(vacia);
                return;
            }

            datos.items.forEach(function (n) {
                var enlace = document.createElement('a');
                enlace.href = n.url;
                enlace.className = 'campana-item' + (n.leida ? '' : ' no-leida');

                var icono = document.createElement('i');
                icono.className = 'bi ' + n.icono;

                var cuerpo = document.createElement('div');
                ['titulo', 'mensaje', 'fecha'].forEach(function (campo) {
                    var linea = document.createElement('div');
                    linea.className = campo;
                    linea.textContent = n[campo];
                    cuerpo.appendChild(linea);
                });

                enlace.appendChild(icono);
                enlace.appendChild(cuerpo);
                lista.appendChild(enlace);
            });
        }

        function cargar() {
            if (document.hidden) { return; }
            fetch(urlLista, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (respuesta) { return respuesta.ok ? respuesta.json() : null; })
                .then(function (datos) { if (datos) { pintar(datos); } })
                .catch(function () { /* sin conexión: reintenta en el próximo ciclo */ });
        }

        boton.addEventListener('click', function (evento) {
            evento.stopPropagation();
            var abrir = panel.hidden;
            panel.hidden = !abrir;
            boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            if (abrir) { cargar(); }
        });

        document.addEventListener('click', function (evento) {
            if (!panel.hidden && !panel.contains(evento.target)) {
                panel.hidden = true;
                boton.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') { panel.hidden = true; }
        });

        leerTodas.addEventListener('click', function () {
            fetch(urlLeerTodas, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(cargar);
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) { cargar(); }
        });

        cargar();
        setInterval(cargar, INTERVALO);
    })();
</script>
