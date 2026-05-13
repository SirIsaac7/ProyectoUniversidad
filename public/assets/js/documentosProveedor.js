document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('documentos-proveedor-success-message');
    const errorMessageElement = document.getElementById('documentos-proveedor-error-message');
    const perfilProveedorSelect = document.querySelector('.js-perfil-proveedor-select');
    const tipoDocumentoSelect = document.querySelector('.js-tipo-documento-select');
    const perfilProveedorFeedback = document.querySelector('.js-perfil-proveedor-feedback');
    const tipoDocumentoFeedback = document.querySelector('.js-tipo-documento-feedback');
    const estadoRevisionSelect = document.querySelector('.js-estado-revision');
    const estadoRevisionHelp = document.querySelector('.js-estado-revision-help');
    const observacionRevision = document.querySelector('.js-observacion-revision');

    const updateSelectValidation = function (select, feedback) {
        if (!select || !feedback) {
            return;
        }

        const shouldShowError = select.closest('form')?.classList.contains('was-validated')
            && !select.checkValidity();

        feedback.classList.toggle('d-block', shouldShowError);
        select.classList.toggle('is-invalid', shouldShowError);
    };

    const updateEstadoRevisionHelp = function () {
        if (!estadoRevisionSelect || !estadoRevisionHelp) {
            return;
        }

        const messages = {
            pendiente: 'El documento queda esperando revision administrativa.',
            aprobado: 'El documento queda validado y se guardara la fecha de revision.',
            rechazado: 'El documento queda rechazado y debes indicar el motivo en observacion.'
        };

        estadoRevisionHelp.textContent = messages[estadoRevisionSelect.value] || '';

        if (observacionRevision) {
            const isRejected = estadoRevisionSelect.value === 'rechazado';

            observacionRevision.disabled = !isRejected;
            observacionRevision.required = isRejected;

            if (!isRejected) {
                observacionRevision.value = '';
                observacionRevision.classList.remove('is-invalid');
            }
        }
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
            updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);
            updateSelectValidation(tipoDocumentoSelect, tipoDocumentoFeedback);
        });
    });

    perfilProveedorSelect?.addEventListener('change', function () {
        updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);
    });

    tipoDocumentoSelect?.addEventListener('change', function () {
        updateSelectValidation(tipoDocumentoSelect, tipoDocumentoFeedback);
    });

    estadoRevisionSelect?.addEventListener('change', updateEstadoRevisionHelp);
    updateEstadoRevisionHelp();

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

    if (tipoDocumentoSelect && typeof Choices !== 'undefined') {
        new Choices(tipoDocumentoSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar tipo de documento',
            placeholder: true,
            placeholderValue: 'Selecciona un tipo de documento',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay tipos de documento disponibles'
        });
    }
});

document.addEventListener('click', function (event) {
    const deleteButton = event.target.closest('.js-delete-documento-proveedor-livewire');

    if (deleteButton) {
        const documentoProveedorId = Number(deleteButton.dataset.documentoProveedorId);
        const proveedor = deleteButton.dataset.proveedorNombre || 'este proveedor';
        const tipoDocumento = deleteButton.dataset.tipoDocumentoNombre || 'este documento';

        if (!documentoProveedorId) {
            return;
        }

        if (typeof Swal === 'undefined') {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarEliminarDocumentoProveedor', { documentoProveedorId: documentoProveedorId });
            }
            return;
        }

        Swal.fire({
            title: 'Estas seguro?',
            text: `Se eliminara ${tipoDocumento} de ${proveedor}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed && typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarEliminarDocumentoProveedor', { documentoProveedorId: documentoProveedorId });
            }
        });
    }

    const showButton = event.target.closest('.js-show-documento-proveedor');

    if (showButton) {
        const modal = document.getElementById('documentoProveedorModal');

        if (!modal) {
            return;
        }

        modal.querySelector('[data-documento-modal="proveedor"]').textContent = showButton.dataset.proveedor || 'Sin proveedor';
        modal.querySelector('[data-documento-modal="email"]').textContent = showButton.dataset.email || '';
        modal.querySelector('[data-documento-modal="tipo"]').textContent = showButton.dataset.tipo || 'Sin tipo';
        modal.querySelector('[data-documento-modal="observacion"]').textContent = showButton.dataset.observacion || 'Sin observacion';
        modal.querySelector('[data-documento-modal="fechaRevision"]').textContent = showButton.dataset.fechaRevision || 'Sin revision';
        modal.querySelector('[data-documento-modal="fechaCreacion"]').textContent = showButton.dataset.fechaCreacion || '';
        modal.querySelector('[data-documento-modal="archivo"]').setAttribute('href', showButton.dataset.archivo || '#');

        const revisionBadge = modal.querySelector('[data-documento-modal="revisionBadge"]');

        if (revisionBadge) {
            const revision = showButton.dataset.revision || 'Pendiente';
            const normalizedRevision = revision.toLowerCase();
            const badgeClasses = {
                aprobado: 'bg-success-subtle text-success',
                rechazado: 'bg-danger-subtle text-danger',
                pendiente: 'bg-warning-subtle text-warning'
            };

            revisionBadge.className = `badge ${badgeClasses[normalizedRevision] || badgeClasses.pendiente}`;
            revisionBadge.textContent = revision;
        }

        const previewImagen = modal.querySelector('[data-documento-modal="previewImagen"]');
        const previewArchivo = modal.querySelector('[data-documento-modal="previewArchivo"]');
        const isImage = showButton.dataset.esImagen === '1';

        if (previewImagen && previewArchivo) {
            previewImagen.classList.toggle('d-none', !isImage);
            previewArchivo.classList.toggle('d-none', isImage);

            if (isImage) {
                previewImagen.setAttribute('src', showButton.dataset.archivo || '');
            } else {
                previewImagen.setAttribute('src', '');
            }
        }
    }
});

document.addEventListener('livewire:init', function () {
    Livewire.on('documento-proveedor-eliminado', function (event) {
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
