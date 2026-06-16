document.addEventListener('DOMContentLoaded', function () {
    cargarNotificaciones();
    inicializarNotificacionesTiempoReal();
    registrarEventosNotificaciones();
});

function inicializarNotificacionesTiempoReal() {
    if (!window.usuarioAutenticadoId || typeof window.Echo === 'undefined' || typeof window.Pusher === 'undefined') {
        return;
    }

    const EchoConstructor = window.Echo;
    window.Pusher = window.Pusher;

    window.Echo = new EchoConstructor({
        broadcaster: 'pusher',
        key: window.reverbConfig?.key,
        wsHost: window.reverbConfig?.host || window.location.hostname,
        wsPort: Number(window.reverbConfig?.port || 8080),
        wssPort: Number(window.reverbConfig?.port || 8080),
        forceTLS: window.reverbConfig?.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        cluster: 'mt1',
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': obtenerTokenCsrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    });

    window.Echo.private(`App.Models.User.${window.usuarioAutenticadoId}`)
        .notification(function (notificacion) {
            mostrarToastNotificacion(notificacion);
            cargarNotificaciones();
        });
}

function registrarEventosNotificaciones() {
    document.querySelector('.js-notificaciones-lista')?.addEventListener('click', function (event) {
        const item = event.target.closest('.js-notificacion-item');

        if (!item) {
            return;
        }

        event.preventDefault();
        marcarNotificacionLeida(item.dataset.id, item.dataset.url);
    });

    document.querySelector('.js-notificaciones-leer-todas')?.addEventListener('click', function () {
        marcarTodasLasNotificacionesLeidas();
    });
}

function cargarNotificaciones() {
    if (!window.notificacionesRutas?.recientes) {
        return;
    }

    fetch(window.notificacionesRutas.recientes, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('No se pudieron cargar las notificaciones.');
            }

            return response.json();
        })
        .then(function (data) {
            actualizarContadorNotificaciones(data.total_no_leidas || 0);
            renderizarNotificaciones(data.notificaciones || []);
        })
        .catch(function () {});
}

function renderizarNotificaciones(notificaciones) {
    const lista = document.querySelector('.js-notificaciones-lista');

    if (!lista) {
        return;
    }

    if (!notificaciones.length) {
        lista.innerHTML = `
            <div class="text-center py-4 js-notificaciones-vacio">
                <div class="avatar-md mx-auto mb-3">
                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                        <i class="bx bx-bell-off fs-24"></i>
                    </span>
                </div>
                <h6 class="mb-1">Sin notificaciones</h6>
                <p class="text-muted mb-0 fs-12">Aqui veras los avisos importantes del sistema.</p>
            </div>
        `;
        return;
    }

    lista.innerHTML = notificaciones.map(function (notificacion) {
        const meta = obtenerMetaNotificacion(notificacion.tipo);
        const titulo = escaparHtml(notificacion.titulo || 'Notificacion');
        const mensaje = escaparHtml(notificacion.mensaje || '');
        const fecha = escaparHtml(notificacion.fecha || 'Ahora');
        const estado = notificacion.leida ? '' : '<span class="badge bg-danger-subtle text-danger ms-2">Nueva</span>';

        return `
            <a href="${notificacion.url || '#'}"
                class="text-reset notification-item d-block dropdown-item position-relative js-notificacion-item ${notificacion.leida ? '' : 'active'}"
                data-id="${notificacion.id}"
                data-url="${notificacion.url || ''}">
                <div class="d-flex">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title ${meta.fondo} ${meta.texto} rounded-circle fs-16">
                            <i class="${meta.icono}"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mt-0 mb-1 fs-13 lh-base">${titulo}${estado}</h6>
                        <p class="mb-1 fs-12 text-muted">${mensaje}</p>
                        <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                            <span><i class="mdi mdi-clock-outline"></i> ${fecha}</span>
                        </p>
                    </div>
                </div>
            </a>
        `;
    }).join('');
}

function actualizarContadorNotificaciones(total) {
    const contador = document.querySelector('.js-notificaciones-contador');
    const resumen = document.querySelector('.js-notificaciones-resumen');
    const botonLeerTodas = document.querySelector('.js-notificaciones-leer-todas');

    if (contador) {
        contador.textContent = total;
        contador.classList.toggle('d-none', Number(total) <= 0);
    }

    if (resumen) {
        resumen.textContent = Number(total) === 1 ? '1 nueva' : `${total} nuevas`;
    }

    if (botonLeerTodas) {
        botonLeerTodas.disabled = Number(total) <= 0;
    }
}

function marcarNotificacionLeida(id, url) {
    if (!id || !window.notificacionesRutas?.leer) {
        if (url) {
            window.location.href = url;
        }

        return;
    }

    fetch(window.notificacionesRutas.leer.replace('__ID__', id), {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCsrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('No se pudo marcar la notificacion.');
            }

            return response.json();
        })
        .then(function (data) {
            actualizarContadorNotificaciones(data.total_no_leidas || 0);

            if (url) {
                window.location.href = url;
                return;
            }

            cargarNotificaciones();
        })
        .catch(function () {
            if (url) {
                window.location.href = url;
            }
        });
}

function marcarTodasLasNotificacionesLeidas() {
    if (!window.notificacionesRutas?.leerTodas) {
        return;
    }

    fetch(window.notificacionesRutas.leerTodas, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': obtenerTokenCsrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('No se pudieron marcar las notificaciones.');
            }

            return response.json();
        })
        .then(function (data) {
            actualizarContadorNotificaciones(data.total_no_leidas || 0);
            cargarNotificaciones();
        })
        .catch(function () {});
}

function mostrarToastNotificacion(notificacion) {
    if (typeof Swal === 'undefined') {
        return;
    }

    const meta = obtenerMetaNotificacion(notificacion.tipo);

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: meta.swal,
        title: notificacion.titulo || 'Nueva notificacion',
        text: notificacion.mensaje || '',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
}

function obtenerMetaNotificacion(tipo) {
    const metas = {
        success: {
            fondo: 'bg-success-subtle',
            texto: 'text-success',
            icono: 'bx bx-check-circle',
            swal: 'success',
        },
        warning: {
            fondo: 'bg-warning-subtle',
            texto: 'text-warning',
            icono: 'bx bx-error-circle',
            swal: 'warning',
        },
        error: {
            fondo: 'bg-danger-subtle',
            texto: 'text-danger',
            icono: 'bx bx-x-circle',
            swal: 'error',
        },
        danger: {
            fondo: 'bg-danger-subtle',
            texto: 'text-danger',
            icono: 'bx bx-x-circle',
            swal: 'error',
        },
        info: {
            fondo: 'bg-info-subtle',
            texto: 'text-info',
            icono: 'bx bx-info-circle',
            swal: 'info',
        },
    };

    return metas[tipo] || metas.info;
}

function obtenerTokenCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function escaparHtml(valor) {
    return String(valor)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
