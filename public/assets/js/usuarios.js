document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('usuarios-success-message');
    const errorMessageElement = document.getElementById('usuarios-error-message');

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

    document.querySelectorAll('.form-toggle-usuario').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            const nombre = button?.dataset.usuarioNombre || 'este usuario';
            const accion = button?.dataset.accion || 'cambiar el estado de';

            if (typeof Swal === 'undefined') {
                form.submit();
                return;
            }

            Swal.fire({
                title: 'Estas seguro?',
                text: `Se procedera a ${accion} a ${nombre}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.js-usuario-avatar-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const file = input.files?.[0];
            const preview = input.closest('.mb-3, .mb-4')?.querySelector('.js-usuario-avatar-preview');

            if (!file) {
                return;
            }

            if (file.size > 8 * 1024 * 1024) {
                input.value = '';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Imagen demasiado grande',
                        text: 'La foto de perfil no debe superar los 8MB.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#f06548'
                    });
                }

                return;
            }

            if (!preview || !file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                if (preview.tagName === 'IMG') {
                    preview.src = event.target.result;
                    return;
                }

                preview.outerHTML = '<img src="' + event.target.result + '" alt="Vista previa" class="rounded-circle img-thumbnail js-usuario-avatar-preview" style="width: 4.5rem; height: 4.5rem; object-fit: cover;">';
            };

            reader.readAsDataURL(file);
        });
    });
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-usuario-livewire');

    if (!button) {
        return;
    }

    const usuarioId = Number(button.dataset.usuarioId);
    const nombre = button.dataset.usuarioNombre || 'este usuario';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!usuarioId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoUsuario', { usuarioId: usuarioId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se procedera a ${accion} a ${nombre}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f06548',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed && typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoUsuario', { usuarioId: usuarioId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('usuario-estado-cambiado', function (event) {
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

    Livewire.on('usuario-estado-error', function (event) {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: 'Error',
            text: event.message,
            icon: 'error',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#f06548'
        });
    });
});
