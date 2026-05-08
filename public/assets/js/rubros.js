document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('rubros-success-message');
    const errorMessageElement = document.getElementById('rubros-error-message');

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

    document.querySelectorAll('.form-toggle-rubro').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            const nombre = button.dataset.rubroNombre || 'este rubro';
            const accion = button.dataset.accion || 'cambiar el estado de';

            if (typeof Swal === 'undefined') {
                form.submit();
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
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

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
    const button = event.target.closest('.js-toggle-rubro-livewire');

    if (!button) {
        return;
    }

    const rubroId = Number(button.dataset.rubroId);
    const nombre = button.dataset.rubroNombre || 'este rubro';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!rubroId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoRubro', { rubroId: rubroId });
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
            Livewire.dispatch('confirmarCambioEstadoRubro', { rubroId: rubroId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('rubro-estado-cambiado', function (event) {
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
