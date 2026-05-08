document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('especialidades-success-message');
    const errorMessageElement = document.getElementById('especialidades-error-message');
    const rubroTipoServicioSelect = document.querySelector('.js-rubro-tipo-servicio-select');
    const rubroTipoServicioFeedback = document.querySelector('.js-rubro-tipo-servicio-feedback');

    const updateRubroTipoServicioValidation = function () {
        if (!rubroTipoServicioSelect || !rubroTipoServicioFeedback) {
            return;
        }

        const shouldShowError = rubroTipoServicioSelect.closest('form')?.classList.contains('was-validated')
            && !rubroTipoServicioSelect.checkValidity();

        rubroTipoServicioFeedback.classList.toggle('d-block', shouldShowError);
        rubroTipoServicioSelect.classList.toggle('is-invalid', shouldShowError);
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
            updateRubroTipoServicioValidation();
        });
    });

    rubroTipoServicioSelect?.addEventListener('change', updateRubroTipoServicioValidation);

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

    if (rubroTipoServicioSelect && typeof Choices !== 'undefined') {
        new Choices(rubroTipoServicioSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar rubro o tipo de servicio',
            placeholder: true,
            placeholderValue: 'Selecciona un rubro y tipo de servicio',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay combinaciones disponibles'
        });
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-especialidad-livewire');

    if (!button) {
        return;
    }

    const especialidadId = Number(button.dataset.especialidadId);
    const nombre = button.dataset.especialidadNombre || 'esta especialidad';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!especialidadId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoEspecialidad', { especialidadId: especialidadId });
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
            Livewire.dispatch('confirmarCambioEstadoEspecialidad', { especialidadId: especialidadId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('especialidad-estado-cambiado', function (event) {
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
