document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('ubicaciones-proveedor-success-message');
    const errorMessageElement = document.getElementById('ubicaciones-proveedor-error-message');
    const perfilProveedorSelect = document.querySelector('.js-perfil-proveedor-select');
    const perfilProveedorFeedback = document.querySelector('.js-perfil-proveedor-feedback');

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

    window.initUbicacionesProveedorMaps();
    updateRadioHelp();

    document.getElementById('radio_cobertura_slider')?.addEventListener('input', function () {
        updateRadioHelp();
        updateFormMapCircle();
    });

    document.getElementById('latitud')?.addEventListener('input', updateFormMapFromInputs);
    document.getElementById('longitud')?.addEventListener('input', updateFormMapFromInputs);
});

let ubicacionFormMap = null;
let ubicacionFormMarker = null;
let ubicacionFormCircle = null;
let ubicacionesIndexMap = null;
let ubicacionesIndexInfoWindow = null;
let ubicacionFormLocationButton = null;

const ubicacionProveedorMapStyle = [
    { featureType: 'poi', stylers: [{ visibility: 'off' }] },
    { featureType: 'transit', stylers: [{ visibility: 'off' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#6b7280' }] },
    { featureType: 'landscape', elementType: 'geometry', stylers: [{ color: '#f3f4f6' }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#dbeafe' }] },
    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#d1d5db' }] }
];

function getUbicacionProveedorMarkerIcon() {
    return {
        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
        <svg width="42" height="52" viewBox="0 0 42 52" fill="none" xmlns="http://www.w3.org/2000/svg">
            <filter id="shadow" x="0" y="0" width="42" height="52" filterUnits="userSpaceOnUse">
                <feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#111827" flood-opacity="0.25"/>
            </filter>
            <path filter="url(#shadow)" d="M21 3C11.6 3 4 10.45 4 19.65C4 31.4 21 49 21 49C21 49 38 31.4 38 19.65C38 10.45 30.4 3 21 3Z" fill="#E53935"/>
            <circle cx="21" cy="20" r="8.5" fill="#FFFFFF"/>
            <circle cx="21" cy="20" r="4.5" fill="#111827"/>
        </svg>
        `),
        scaledSize: new google.maps.Size(42, 52),
        anchor: new google.maps.Point(21, 49)
    };
}

window.initUbicacionesProveedorMaps = function () {
    initUbicacionFormMap();
    initUbicacionesIndexMap();
};

function isGoogleMapsReady() {
    return typeof google !== 'undefined' && google.maps;
}

function initUbicacionFormMap() {
    const mapElement = document.getElementById('ubicacionProveedorMap');

    if (!mapElement || !isGoogleMapsReady() || ubicacionFormMap) {
        return;
    }

    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');
    const defaultLat = Number(latInput?.value || mapElement.dataset.lat || -16.5);
    const defaultLng = Number(lngInput?.value || mapElement.dataset.lng || -68.15);

    ubicacionFormMap = new google.maps.Map(mapElement, {
        center: { lat: defaultLat, lng: defaultLng },
        zoom: 13,
        mapTypeControl: false,
        zoomControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        clickableIcons: false,
        gestureHandling: 'greedy',
        styles: ubicacionProveedorMapStyle
    });
    setFormMapPosition(defaultLat, defaultLng, true);
    addUbicacionFormControls();

    ubicacionFormMap.addListener('click', function (event) {
        setFormMapPosition(event.latLng.lat(), event.latLng.lng(), true);
    });

    if (mapElement.dataset.useCurrentLocation === 'true') {
        locateCurrentPosition(true);
    }
}

function addUbicacionFormControls() {
    if (!ubicacionFormMap || ubicacionFormLocationButton) {
        return;
    }

    const control = document.createElement('div');
    control.className = 'ubicacion-map-controls';

    const locationButton = document.createElement('button');
    locationButton.type = 'button';
    locationButton.className = 'ubicacion-map-control-btn';
    locationButton.title = 'Usar mi ubicacion actual';
    locationButton.innerHTML = '<i class="ri-focus-3-line"></i><span>Mi ubicacion</span>';

    const zoomInButton = document.createElement('button');
    zoomInButton.type = 'button';
    zoomInButton.className = 'ubicacion-map-control-btn ubicacion-map-control-icon';
    zoomInButton.title = 'Acercar';
    zoomInButton.innerHTML = '<i class="ri-add-line"></i>';

    const zoomOutButton = document.createElement('button');
    zoomOutButton.type = 'button';
    zoomOutButton.className = 'ubicacion-map-control-btn ubicacion-map-control-icon';
    zoomOutButton.title = 'Alejar';
    zoomOutButton.innerHTML = '<i class="ri-subtract-line"></i>';

    locationButton.addEventListener('click', function () {
        locateCurrentPosition(false);
    });

    zoomInButton.addEventListener('click', function () {
        ubicacionFormMap.setZoom(ubicacionFormMap.getZoom() + 1);
    });

    zoomOutButton.addEventListener('click', function () {
        ubicacionFormMap.setZoom(ubicacionFormMap.getZoom() - 1);
    });

    control.appendChild(locationButton);
    control.appendChild(zoomInButton);
    control.appendChild(zoomOutButton);
    ubicacionFormMap.controls[google.maps.ControlPosition.RIGHT_TOP].push(control);
    ubicacionFormLocationButton = locationButton;
}

function locateCurrentPosition(isAutomatic = false) {
    if (!navigator.geolocation || !ubicacionFormMap) {
        return;
    }

    if (ubicacionFormLocationButton) {
        ubicacionFormLocationButton.classList.add('is-loading');
    }

    navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        setFormMapPosition(lat, lng, true);
        ubicacionFormMap.panTo({ lat: lat, lng: lng });
        ubicacionFormMap.setZoom(15);

        if (ubicacionFormLocationButton) {
            ubicacionFormLocationButton.classList.remove('is-loading');
        }
    }, function () {
        if (!isAutomatic && typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Ubicacion no disponible',
                text: 'Activa el permiso de ubicacion del navegador o selecciona el punto manualmente en el mapa.',
                icon: 'info',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#405189'
            });
        }

        if (ubicacionFormLocationButton) {
            ubicacionFormLocationButton.classList.remove('is-loading');
        }
    }, {
        enableHighAccuracy: true,
        timeout: 8000,
        maximumAge: 60000
    });
}

function setFormMapPosition(lat, lng, updateInputs = false) {
    if (!ubicacionFormMap || Number.isNaN(lat) || Number.isNaN(lng)) {
        return;
    }

    const latLng = { lat: lat, lng: lng };

    if (!ubicacionFormMarker) {
        ubicacionFormMarker = new google.maps.Marker({
            position: latLng,
            map: ubicacionFormMap,
            draggable: true,
            title: 'Ubicacion del proveedor',
            icon: getUbicacionProveedorMarkerIcon()
        });

        ubicacionFormMarker.addListener('dragend', function (event) {
            setFormMapPosition(event.latLng.lat(), event.latLng.lng(), true);
        });
    } else {
        ubicacionFormMarker.setPosition(latLng);
    }

    if (updateInputs || document.getElementById('latitud')?.type === 'hidden') {
        document.getElementById('latitud').value = lat.toFixed(7);
        document.getElementById('longitud').value = lng.toFixed(7);
    }

    updateSelectedCoordinatesText(lat, lng);
    updateFormMapCircle();
}

function updateSelectedCoordinatesText(lat, lng) {
    const coordinatesText = document.querySelector('.js-coordenadas-seleccionadas');

    if (!coordinatesText) {
        return;
    }

    coordinatesText.textContent = `Lat. ${lat.toFixed(7)} / Long. ${lng.toFixed(7)}`;
}

function updateFormMapFromInputs() {
    const lat = Number(document.getElementById('latitud')?.value);
    const lng = Number(document.getElementById('longitud')?.value);

    setFormMapPosition(lat, lng);
}

function updateFormMapCircle() {
    if (!ubicacionFormMap || !ubicacionFormMarker) {
        return;
    }

    const radioKm = getRadioCoberturaKm();
    const radioMetros = radioKm > 0 ? radioKm * 1000 : 0;

    if (ubicacionFormCircle) {
        ubicacionFormCircle.setMap(null);
        ubicacionFormCircle = null;
    }

    if (radioMetros > 0) {
        ubicacionFormCircle = new google.maps.Circle({
            map: ubicacionFormMap,
            center: ubicacionFormMarker.getPosition(),
            radius: radioMetros,
            strokeColor: '#405189',
            strokeOpacity: 0.9,
            strokeWeight: 2,
            fillColor: '#405189',
            fillOpacity: 0.12,
        });
    }
}

function updateRadioHelp() {
    const help = document.querySelector('.js-radio-help');
    const hiddenInput = document.getElementById('radio_cobertura_km');
    const radio = getRadioCoberturaKm();
    const radioValue = document.getElementById('radioCoberturaValue');
    const radioToSave = Math.round(radio);

    if (hiddenInput && radioToSave >= 1 && radioToSave <= 5) {
        hiddenInput.value = radioToSave;
    }

    if (radioValue && radio) {
        radioValue.textContent = Number(radio).toFixed(1);
    }

    if (!help) {
        return;
    }

    help.textContent = radio
        ? `El proveedor cubrira aproximadamente ${Number(radio).toFixed(1)} km alrededor del punto seleccionado.`
        : 'Si no defines un radio, solo se usara el punto exacto.';
}

function getRadioCoberturaKm() {
    const slider = document.getElementById('radio_cobertura_slider');

    if (slider) {
        return Number(slider.value || 0) / 10;
    }

    return Number(document.getElementById('radio_cobertura_km')?.value || 0);
}

function initUbicacionesIndexMap() {
    const mapElement = document.getElementById('ubicacionesProveedorIndexMap');

    if (!mapElement || !isGoogleMapsReady() || ubicacionesIndexMap) {
        return;
    }

    const markers = Array.from(document.querySelectorAll('.js-ubicacion-map-marker'));
    const bounds = new google.maps.LatLngBounds();
    let hasBounds = false;

    ubicacionesIndexMap = new google.maps.Map(mapElement, {
        center: { lat: -16.5, lng: -68.15 },
        zoom: 12,
        mapTypeControl: false,
        zoomControl: true,
        streetViewControl: false,
        fullscreenControl: false,
        clickableIcons: false,
        styles: ubicacionProveedorMapStyle
    });

    ubicacionesIndexInfoWindow = new google.maps.InfoWindow();

    markers.forEach(function (markerElement) {
        const lat = Number(markerElement.dataset.lat);
        const lng = Number(markerElement.dataset.lng);
        const radio = Number(markerElement.dataset.radio || 0);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }

        const latLng = { lat: lat, lng: lng };
        bounds.extend(latLng);
        hasBounds = true;

        const marker = new google.maps.Marker({
            position: latLng,
            map: ubicacionesIndexMap,
            title: markerElement.dataset.proveedor || 'Proveedor',
            icon: getUbicacionProveedorMarkerIcon()
        });

        marker.addListener('click', function () {
            ubicacionesIndexInfoWindow.setContent(`<strong>${markerElement.dataset.proveedor || 'Proveedor'}</strong><br>${markerElement.dataset.zona || 'Sin zona'}`);
            ubicacionesIndexInfoWindow.open(ubicacionesIndexMap, marker);
        });

        if (radio > 0) {
            new google.maps.Circle({
                map: ubicacionesIndexMap,
                center: latLng,
                radius: radio * 1000,
                strokeColor: '#405189',
                strokeOpacity: 0.8,
                strokeWeight: 1,
                fillColor: '#405189',
                fillOpacity: 0.08,
            });
        }
    });

    if (hasBounds) {
        ubicacionesIndexMap.fitBounds(bounds);
    }
}

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-delete-ubicacion-proveedor-livewire');

    if (!button) {
        return;
    }

    const ubicacionProveedorId = Number(button.dataset.ubicacionProveedorId);
    const proveedor = button.dataset.proveedorNombre || 'este proveedor';

    if (!ubicacionProveedorId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarEliminarUbicacionProveedor', { ubicacionProveedorId: ubicacionProveedorId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se eliminara la ubicacion de ${proveedor}.`,
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
            Livewire.dispatch('confirmarEliminarUbicacionProveedor', { ubicacionProveedorId: ubicacionProveedorId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('ubicacion-proveedor-eliminada', function (event) {
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
