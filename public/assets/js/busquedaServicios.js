let busquedaServiciosMap = null;
let busquedaServiciosMarker = null;
let busquedaServiciosCircle = null;
let busquedaServiciosLocationButton = null;

const busquedaServiciosMapStyle = [
    { featureType: 'poi', stylers: [{ visibility: 'off' }] },
    { featureType: 'transit', stylers: [{ visibility: 'off' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#6b7280' }] },
    { featureType: 'landscape', elementType: 'geometry', stylers: [{ color: '#f3f4f6' }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#dbeafe' }] },
    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#d1d5db' }] }
];

document.addEventListener('DOMContentLoaded', function () {
    const locationSwitch = document.querySelector('.js-busqueda-location-switch');

    locationSwitch?.addEventListener('change', function () {
        toggleBusquedaServiciosLocation(this.checked, true);
    });

    initProveedorBusquedaDelegatedEvents();

    initSolicitudWizards();
    initBusquedaScrollButtons();
    cargarZonasSolicitudLaPaz();

    if (window.busquedaSolicitudModalProveedorId && typeof bootstrap !== 'undefined') {
        const modalElement = document.querySelector(
            '[data-proveedor-id="' + window.busquedaSolicitudModalProveedorId + '"]'
        );

        if (modalElement) {
            const modalContent = modalElement.querySelector('[data-proveedor-profile-modal]');
            toggleProveedorRequestView(modalContent, true);
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    }

    toggleBusquedaServiciosLocation(Boolean(locationSwitch?.checked), false);

    if (isBusquedaServiciosGoogleMapsReady()) {
        window.initBusquedaServiciosMap();
    }
});

document.addEventListener('livewire:init', function () {
    if (typeof Livewire === 'undefined') {
        return;
    }

    try {
        Livewire.hook('morph.updated', function () {
            initSolicitudWizards();
            cargarZonasSolicitudLaPaz();
        });
    } catch (error) {
        document.addEventListener('livewire:update', function () {
            initSolicitudWizards();
            cargarZonasSolicitudLaPaz();
        });
    }
});

function initProveedorBusquedaDelegatedEvents() {
    if (document.body.dataset.busquedaProveedorDelegated === 'true') {
        return;
    }

    document.body.dataset.busquedaProveedorDelegated = 'true';

    document.addEventListener('click', function (event) {
        const profileButton = event.target.closest('[data-proveedor-modal-tab]');

        if (profileButton) {
            const modalSelector = profileButton.getAttribute('data-bs-target');
            const modalElement = modalSelector ? document.querySelector(modalSelector) : null;

            toggleProveedorRequestView(modalElement?.querySelector('[data-proveedor-profile-modal]'), false);
        }

        const requestOpenButton = event.target.closest('[data-proveedor-request-open]');

        if (requestOpenButton) {
            const modalContent = requestOpenButton.closest('[data-proveedor-profile-modal]');
            toggleProveedorRequestView(modalContent, true);
        }

        const requestBackButton = event.target.closest('[data-proveedor-request-back]');

        if (requestBackButton) {
            const modalContent = requestBackButton.closest('[data-proveedor-profile-modal]');
            toggleProveedorRequestView(modalContent, false);
        }
    });

    document.addEventListener('hide.bs.modal', function (event) {
        if (event.target?.classList?.contains('proveedor-busqueda-modal') && event.target.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });

    document.addEventListener('hidden.bs.modal', function (event) {
        if (!event.target?.classList?.contains('proveedor-busqueda-modal')) {
            return;
        }

        toggleProveedorRequestView(event.target.querySelector('[data-proveedor-profile-modal]'), false);
    });
}

function initBusquedaScrollButtons() {
    if (document.body.dataset.busquedaScrollDelegated === 'true') {
        return;
    }

    document.body.dataset.busquedaScrollDelegated = 'true';

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-busqueda-scroll-target]');

        if (button) {
            const target = document.querySelector(button.dataset.busquedaScrollTarget);

            if (!target) {
                return;
            }

            const direction = Number(button.dataset.busquedaScrollDirection || 1);
            const distance = target.clientWidth * 0.85;

            target.scrollBy({
                left: distance * direction,
                behavior: 'smooth'
            });
        }
    });
}

function toggleProveedorRequestView(modalContent, shouldShowRequest) {
    if (!modalContent) {
        return;
    }

    modalContent.classList.toggle('is-requesting', shouldShowRequest);
}

function initSolicitudWizards() {
    document.querySelectorAll('[data-solicitud-wizard]').forEach(function (form) {
        if (form.dataset.wizardInicializado === 'true') {
            return;
        }

        form.dataset.wizardInicializado = 'true';

        const state = { current: 0 };
        const nextButton = form.querySelector('[data-wizard-next]');
        const prevButton = form.querySelector('[data-wizard-prev]');
        const submitButton = form.querySelector('[data-wizard-submit]');
        const tipoAtencion = form.querySelector('[data-solicitud-tipo-atencion]');
        const especialidad = form.querySelector('[data-solicitud-especialidad]');
        const fechaSolicitada = form.querySelector('input[name="fecha_solicitada"]');

        if (fechaSolicitada && !fechaSolicitada.min) {
            fechaSolicitada.min = new Date().toISOString().slice(0, 10);
        }

        nextButton?.addEventListener('click', function () {
            if (!validarPasoSolicitud(form, state.current)) {
                return;
            }

            mostrarPasoSolicitud(form, state, state.current + 1);
        });

        prevButton?.addEventListener('click', function () {
            mostrarPasoSolicitud(form, state, state.current - 1);
        });

        form.querySelectorAll('[data-wizard-step-button]').forEach(function (button) {
            button.addEventListener('click', function () {
                const nextStep = Number(this.dataset.wizardStepButton);

                if (nextStep <= state.current || validarPasosAnterioresSolicitud(form, nextStep)) {
                    mostrarPasoSolicitud(form, state, nextStep);
                }
            });
        });

        tipoAtencion?.addEventListener('change', function () {
            actualizarAyudaTipoAtencion(form);
            actualizarEstadoCampoSolicitud(this);
        });

        especialidad?.addEventListener('change', function () {
            actualizarTituloSolicitud(form);
            actualizarEstadoCampoSolicitud(this);
        });

        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.addEventListener('input', function () {
                actualizarEstadoCampoSolicitud(field);
            });

            field.addEventListener('change', function () {
                actualizarEstadoCampoSolicitud(field);
            });
        });

        actualizarAyudaTipoAtencion(form);
        actualizarTituloSolicitud(form);
        if (!mostrarPrimerPasoConErrorSolicitud(form, state)) {
            mostrarPasoSolicitud(form, state, 0);
        }
    });
}

function mostrarPasoSolicitud(form, state, nextStep) {
    const panels = Array.from(form.querySelectorAll('[data-wizard-panel]'));
    const buttons = Array.from(form.querySelectorAll('[data-wizard-step-button]'));
    const maxStep = panels.length - 1;

    state.current = Math.max(0, Math.min(nextStep, maxStep));

    panels.forEach(function (panel, index) {
        panel.classList.toggle('is-active', index === state.current);
    });

    buttons.forEach(function (button, index) {
        button.classList.toggle('is-active', index === state.current);
        button.classList.toggle('is-complete', index < state.current);
    });

    form.querySelector('[data-wizard-prev]')?.toggleAttribute('disabled', state.current === 0);
    form.querySelector('[data-wizard-next]')?.classList.toggle('d-none', state.current === maxStep);
    form.querySelector('[data-wizard-submit]')?.classList.toggle('d-none', state.current !== maxStep);
}

function validarPasosAnterioresSolicitud(form, nextStep) {
    for (let step = 0; step < nextStep; step++) {
        if (!validarPasoSolicitud(form, step)) {
            return false;
        }
    }

    return true;
}

function validarPasoSolicitud(form, step) {
    const panel = form.querySelector('[data-wizard-panel="' + step + '"]');

    if (!panel) {
        return true;
    }

    const fields = Array.from(panel.querySelectorAll('input, select, textarea'))
        .filter(function (field) {
            return !field.disabled && field.offsetParent !== null;
        });

    fields.forEach(function (field) {
        actualizarEstadoCampoSolicitud(field, true);
    });

    const invalidField = fields.find(function (field) {
        return !field.checkValidity();
    });

    if (invalidField) {
        invalidField.reportValidity();
        return false;
    }

    return true;
}

function actualizarEstadoCampoSolicitud(field, forced = false) {
    if (!field || field.type === 'hidden') {
        return;
    }

    const shouldValidate = forced || field.classList.contains('is-invalid') || field.classList.contains('is-valid') || Boolean(field.value);
    const isRequired = field.required;
    const hasValue = field.value !== null && field.value.toString().trim() !== '';
    const shouldShowValid = isRequired && hasValue && field.checkValidity();
    const shouldShowInvalid = shouldValidate && !field.checkValidity();

    field.classList.toggle('is-valid', shouldShowValid);
    field.classList.toggle('is-invalid', shouldShowInvalid);
    actualizarEstadoChoicesSolicitud(field, shouldShowValid, shouldShowInvalid);
}

function actualizarEstadoChoicesSolicitud(field, isValid, isInvalid) {
    const choices = field.closest('.choices');

    if (!choices) {
        return;
    }

    choices.classList.toggle('is-valid', isValid);
    choices.classList.toggle('is-invalid', isInvalid);
}

function mostrarPrimerPasoConErrorSolicitud(form, state) {
    const invalidField = form.querySelector('.is-invalid');

    if (!invalidField) {
        return false;
    }

    const panel = invalidField.closest('[data-wizard-panel]');
    const step = Number(panel?.dataset.wizardPanel || 0);

    if (!Number.isNaN(step)) {
        mostrarPasoSolicitud(form, state, step);
        return true;
    }

    return false;
}

function actualizarTituloSolicitud(form) {
    const titleInput = form.querySelector('[data-solicitud-titulo]');
    const especialidad = form.querySelector('[data-solicitud-especialidad]');
    const selectedOption = especialidad?.selectedOptions?.[0];

    if (!titleInput || !selectedOption) {
        return;
    }

    titleInput.value = selectedOption.dataset.titulo || 'Solicitud de servicio';
}

function actualizarAyudaTipoAtencion(form) {
    const select = form.querySelector('[data-solicitud-tipo-atencion]');
    const help = form.querySelector('[data-solicitud-atencion-ayuda]');

    if (!select || !help) {
        return;
    }

    const mensajes = {
        mixto: 'El proveedor podra coordinar contigo si conviene atender en domicilio, local o de forma remota.',
        domicilio: 'El proveedor necesitara una zona y direccion clara para evaluar traslado y cobertura.',
        local: 'La atencion se realizara en el local o punto de trabajo del proveedor.',
        remoto: 'Ideal para soporte por internet, configuracion o diagnostico que no requiere visita presencial.'
    };

    help.innerHTML = '<i class="ri-information-line"></i><span>' + (mensajes[select.value] || mensajes.mixto) + '</span>';
}

function cargarZonasSolicitudLaPaz() {
    const selects = document.querySelectorAll('[data-zonas-lapaz-select]');

    if (!selects.length) {
        return;
    }

    fetch('/assets/js/maps/LaPaz/zonasGAMPL.geojson')
        .then(function (response) {
            if (!response.ok) {
                throw new Error('No se pudo cargar las zonas.');
            }

            return response.json();
        })
        .then(function (geojson) {
            const zonas = extraerZonasSolicitudLaPaz(geojson);

            selects.forEach(function (select) {
                poblarSelectZonaSolicitud(select, zonas);
            });
        })
        .catch(function () {
            selects.forEach(function (select) {
                poblarSelectZonaSolicitud(select, []);
            });
        });
}

function extraerZonasSolicitudLaPaz(geojson) {
    const zonas = new Map();

    (geojson?.features || []).forEach(function (feature) {
        const properties = feature.properties || {};
        const nombre = properties.zona || properties.zonaref;

        if (!nombre) {
            return;
        }

        zonas.set(normalizarZonaSolicitud(nombre), nombre);
    });

    return Array.from(zonas.values()).sort(function (a, b) {
        return a.localeCompare(b, 'es');
    });
}

function poblarSelectZonaSolicitud(select, zonas) {
    const selected = select.dataset.selected || select.value || '';
    const opciones = ['<option value="">Selecciona una zona</option>'];

    zonas.forEach(function (zona) {
        opciones.push('<option value="' + escaparHtmlSolicitud(zona) + '"' + (zona === selected ? ' selected' : '') + '>' + escaparHtmlSolicitud(zona) + '</option>');
    });

    if (selected && !zonas.includes(selected)) {
        opciones.push('<option value="' + escaparHtmlSolicitud(selected) + '" selected>' + escaparHtmlSolicitud(selected) + '</option>');
    }

    select.innerHTML = opciones.join('');

    if (typeof Choices !== 'undefined' && select.dataset.choicesInicializado !== 'true') {
        select.dataset.choicesInicializado = 'true';
        new Choices(select, {
            searchEnabled: true,
            shouldSort: false,
            itemSelectText: '',
            placeholder: true,
            searchPlaceholderValue: 'Buscar zona...',
            noResultsText: 'No se encontraron zonas',
            noChoicesText: 'No hay zonas disponibles'
        });
    }
}

function normalizarZonaSolicitud(texto) {
    return texto
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function escaparHtmlSolicitud(texto) {
    return texto
        .toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

window.initBusquedaServiciosMap = function () {
    const mapElement = document.getElementById('busquedaServiciosMap');

    if (!mapElement || !isBusquedaServiciosGoogleMapsReady() || busquedaServiciosMap) {
        return;
    }

    const lat = Number(document.getElementById('busquedaLatitud')?.value || mapElement.dataset.lat || -16.5);
    const lng = Number(document.getElementById('busquedaLongitud')?.value || mapElement.dataset.lng || -68.15);

    busquedaServiciosMap = new google.maps.Map(mapElement, {
        center: { lat: lat, lng: lng },
        zoom: 13,
        mapTypeControl: false,
        zoomControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        clickableIcons: false,
        gestureHandling: 'greedy',
        styles: busquedaServiciosMapStyle
    });

    setBusquedaServiciosPosition(lat, lng, false);
    addBusquedaServiciosMapControls();

    busquedaServiciosMap.addListener('click', function (event) {
        setBusquedaServiciosPosition(event.latLng.lat(), event.latLng.lng(), true);
    });

    if (document.querySelector('.js-busqueda-location-switch')?.checked && !document.getElementById('busquedaLatitud')?.value) {
        locateBusquedaServiciosCurrentPosition();
    }
};

function toggleBusquedaServiciosLocation(isEnabled, shouldLocate) {
    const mapWrapper = document.querySelector('.proveedores-cerca-map-wrapper');
    const locationLabel = document.querySelector('.js-busqueda-location-label');

    mapWrapper?.classList.toggle('is-location-enabled', isEnabled);

    if (locationLabel) {
        locationLabel.textContent = isEnabled ? 'Mi ubicacion actual' : 'La Paz';
    }

    if (!isEnabled) {
        return;
    }

    setTimeout(function () {
        if (busquedaServiciosMap) {
            google.maps.event.trigger(busquedaServiciosMap, 'resize');
            busquedaServiciosMap.panTo(getBusquedaServiciosCurrentLatLng());
        }
    }, 180);

    if (shouldLocate) {
        locateBusquedaServiciosCurrentPosition();
    }
}

function isBusquedaServiciosGoogleMapsReady() {
    return typeof google !== 'undefined' && google.maps;
}

function getBusquedaServiciosCurrentLatLng() {
    const mapElement = document.getElementById('busquedaServiciosMap');
    const lat = Number(document.getElementById('busquedaLatitud')?.value || mapElement?.dataset.lat || -16.5);
    const lng = Number(document.getElementById('busquedaLongitud')?.value || mapElement?.dataset.lng || -68.15);

    return { lat: lat, lng: lng };
}

function getBusquedaServiciosMarkerIcon() {
    return {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
        <svg width="42" height="52" viewBox="0 0 42 52" fill="none" xmlns="http://www.w3.org/2000/svg">
            <filter id="shadow" x="0" y="0" width="42" height="52" filterUnits="userSpaceOnUse">
                <feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#111827" flood-opacity="0.25"/>
            </filter>
            <path filter="url(#shadow)" d="M21 3C11.6 3 4 10.45 4 19.65C4 31.4 21 49 21 49C21 49 38 31.4 38 19.65C38 10.45 30.4 3 21 3Z" fill="#6f5cff"/>
            <circle cx="21" cy="20" r="8.5" fill="#FFFFFF"/>
            <circle cx="21" cy="20" r="4.5" fill="#28c7d8"/>
        </svg>
        `),
        scaledSize: new google.maps.Size(42, 52),
        anchor: new google.maps.Point(21, 49)
    };
}

function addBusquedaServiciosMapControls() {
    if (!busquedaServiciosMap || busquedaServiciosLocationButton) {
        return;
    }

    const control = document.createElement('div');
    control.className = 'busqueda-map-controls';

    const locationButton = document.createElement('button');
    locationButton.type = 'button';
    locationButton.className = 'busqueda-map-control-btn';
    locationButton.title = 'Usar mi ubicacion actual';
    locationButton.innerHTML = '<i class="ri-focus-3-line"></i><span>Mi ubicacion</span>';

    const zoomInButton = document.createElement('button');
    zoomInButton.type = 'button';
    zoomInButton.className = 'busqueda-map-control-btn busqueda-map-control-icon';
    zoomInButton.title = 'Acercar';
    zoomInButton.innerHTML = '<i class="ri-add-line"></i>';

    const zoomOutButton = document.createElement('button');
    zoomOutButton.type = 'button';
    zoomOutButton.className = 'busqueda-map-control-btn busqueda-map-control-icon';
    zoomOutButton.title = 'Alejar';
    zoomOutButton.innerHTML = '<i class="ri-subtract-line"></i>';

    locationButton.addEventListener('click', locateBusquedaServiciosCurrentPosition);

    zoomInButton.addEventListener('click', function () {
        busquedaServiciosMap.setZoom(busquedaServiciosMap.getZoom() + 1);
    });

    zoomOutButton.addEventListener('click', function () {
        busquedaServiciosMap.setZoom(busquedaServiciosMap.getZoom() - 1);
    });

    control.appendChild(locationButton);
    control.appendChild(zoomInButton);
    control.appendChild(zoomOutButton);
    busquedaServiciosMap.controls[google.maps.ControlPosition.RIGHT_TOP].push(control);
    busquedaServiciosLocationButton = locationButton;
}

function locateBusquedaServiciosCurrentPosition() {
    if (!navigator.geolocation || !busquedaServiciosMap) {
        return;
    }

    busquedaServiciosLocationButton?.classList.add('is-loading');

    navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        setBusquedaServiciosPosition(lat, lng, true);
        busquedaServiciosMap.panTo({ lat: lat, lng: lng });
        busquedaServiciosMap.setZoom(15);
        busquedaServiciosLocationButton?.classList.remove('is-loading');
    }, function () {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Ubicacion no disponible',
                text: 'Activa el permiso de ubicacion del navegador o selecciona el punto manualmente en el mapa.',
                icon: 'info',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#405189'
            });
        }

        busquedaServiciosLocationButton?.classList.remove('is-loading');
    }, {
        enableHighAccuracy: true,
        timeout: 8000,
        maximumAge: 60000
    });
}

function setBusquedaServiciosPosition(lat, lng, updateInputs = false) {
    if (!busquedaServiciosMap || Number.isNaN(lat) || Number.isNaN(lng)) {
        return;
    }

    const latLng = { lat: lat, lng: lng };

    if (!busquedaServiciosMarker) {
        busquedaServiciosMarker = new google.maps.Marker({
            position: latLng,
            map: busquedaServiciosMap,
            draggable: true,
            title: 'Mi ubicacion de busqueda',
            icon: getBusquedaServiciosMarkerIcon()
        });

        busquedaServiciosMarker.addListener('dragend', function (event) {
            setBusquedaServiciosPosition(event.latLng.lat(), event.latLng.lng(), true);
        });
    } else {
        busquedaServiciosMarker.setPosition(latLng);
    }

    if (updateInputs) {
        document.getElementById('busquedaLatitud').value = lat.toFixed(7);
        document.getElementById('busquedaLongitud').value = lng.toFixed(7);
    }

    updateBusquedaServiciosCircle();
}

function updateBusquedaServiciosCircle() {
    if (!busquedaServiciosMap || !busquedaServiciosMarker) {
        return;
    }

    if (busquedaServiciosCircle) {
        busquedaServiciosCircle.setMap(null);
        busquedaServiciosCircle = null;
    }

    busquedaServiciosCircle = new google.maps.Circle({
        map: busquedaServiciosMap,
        center: busquedaServiciosMarker.getPosition(),
        radius: 1000,
        strokeColor: '#6f5cff',
        strokeOpacity: 0.9,
        strokeWeight: 2,
        fillColor: '#6f5cff',
        fillOpacity: 0.12
    });
}
