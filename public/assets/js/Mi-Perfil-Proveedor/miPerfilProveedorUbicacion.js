let miUbicacionProveedorMap = null;
let miUbicacionProveedorMarker = null;
let miUbicacionProveedorCircle = null;
let miUbicacionLocationButton = null;

const miUbicacionMapStyle = [
    { featureType: 'poi', stylers: [{ visibility: 'off' }] },
    { featureType: 'transit', stylers: [{ visibility: 'off' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#6b7280' }] },
    { featureType: 'landscape', elementType: 'geometry', stylers: [{ color: '#f3f4f6' }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#dbeafe' }] },
    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#d1d5db' }] }
];

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-mi-ubicacion-form-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            toggleMiUbicacionForm();
        });
    });

    document.getElementById('radio_cobertura_slider')?.addEventListener('input', function () {
        updateMiUbicacionRadioHelp();
        updateMiUbicacionCircle();
    });

    updateMiUbicacionRadioHelp();

    if (isMiUbicacionGoogleMapsReady()) {
        window.initMiPerfilProveedorUbicacionMap();
    }
});

window.initMiPerfilProveedorUbicacionMap = function () {
    const mapElement = document.getElementById('miUbicacionProveedorMap');

    if (!mapElement || !isMiUbicacionGoogleMapsReady() || miUbicacionProveedorMap) {
        return;
    }

    const lat = Number(document.getElementById('latitud')?.value || mapElement.dataset.lat || -16.5);
    const lng = Number(document.getElementById('longitud')?.value || mapElement.dataset.lng || -68.15);

    miUbicacionProveedorMap = new google.maps.Map(mapElement, {
        center: { lat: lat, lng: lng },
        zoom: 13,
        mapTypeControl: false,
        zoomControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        clickableIcons: false,
        gestureHandling: 'greedy',
        styles: miUbicacionMapStyle
    });

    setMiUbicacionPosition(lat, lng, true);
    addMiUbicacionMapControls();

    miUbicacionProveedorMap.addListener('click', function (event) {
        setMiUbicacionPosition(event.latLng.lat(), event.latLng.lng(), true);
    });

};

function toggleMiUbicacionForm(forceOpen = null) {
    const formPanel = document.getElementById('miUbicacionFormPanel');
    const summaryPanel = document.getElementById('miUbicacionSummaryPanel');

    if (!formPanel) {
        return;
    }

    const shouldOpen = forceOpen === null ? formPanel.classList.contains('d-none') : forceOpen;

    formPanel.classList.toggle('d-none', !shouldOpen);
    summaryPanel?.classList.toggle('d-none', shouldOpen);
}

function isMiUbicacionGoogleMapsReady() {
    return typeof google !== 'undefined' && google.maps;
}

function getMiUbicacionMarkerIcon() {
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

function addMiUbicacionMapControls() {
    if (!miUbicacionProveedorMap || miUbicacionLocationButton) {
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

    locationButton.addEventListener('click', confirmMiUbicacionBeforeLocate);

    zoomInButton.addEventListener('click', function () {
        miUbicacionProveedorMap.setZoom(miUbicacionProveedorMap.getZoom() + 1);
    });

    zoomOutButton.addEventListener('click', function () {
        miUbicacionProveedorMap.setZoom(miUbicacionProveedorMap.getZoom() - 1);
    });

    control.appendChild(locationButton);
    control.appendChild(zoomInButton);
    control.appendChild(zoomOutButton);
    miUbicacionProveedorMap.controls[google.maps.ControlPosition.RIGHT_TOP].push(control);
    miUbicacionLocationButton = locationButton;
}

function confirmMiUbicacionBeforeLocate() {
    if (typeof Swal === 'undefined') {
        locateMiUbicacionCurrentPosition(false);
        return;
    }

    Swal.fire({
        title: 'Compartir ubicacion',
        text: 'Quieres usar tu ubicacion actual para mover el punto en el mapa?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, usar mi ubicacion',
        cancelButtonText: 'No, cancelar',
        confirmButtonColor: '#405189',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            locateMiUbicacionCurrentPosition(false);
        }
    });
}

function locateMiUbicacionCurrentPosition(isAutomatic = false) {
    if (!navigator.geolocation || !miUbicacionProveedorMap) {
        return;
    }

    miUbicacionLocationButton?.classList.add('is-loading');

    navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        setMiUbicacionPosition(lat, lng, true);
        miUbicacionProveedorMap.panTo({ lat: lat, lng: lng });
        miUbicacionProveedorMap.setZoom(15);
        miUbicacionLocationButton?.classList.remove('is-loading');
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

        miUbicacionLocationButton?.classList.remove('is-loading');
    }, {
        enableHighAccuracy: true,
        timeout: 8000,
        maximumAge: 60000
    });
}

