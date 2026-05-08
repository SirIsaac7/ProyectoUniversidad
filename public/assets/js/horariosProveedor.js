document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('horarios-proveedor-success-message');
    const errorMessageElement = document.getElementById('horarios-proveedor-error-message');
    const perfilProveedorSelect = document.querySelector('.js-perfil-proveedor-select');
    const perfilProveedorFeedback = document.querySelector('.js-perfil-proveedor-feedback');
    const tipoAtencionSelect = document.getElementById('tipo_atencion');
    const disponibleSelect = document.getElementById('disponible');
    const tipoAtencionHelp = document.querySelector('.js-tipo-atencion-help');
    const disponibleHelp = document.querySelector('.js-disponible-help');

    const updatePerfilProveedorValidation = function () {
        if (!perfilProveedorSelect || !perfilProveedorFeedback) {
            return;
        }

        const shouldShowError = perfilProveedorSelect.closest('form')?.classList.contains('was-validated')
            && !perfilProveedorSelect.checkValidity();

        perfilProveedorFeedback.classList.toggle('d-block', shouldShowError);
        perfilProveedorSelect.classList.toggle('is-invalid', shouldShowError);
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
            updatePerfilProveedorValidation();
        });
    });

    perfilProveedorSelect?.addEventListener('change', updatePerfilProveedorValidation);

    const updateTipoAtencionHelp = function () {
        if (!tipoAtencionSelect || !tipoAtencionHelp) {
            return;
        }

        const mensajes = {
            mixto: 'El proveedor puede atender en domicilio, local o remoto segun el caso.',
            domicilio: 'El proveedor se desplaza hasta la direccion del cliente.',
            local: 'El cliente debe acudir al local o punto de atencion del proveedor.',
            remoto: 'La atencion se realiza por internet, llamada o soporte remoto.'
        };

        tipoAtencionHelp.textContent = mensajes[tipoAtencionSelect.value] || 'Selecciona como atendera el proveedor en este horario.';
    };

    const updateDisponibleHelp = function () {
        if (!disponibleHelp) {
            return;
        }

        const value = disponibleSelect?.value ?? disponibleHelp.dataset.fixed;

        disponibleHelp.textContent = value === '1'
            ? 'Este horario estara visible y podra usarse para atencion.'
            : 'Este horario queda registrado, pero no se usara mientras este desactivado.';
    };

    tipoAtencionSelect?.addEventListener('change', updateTipoAtencionHelp);
    disponibleSelect?.addEventListener('change', updateDisponibleHelp);
    updateTipoAtencionHelp();
    updateDisponibleHelp();

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
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-horario-proveedor-livewire');

    if (!button) {
        return;
    }

    const horarioProveedorId = Number(button.dataset.horarioProveedorId);
    const proveedor = button.dataset.proveedorNombre || 'este proveedor';
    const dia = button.dataset.dia || 'este dia';
    const horario = button.dataset.horario || 'este horario';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!horarioProveedorId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioDisponibilidadHorarioProveedor', { horarioProveedorId: horarioProveedorId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se procedera a ${accion} ${dia}, ${horario}, para ${proveedor}.`,
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
            Livewire.dispatch('confirmarCambioDisponibilidadHorarioProveedor', { horarioProveedorId: horarioProveedorId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('horario-proveedor-disponibilidad-cambiada', function (event) {
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
