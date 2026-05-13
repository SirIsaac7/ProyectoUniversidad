document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('tipos-documento-proveedor-success-message');
    const errorMessageElement = document.getElementById('tipos-documento-proveedor-error-message');

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

    if (typeof Swal !== 'undefined' && errorMessageElement?.dataset.message) {
        Swal.fire({
            title: 'Error',
            text: errorMessageElement.dataset.message,
            icon: 'error',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#f06548'
        });
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-tipo-documento-proveedor-livewire');

    if (!button) {
        return;
    }

    const tipoDocumentoProveedorId = Number(button.dataset.tipoDocumentoProveedorId);
    const nombre = button.dataset.tipoDocumentoProveedorNombre || 'este tipo de documento';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!tipoDocumentoProveedorId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoTipoDocumentoProveedor', { tipoDocumentoProveedorId: tipoDocumentoProveedorId });
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
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed && typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoTipoDocumentoProveedor', { tipoDocumentoProveedorId: tipoDocumentoProveedorId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('tipo-documento-proveedor-estado-cambiado', function (event) {
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
