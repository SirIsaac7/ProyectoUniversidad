document.addEventListener('DOMContentLoaded', function () {
    const formularioMasivo = document.getElementById('accionMasivaNotificaciones');
    const seleccionarTodas = document.querySelector('.js-seleccionar-todas-notificaciones');
    const casillas = Array.from(document.querySelectorAll('.js-notificacion-checkbox'));
    const contador = document.querySelector('.js-notificaciones-seleccionadas');
    const botonAplicar = document.querySelector('.js-aplicar-accion');
    const selectorAccion = document.querySelector('[name="accion"][form="accionMasivaNotificaciones"]');

    function actualizarSeleccion() {
        const seleccionadas = casillas.filter(function (casilla) { return casilla.checked; }).length;

        if (contador) {
            contador.textContent = seleccionadas;
        }

        if (botonAplicar) {
            botonAplicar.disabled = seleccionadas === 0 || !selectorAccion?.value;
        }

        if (seleccionarTodas) {
            seleccionarTodas.checked = casillas.length > 0 && seleccionadas === casillas.length;
            seleccionarTodas.indeterminate = seleccionadas > 0 && seleccionadas < casillas.length;
        }
    }

    seleccionarTodas?.addEventListener('change', function () {
        casillas.forEach(function (casilla) { casilla.checked = seleccionarTodas.checked; });
        actualizarSeleccion();
    });

    casillas.forEach(function (casilla) {
        casilla.addEventListener('change', actualizarSeleccion);
    });

    selectorAccion?.addEventListener('change', actualizarSeleccion);

    formularioMasivo?.addEventListener('submit', function (event) {
        const seleccionadas = casillas.filter(function (casilla) { return casilla.checked; }).length;

        if (!seleccionadas || !selectorAccion?.value) {
            event.preventDefault();
            return;
        }

        if (selectorAccion.value !== 'eliminar' || formularioMasivo.dataset.confirmado === '1') {
            return;
        }

        event.preventDefault();

        if (typeof Swal === 'undefined') {
            if (window.confirm('¿Deseas eliminar las notificaciones seleccionadas?')) {
                formularioMasivo.dataset.confirmado = '1';
                formularioMasivo.requestSubmit();
            }
            return;
        }

        Swal.fire({
            title: 'Eliminar notificaciones',
            text: 'Esta accion no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f06548',
        }).then(function (resultado) {
            if (resultado.isConfirmed) {
                formularioMasivo.dataset.confirmado = '1';
                formularioMasivo.requestSubmit();
            }
        });
    });

    document.querySelectorAll('.js-eliminar-notificacion').forEach(function (formulario) {
        formulario.addEventListener('submit', function (event) {
            if (formulario.dataset.confirmado === '1') {
                return;
            }

            event.preventDefault();

            const confirmar = typeof Swal === 'undefined'
                ? Promise.resolve({ isConfirmed: window.confirm('¿Deseas eliminar esta notificacion?') })
                : Swal.fire({
                    title: 'Eliminar notificacion',
                    text: 'Esta accion no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Si, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#f06548',
                });

            confirmar.then(function (resultado) {
                if (resultado.isConfirmed) {
                    formulario.dataset.confirmado = '1';
                    formulario.requestSubmit();
                }
            });
        });
    });

    window.addEventListener('notificacion:recibida', function () {
        document.querySelector('.js-centro-notificacion-nueva')?.classList.remove('d-none');
    });

    actualizarSeleccion();
});
