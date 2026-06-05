document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('citas-success-message');

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
    const button = event.target.closest('.js-cancelar-cita-livewire');

    if (!button) {
        return;
    }

    const citaId = Number(button.dataset.citaId);
    const tituloSolicitud = button.dataset.solicitudTitulo || 'esta cita';
    const clienteNombre = button.dataset.clienteNombre || 'este cliente';

    if (!citaId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCancelacionCita', { citaId: citaId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se cancelara ${tituloSolicitud} asociada a ${clienteNombre}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, cancelar',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#f06548',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed && typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCancelacionCita', { citaId: citaId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('cita-cancelada', function (event) {
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
