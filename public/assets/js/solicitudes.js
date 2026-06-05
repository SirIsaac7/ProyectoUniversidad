document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('solicitudes-success-message');
    const errorMessageElement = document.getElementById('solicitudes-error-message');
    const clienteSelect = document.querySelector('.js-cliente-select');
    const proveedorSelect = document.querySelector('.js-perfil-proveedor-select');
    const especialidadSelect = document.querySelector('.js-especialidad-select');
    const clienteFeedback = document.querySelector('.js-cliente-feedback');
    const proveedorFeedback = document.querySelector('.js-perfil-proveedor-feedback');
    const especialidadFeedback = document.querySelector('.js-especialidad-feedback');
    const tipoAtencionSelect = document.querySelector('.js-tipo-atencion');
    const tipoAtencionHelp = document.querySelector('.js-tipo-atencion-help');
    const estadoSelect = document.querySelector('.js-estado-solicitud');
    const estadoHelp = document.querySelector('.js-estado-solicitud-help');
    const motivoCancelacion = document.querySelector('.js-motivo-cancelacion');

    let especialidadChoices = null;

    const createChoices = function (element, placeholder, searchPlaceholder, noChoicesText) {
        if (!element || typeof Choices === 'undefined') {
            return null;
        }

        return new Choices(element, {
            searchEnabled: true,
            searchPlaceholderValue: searchPlaceholder,
            placeholder: true,
            placeholderValue: placeholder,
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: noChoicesText
        });
    };

    const updateSelectValidation = function (select, feedback) {
        if (!select || !feedback) {
            return;
        }

        const shouldShowError = select.closest('form')?.classList.contains('was-validated')
            && !select.checkValidity();

        feedback.classList.toggle('d-block', shouldShowError);
        select.classList.toggle('is-invalid', shouldShowError);
    };

    const especialidadOptions = especialidadSelect
        ? Array.from(especialidadSelect.options).map(function (option) {
            return {
                value: option.value,
                label: option.textContent,
                disabled: option.disabled,
                perfiles: (option.dataset.perfiles || '')
                    .split(',')
                    .map(function (value) {
                        return value.trim();
                    })
                    .filter(Boolean)
            };
        })
        : [];

    const rebuildEspecialidadSelect = function () {
        if (!especialidadSelect) {
            return;
        }

        const selectedValue = especialidadSelect.dataset.selectedValue || '';
        const perfilProveedorId = proveedorSelect?.value || '';

        const filteredOptions = especialidadOptions.filter(function (option, index) {
            if (index === 0 || option.value === '') {
                return true;
            }

            if (!perfilProveedorId) {
                return true;
            }

            return option.perfiles.includes(perfilProveedorId);
        });

        if (especialidadChoices) {
            especialidadChoices.destroy();
        }

        especialidadSelect.innerHTML = '';

        filteredOptions.forEach(function (option, index) {
            const optionElement = document.createElement('option');
            optionElement.value = option.value;
            optionElement.textContent = option.label;
            optionElement.disabled = option.disabled;

            if (option.perfiles.length > 0) {
                optionElement.dataset.perfiles = option.perfiles.join(',');
            }

            if (index === 0) {
                optionElement.selected = !selectedValue;
            }

            if (selectedValue && option.value === selectedValue) {
                optionElement.selected = true;
            }

            especialidadSelect.appendChild(optionElement);
        });

        if (
            selectedValue
            && ! Array.from(especialidadSelect.options).some(function (option) {
                return option.value === selectedValue;
            })
        ) {
            especialidadSelect.value = '';
            especialidadSelect.dataset.selectedValue = '';
        }

        especialidadChoices = createChoices(
            especialidadSelect,
            'Selecciona una especialidad',
            'Buscar especialidad',
            'No hay especialidades disponibles'
        );
    };

    const updateTipoAtencionHelp = function () {
        if (!tipoAtencionSelect || !tipoAtencionHelp) {
            return;
        }

        const messages = {
            mixto: 'La solicitud puede resolverse combinando visita, atencion en local o apoyo remoto.',
            domicilio: 'El proveedor debe atender en la direccion registrada para esta solicitud.',
            local: 'La atencion se realizara en el local o taller del proveedor.',
            remoto: 'La solicitud se resuelve a distancia, sin desplazamiento fisico.'
        };

        tipoAtencionHelp.textContent = messages[tipoAtencionSelect.value] || '';
    };

    const updateEstadoHelp = function () {
        if (!estadoSelect || !estadoHelp) {
            return;
        }

        const messages = {
            pendiente: 'La solicitud queda registrada y esperando gestion.',
            aceptada: 'La solicitud fue aceptada y puede avanzar a cita.',
            rechazada: 'La solicitud queda rechazada y ya no avanza.',
            cancelada: 'La solicitud queda cancelada y requiere un motivo.',
            en_proceso: 'La solicitud ya se encuentra en ejecucion.',
            finalizada: 'La solicitud ya termino su ciclo de atencion.'
        };

        estadoHelp.textContent = messages[estadoSelect.value] || '';

        if (motivoCancelacion) {
            const isCancelled = estadoSelect.value === 'cancelada';

            motivoCancelacion.disabled = !isCancelled;
            motivoCancelacion.required = isCancelled;

            if (!isCancelled) {
                motivoCancelacion.classList.remove('is-invalid');
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
            updateSelectValidation(clienteSelect, clienteFeedback);
            updateSelectValidation(proveedorSelect, proveedorFeedback);
            updateSelectValidation(especialidadSelect, especialidadFeedback);
        });
    });

    if (clienteSelect) {
        createChoices(
            clienteSelect,
            'Selecciona un cliente',
            'Buscar cliente',
            'No hay clientes disponibles'
        );

        clienteSelect.addEventListener('change', function () {
            updateSelectValidation(clienteSelect, clienteFeedback);
        });
    }

    if (proveedorSelect) {
        createChoices(
            proveedorSelect,
            'Selecciona un proveedor',
            'Buscar proveedor',
            'No hay proveedores disponibles'
        );

        proveedorSelect.addEventListener('change', function () {
            especialidadSelect.dataset.selectedValue = '';
            rebuildEspecialidadSelect();
            updateSelectValidation(proveedorSelect, proveedorFeedback);
            updateSelectValidation(especialidadSelect, especialidadFeedback);
        });
    }

    if (especialidadSelect) {
        rebuildEspecialidadSelect();

        especialidadSelect.addEventListener('change', function () {
            especialidadSelect.dataset.selectedValue = especialidadSelect.value;
            updateSelectValidation(especialidadSelect, especialidadFeedback);
        });
    }

    tipoAtencionSelect?.addEventListener('change', updateTipoAtencionHelp);
    estadoSelect?.addEventListener('change', updateEstadoHelp);
    updateTipoAtencionHelp();
    updateEstadoHelp();

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

    const clienteItems = Array.from(document.querySelectorAll('.js-solicitud-cliente-item'));
    const clienteSearch = document.querySelector('.js-solicitud-cliente-search');
    const clienteEstado = document.querySelector('.js-solicitud-cliente-estado');
    const clienteOrden = document.querySelector('.js-solicitud-cliente-orden');
    const clienteEmpty = document.querySelector('.js-solicitud-cliente-empty');
    const clienteList = document.querySelector('.solicitudes-client-list');

    const normalizeText = function (value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    };

    const applyClienteFilters = function () {
        if (!clienteItems.length) {
            return;
        }

        const searchValue = normalizeText(clienteSearch?.value);
        const estadoValue = clienteEstado?.value || '';
        let visibles = 0;

        clienteItems.forEach(function (item) {
            const matchesSearch = !searchValue || normalizeText(item.dataset.search).includes(searchValue);
            const matchesEstado = !estadoValue || item.dataset.estado === estadoValue;
            const visible = matchesSearch && matchesEstado;

            item.classList.toggle('d-none', !visible);

            if (visible) {
                visibles += 1;
            }
        });

        clienteEmpty?.classList.toggle('d-none', visibles > 0);
    };

    const applyClienteOrder = function () {
        if (!clienteList || !clienteItems.length) {
            return;
        }

        const orden = clienteOrden?.value || 'recientes';
        const sortedItems = [...clienteItems].sort(function (a, b) {
            const dateA = Number(a.dataset.created || 0);
            const dateB = Number(b.dataset.created || 0);

            return orden === 'antiguas' ? dateA - dateB : dateB - dateA;
        });

        sortedItems.forEach(function (item) {
            clienteList.appendChild(item);
        });

        if (clienteEmpty) {
            clienteList.appendChild(clienteEmpty);
        }
    };

    clienteSearch?.addEventListener('input', applyClienteFilters);
    clienteEstado?.addEventListener('change', applyClienteFilters);
    clienteOrden?.addEventListener('change', function () {
        applyClienteOrder();
        applyClienteFilters();
    });

    document.querySelector('.js-solicitud-detail-button')?.closest('.js-solicitud-cliente-item')?.classList.add('is-selected');

    const invalidPanel = document.querySelector('.solicitud-side-panel .is-invalid')?.closest('.solicitud-side-panel');

    if (invalidPanel) {
        document.querySelectorAll('.solicitud-side-panel').forEach(function (panel) {
            panel.classList.add('d-none');
        });

        invalidPanel.classList.remove('d-none');
    }
});

