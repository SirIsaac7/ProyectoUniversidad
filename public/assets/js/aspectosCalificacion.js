document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('aspectos-calificacion-success-message');

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    if (typeof Swal !== 'undefined' && successMessageElement?.dataset.message) {
        Swal.fire({
            title: 'Exito',
            text: successMessageElement.dataset.message,
            icon: 'success',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#0ab39c'
        });
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-aspecto-calificacion-livewire');

    if (!button) {
        return;
    }

    const aspectoCalificacionId = Number(button.dataset.aspectoCalificacionId);
    const nombre = button.dataset.aspectoCalificacionNombre || 'este aspecto';
    const accion = button.dataset.accion || 'cambiar estado';

    if (!aspectoCalificacionId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoAspectoCalificacion', { aspectoCalificacionId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se procedera a ${accion} ${nombre}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f06548',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed && typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoAspectoCalificacion', { aspectoCalificacionId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('aspecto-calificacion-estado-cambiado', function (event) {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: 'Exito',
            text: event.message,
            icon: 'success',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#0ab39c'
        });
    });
});