function setMiUbicacionPosition(lat, lng, updateInputs = false) {
    if (!miUbicacionProveedorMap || Number.isNaN(lat) || Number.isNaN(lng)) {
        return;
    }

    const latLng = { lat: lat, lng: lng };

    if (!miUbicacionProveedorMarker) {
        miUbicacionProveedorMarker = new google.maps.Marker({
            position: latLng,
            map: miUbicacionProveedorMap,
            draggable: true,
            title: 'Mi ubicacion de atencion',
            icon: getMiUbicacionMarkerIcon()
        });

        miUbicacionProveedorMarker.addListener('dragend', function (event) {
            setMiUbicacionPosition(event.latLng.lat(), event.latLng.lng(), true);
        });
    } else {
        miUbicacionProveedorMarker.setPosition(latLng);
    }

    if (updateInputs) {
        document.getElementById('latitud').value = lat.toFixed(7);
        document.getElementById('longitud').value = lng.toFixed(7);
    }

    updateMiUbicacionCoordinatesText(lat, lng);
    updateMiUbicacionCircle();
}

function updateMiUbicacionCoordinatesText(lat, lng) {
    const coordinatesText = document.querySelector('.js-coordenadas-seleccionadas');

    if (coordinatesText) {
        coordinatesText.textContent = `Lat. ${lat.toFixed(7)} / Long. ${lng.toFixed(7)}`;
    }
}

function updateMiUbicacionCircle() {
    if (!miUbicacionProveedorMap || !miUbicacionProveedorMarker) {
        return;
    }

    const radioKm = getMiUbicacionRadioKm();
    const radioMetros = radioKm > 0 ? radioKm * 1000 : 0;

    if (miUbicacionProveedorCircle) {
        miUbicacionProveedorCircle.setMap(null);
        miUbicacionProveedorCircle = null;
    }

    if (radioMetros > 0) {
        miUbicacionProveedorCircle = new google.maps.Circle({
            map: miUbicacionProveedorMap,
            center: miUbicacionProveedorMarker.getPosition(),
            radius: radioMetros,
            strokeColor: '#6f5cff',
            strokeOpacity: 0.9,
            strokeWeight: 2,
            fillColor: '#6f5cff',
            fillOpacity: 0.12
        });
    }
}

function updateMiUbicacionRadioHelp() {
    const hiddenInput = document.getElementById('radio_cobertura_km');
    const radioValue = document.getElementById('radioCoberturaValue');
    const help = document.querySelector('.js-radio-help');
    const radio = getMiUbicacionRadioKm();
    const radioToSave = Math.round(radio);

    if (hiddenInput && radioToSave >= 1 && radioToSave <= 5) {
        hiddenInput.value = radioToSave;
    }

    if (radioValue && radio) {
        radioValue.textContent = Number(radio).toFixed(1);
    }

    if (help) {
        help.textContent = `Cubriras aproximadamente ${Number(radio).toFixed(1)} km alrededor del punto seleccionado.`;
    }
}

function getMiUbicacionRadioKm() {
    const slider = document.getElementById('radio_cobertura_slider');

    if (slider) {
        return Number(slider.value || 10) / 10;
    }

    return Number(document.getElementById('radio_cobertura_km')?.value || 1);
}
