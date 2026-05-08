document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('perfiles-proveedores-success-message');
    const errorMessageElement = document.getElementById('perfiles-proveedores-error-message');
    const usuarioSelect = document.querySelector('.js-usuario-select');
    const usuarioFeedback = document.querySelector('.js-usuario-feedback');
    const estadoVerificacionSelect = document.getElementById('estado_verificacion');
    const motivoRechazoWrapper = document.querySelector('.js-motivo-rechazo-wrapper');
    const motivoRechazoTextarea = document.getElementById('motivo_rechazo');

    const updateUsuarioValidation = function () {
        if (!usuarioSelect || !usuarioFeedback) {
            return;
        }

        const shouldShowError = usuarioSelect.closest('form')?.classList.contains('was-validated')
            && !usuarioSelect.checkValidity();

        usuarioFeedback.classList.toggle('d-block', shouldShowError);
        usuarioSelect.classList.toggle('is-invalid', shouldShowError);
    };

    const updateMotivoRechazo = function () {
        if (!estadoVerificacionSelect || !motivoRechazoWrapper || !motivoRechazoTextarea) {
            return;
        }

        const esRechazado = estadoVerificacionSelect.value === 'rechazado';

        motivoRechazoWrapper.classList.toggle('d-none', !esRechazado);
        motivoRechazoTextarea.disabled = !esRechazado;
        motivoRechazoTextarea.required = esRechazado;

        if (!esRechazado) {
            motivoRechazoTextarea.value = '';
            motivoRechazoTextarea.classList.remove('is-invalid');
        }
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
            updateUsuarioValidation();
            updateMotivoRechazo();
        });
    });

    usuarioSelect?.addEventListener('change', updateUsuarioValidation);
    estadoVerificacionSelect?.addEventListener('change', updateMotivoRechazo);
    updateMotivoRechazo();

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

    if (usuarioSelect && typeof Choices !== 'undefined') {
        new Choices(usuarioSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar usuario proveedor',
            placeholder: true,
            placeholderValue: 'Selecciona un usuario proveedor',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay usuarios proveedores disponibles'
        });
    }

    if (typeof FilePond !== 'undefined') {
        const plugins = [];

        if (typeof FilePondPluginFileValidateType !== 'undefined') {
            plugins.push(FilePondPluginFileValidateType);
        }

        if (typeof FilePondPluginFileValidateSize !== 'undefined') {
            plugins.push(FilePondPluginFileValidateSize);
        }

        if (typeof FilePondPluginImageExifOrientation !== 'undefined') {
            plugins.push(FilePondPluginImageExifOrientation);
        }

        if (typeof FilePondPluginImagePreview !== 'undefined') {
            plugins.push(FilePondPluginImagePreview);
        }

        if (plugins.length > 0) {
            FilePond.registerPlugin(...plugins);
        }

        document.querySelectorAll('.filepond-input-multiple').forEach(function (input) {
            FilePond.create(input, {
                allowMultiple: false,
                allowReorder: false,
                allowImagePreview: true,
                storeAsFile: true,
                credits: false,
                acceptedFileTypes: ['image/png', 'image/jpeg', 'image/webp'],
                labelIdle: 'Arrastra tu imagen aqui o <span class="filepond--label-action">explora</span>',
                labelFileTypeNotAllowed: 'Archivo no permitido',
                fileValidateTypeLabelExpectedTypes: 'Solo se permiten imagenes JPG, PNG o WEBP',
                labelMaxFilesExceeded: 'Solo puedes subir un archivo',
                labelMaxFiles: 'Solo se permite un archivo',
                maxFileSize: '3MB',
                labelMaxFileSizeExceeded: 'El archivo es demasiado grande',
                labelMaxFileSize: 'El tamano maximo permitido es {filesize}',
                imagePreviewHeight: 180,
                beforeAddFile: function (item) {
                    const allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];

                    if (!allowedTypes.includes(item.fileType)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Archivo no permitido',
                                text: 'Solo se permiten imagenes JPG, PNG o WEBP.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#f06548'
                            });
                        }

                        return false;
                    }

                    return true;
                }
            });
        });
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-perfil-proveedor-livewire');

    if (!button) {
        return;
    }

    const perfilProveedorId = Number(button.dataset.perfilProveedorId);
    const nombre = button.dataset.perfilProveedorNombre || 'este proveedor';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!perfilProveedorId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoPerfilProveedor', { perfilProveedorId: perfilProveedorId });
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
            Livewire.dispatch('confirmarCambioEstadoPerfilProveedor', { perfilProveedorId: perfilProveedorId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('abrir-modal-detalle-proveedor', function () {
        const modalElement = document.getElementById('detalleProveedorModal');

        if (!modalElement || typeof bootstrap === 'undefined') {
            return;
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    });

    Livewire.on('perfil-proveedor-estado-cambiado', function (event) {
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
