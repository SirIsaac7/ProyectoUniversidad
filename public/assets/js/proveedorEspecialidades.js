document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('proveedor-especialidades-success-message');
    const errorMessageElement = document.getElementById('proveedor-especialidades-error-message');
    const perfilProveedorSelect = document.querySelector('.js-perfil-proveedor-select');
    const especialidadSelect = document.querySelector('.js-especialidad-select');
    const perfilProveedorFeedback = document.querySelector('.js-perfil-proveedor-feedback');
    const especialidadFeedback = document.querySelector('.js-especialidad-feedback');

    const updateSelectValidation = function (select, feedback) {
        if (!select || !feedback) {
            return;
        }

        const shouldShowError = select.closest('form')?.classList.contains('was-validated')
            && !select.checkValidity();

        feedback.classList.toggle('d-block', shouldShowError);
        select.classList.toggle('is-invalid', shouldShowError);
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
            updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);
            updateSelectValidation(especialidadSelect, especialidadFeedback);
        });
    });

    perfilProveedorSelect?.addEventListener('change', function () {
        updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);
    });

    especialidadSelect?.addEventListener('change', function () {
        updateSelectValidation(especialidadSelect, especialidadFeedback);
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

    if (perfilProveedorSelect && typeof Choices !== 'undefined') {
        new Choices(perfilProveedorSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar proveedor',
            placeholder: true,
            placeholderValue: 'Selecciona un proveedor',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay proveedores disponibles'
        });
    }

    if (especialidadSelect && typeof Choices !== 'undefined') {
        new Choices(especialidadSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar especialidad',
            placeholder: true,
            placeholderValue: 'Selecciona una especialidad',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay especialidades disponibles'
        });
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-proveedor-especialidad-livewire');

    if (!button) {
        return;
    }

    const proveedorEspecialidadId = Number(button.dataset.proveedorEspecialidadId);
    const proveedor = button.dataset.proveedorNombre || 'este proveedor';
    const especialidad = button.dataset.especialidadNombre || 'esta especialidad';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!proveedorEspecialidadId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoProveedorEspecialidad', { proveedorEspecialidadId: proveedorEspecialidadId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se procedera a ${accion} ${especialidad} para ${proveedor}.`,
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
            Livewire.dispatch('confirmarCambioEstadoProveedorEspecialidad', { proveedorEspecialidadId: proveedorEspecialidadId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('proveedor-especialidad-estado-cambiado', function (event) {
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