document.addEventListener('click', function (event) {
    const detailButton = event.target.closest('.js-solicitud-detail-button');

    if (detailButton) {
        document.querySelectorAll('.solicitud-side-panel').forEach(function (panel) {
            panel.classList.add('d-none');
        });

        document.getElementById('solicitudDetailPanel')?.classList.remove('d-none');

        document.querySelectorAll('.js-solicitud-cliente-item').forEach(function (item) {
            item.classList.remove('is-selected');
        });

        detailButton.closest('.js-solicitud-cliente-item')?.classList.add('is-selected');

        const panel = document.querySelector('.solicitud-detail-panel');

        if (!panel) {
            return;
        }

        const status = panel.querySelector('.js-detail-status');

        panel.querySelector('.js-detail-title').textContent = `Solicitud #${detailButton.dataset.id}`;
        panel.querySelector('.js-detail-service').textContent = detailButton.dataset.titulo || '-';
        panel.querySelector('.js-detail-description').textContent = detailButton.dataset.descripcion || '-';
        panel.querySelector('.js-detail-category').textContent = `${detailButton.dataset.rubro || '-'} - ${detailButton.dataset.tipoServicio || '-'}`;
        panel.querySelector('.js-detail-specialty').textContent = detailButton.dataset.especialidad || '-';
        panel.querySelector('.js-detail-provider').textContent = detailButton.dataset.proveedor || '-';
        panel.querySelector('.js-detail-date').textContent = `${detailButton.dataset.fecha || 'Sin fecha'} · ${detailButton.dataset.hora || 'Sin hora'}`;
        panel.querySelector('.js-detail-location').textContent = `${detailButton.dataset.zona || 'Sin zona'} · ${detailButton.dataset.direccion || 'Sin direccion'}`;
        panel.querySelector('.js-detail-attention').textContent = detailButton.dataset.tipoAtencion || '-';

        if (status) {
            status.className = `badge bg-${detailButton.dataset.estadoClass || 'secondary'}-subtle text-${detailButton.dataset.estadoClass || 'secondary'} js-detail-status`;
            status.innerHTML = `<i class="${detailButton.dataset.estadoIcon || 'ri-information-line'} me-1"></i>${detailButton.dataset.estado || '-'}`;
        }

        return;
    }

    const panelButton = event.target.closest('.js-solicitud-panel-toggle');

    if (panelButton) {
        const targetPanel = document.getElementById(panelButton.dataset.panelTarget || '');

        if (!targetPanel) {
            return;
        }

        const isOpen = !targetPanel.classList.contains('d-none');

        document.querySelectorAll('.solicitud-side-panel').forEach(function (panel) {
            panel.classList.add('d-none');
        });

        if (isOpen) {
            document.getElementById('solicitudDetailPanel')?.classList.remove('d-none');
        } else {
            targetPanel.classList.remove('d-none');
        }

        return;
    }

    if (event.target.closest('.js-solicitud-panel-cancel')) {
        document.querySelectorAll('.solicitud-side-panel').forEach(function (panel) {
            panel.classList.add('d-none');
        });

        document.getElementById('solicitudDetailPanel')?.classList.remove('d-none');
        return;
    }

    const form = event.target.closest('.js-confirm-submit');

    if (form) {
        event.preventDefault();

        if (typeof Swal === 'undefined') {
            form.submit();
            return;
        }

        Swal.fire({
            title: form.dataset.confirmTitle || 'Confirmar accion',
            text: form.dataset.confirmText || 'Se aplicara esta accion.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Volver',
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return;
    }

    const button = event.target.closest('.js-cancelar-solicitud-livewire');

    if (button) {
        const solicitudId = Number(button.dataset.solicitudId);
        const tituloSolicitud = button.dataset.tituloSolicitud || 'esta solicitud';
        const clienteNombre = button.dataset.clienteNombre || 'este cliente';

        if (!solicitudId) {
            return;
        }

        if (typeof Swal === 'undefined') {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarCancelacionSolicitud', { solicitudId: solicitudId });
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
                Livewire.dispatch('confirmarCancelacionSolicitud', { solicitudId: solicitudId });
            }
        });

        return;
    }

    return;
});

document.addEventListener('livewire:init', function () {
    Livewire.on('solicitud-cancelada', function (event) {
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
