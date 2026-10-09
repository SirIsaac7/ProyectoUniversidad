let busquedaServiciosMap = null;
let busquedaServiciosMarker = null;
let busquedaServiciosCircle = null;
let busquedaServiciosLocationButton = null;
let zonasSolicitudLaPazCache = null;
let zonasSolicitudLaPazPromise = null;

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
    initProveedorDetailSelectors();
    initBusquedaScrollButtons();
    initBusquedaSemantica();
    initBusquedaInteligente();
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
            initProveedorDetailSelectors();
            cargarZonasSolicitudLaPaz();
        });
    } catch (error) {
        document.addEventListener('livewire:update', function () {
            initSolicitudWizards();
            initProveedorDetailSelectors();
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
            prepararSolicitudModal(modalContent);
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

    document.addEventListener('shown.bs.modal', function (event) {
        if (!event.target?.classList?.contains('proveedor-busqueda-modal')) {
            return;
        }

        prepararSolicitudModal(event.target.querySelector('[data-proveedor-profile-modal]'));
        initProveedorDetailSelectors(event.target);
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

function initBusquedaSemantica() {
    const form = document.getElementById('busquedaSemanticaForm');

    if (!form || form.dataset.inicializado === 'true') {
        return;
    }

    form.dataset.inicializado = 'true';

    const configuracion = obtenerConfiguracionBusquedaSemantica();
    let seguimientoTimer = null;
    let cicloActivo = 0;
    let fallosConsecutivos = 0;

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const input = form.querySelector('[name="consulta"]');
        const error = form.querySelector('[data-busqueda-semantica-error]');

        input?.classList.remove('is-invalid');
        if (error) {
            error.textContent = '';
            error.classList.remove('d-block');
        }

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            form.reportValidity();
            return;
        }

        clearTimeout(seguimientoTimer);
        cicloActivo += 1;
        fallosConsecutivos = 0;
        const ciclo = cicloActivo;
        setEstadoBusquedaSemantica(true);
        renderProcesamientoBusquedaSemantica({ fase: 'iniciando', historial_fases: [] }, configuracion);

        try {
            const data = await solicitarBusquedaSemantica(form.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: new FormData(form)
            });

            if (ciclo !== cicloActivo) return;
            seguirBusquedaSemantica(data.data?.id, ciclo);
        } catch (requestError) {
            if (ciclo !== cicloActivo) return;
            mostrarErrorValidacionBusqueda(requestError, input, error);
            renderEstadoBusquedaSemantica(
                'error',
                'No pudimos consultar el buscador',
                requestError.message || 'Intenta nuevamente en unos minutos.'
            );
            setEstadoBusquedaSemantica(false);
        }
    });

    async function seguirBusquedaSemantica(ejecucionId, ciclo) {
        if (!ejecucionId || ciclo !== cicloActivo) {
            renderEstadoBusquedaSemantica('error', 'Búsqueda no disponible', 'No se recibió un identificador válido.');
            setEstadoBusquedaSemantica(false);
            return;
        }

        try {
            const url = form.dataset.estadoUrl.replace('__EJECUCION__', encodeURIComponent(ejecucionId));
            const respuesta = await solicitarBusquedaSemantica(url);

            if (ciclo !== cicloActivo) return;
            fallosConsecutivos = 0;
            const estado = respuesta.data || {};
            renderProcesamientoBusquedaSemantica(estado, configuracion);

            if (estado.terminal) {
                if (estado.categoria_estado === 'completado' || estado.categoria_estado === 'sin_resultados') {
                    await cargarResultadosBusquedaSemantica(ejecucionId, ciclo);
                } else {
                    renderEstadoBusquedaSemantica(
                        'error',
                        'La búsqueda no pudo completarse',
                        estado.error || mensajeFaseBusqueda('error', configuracion)
                    );
                    setEstadoBusquedaSemantica(false);
                }
                return;
            }

            seguimientoTimer = setTimeout(function () {
                seguirBusquedaSemantica(ejecucionId, ciclo);
            }, 650);
        } catch (requestError) {
            if (ciclo !== cicloActivo) return;
            fallosConsecutivos += 1;

            if ([403, 404, 422].includes(requestError.status) || fallosConsecutivos >= 5) {
                renderEstadoBusquedaSemantica(
                    'error',
                    'Se interrumpió el seguimiento',
                    requestError.message || 'No fue posible recuperar el estado de la búsqueda.'
                );
                setEstadoBusquedaSemantica(false);
                return;
            }

            renderProcesamientoBusquedaSemantica(
                { fase: 'error_temporal', detalle: 'Se perdió temporalmente la conexión. Volveremos a intentarlo.', historial_fases: [] },
                configuracion
            );
            seguimientoTimer = setTimeout(function () {
                seguirBusquedaSemantica(ejecucionId, ciclo);
            }, 2000);
        }
    }

    async function cargarResultadosBusquedaSemantica(ejecucionId, ciclo) {
        try {
            const url = form.dataset.resultadosUrl.replace('__EJECUCION__', encodeURIComponent(ejecucionId));
            const data = await solicitarBusquedaSemantica(url);

            if (ciclo !== cicloActivo) return;
            const container = document.getElementById('busquedaSemanticaResultado');
            container.innerHTML = data.html || '';
            initSolicitudWizards();
            initProveedorDetailSelectors(container);
            cargarZonasSolicitudLaPaz();
        } catch (requestError) {
            if (ciclo !== cicloActivo) return;
            renderEstadoBusquedaSemantica(
                'error',
                'No pudimos recuperar los resultados',
                requestError.message || 'Intenta realizar la búsqueda nuevamente.'
            );
        } finally {
            if (ciclo === cicloActivo) setEstadoBusquedaSemantica(false);
        }
    }
}

function setEstadoBusquedaSemantica(cargando) {
    const button = document.getElementById('btnBusquedaSemantica');

    if (button) {
        button.disabled = cargando;
        button.innerHTML = cargando
            ? '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Buscando'
            : '<i class="ri-user-search-line align-bottom me-1"></i> Buscar tecnicos';
    }

}

function obtenerConfiguracionBusquedaSemantica() {
    const elemento = document.getElementById('busquedaSemanticaConfiguracion');

    try {
        return JSON.parse(elemento?.textContent || '{}');
    } catch (error) {
        return {};
    }
}

async function solicitarBusquedaSemantica(url, opciones) {
    const response = await fetch(url, {
        ...(opciones || {}),
        headers: {
            Accept: 'application/json',
            ...(opciones?.headers || {})
        }
    });
    const data = await response.json().catch(function () {
        return {};
    });

    if (!response.ok || !data.ok) {
        const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
        const error = new Error(validationMessage || data.mensaje || 'No se pudo completar la búsqueda.');
        error.status = response.status;
        error.payload = data;
        throw error;
    }

    return data;
}

function mostrarErrorValidacionBusqueda(requestError, input, errorContainer) {
    if (!requestError.payload?.errors?.consulta || !input) {
        return;
    }

    input.classList.add('is-invalid');
    if (errorContainer) {
        errorContainer.textContent = requestError.payload.errors.consulta[0];
        errorContainer.classList.add('d-block');
    }
}

function renderProcesamientoBusquedaSemantica(estado, configuracion) {
    const container = document.getElementById('busquedaSemanticaResultado');

    if (!container) return;

    const card = document.createElement('div');
    const body = document.createElement('div');
    const main = document.createElement('div');
    const animation = document.createElement('div');
    const text = document.createElement('div');
    const title = document.createElement('h5');
    const message = document.createElement('p');
    const fase = estado.fase || 'iniciando';

    card.className = 'card busqueda-semantica-processing';
    body.className = 'card-body';
    main.className = 'busqueda-processing-main';
    animation.className = 'semantic-search-animation is-' + (configuracion.animacion || 'spinner');
    animation.setAttribute('aria-hidden', 'true');
    title.className = 'mb-1';
    title.textContent = fase === 'error_temporal' ? 'Reconectando con el buscador' : 'Analizando tu consulta';
    message.className = 'text-muted mb-0';
    message.textContent = fase === 'error_temporal'
        ? estado.detalle
        : mensajeFaseBusqueda(fase, configuracion, estado.detalle);
    text.append(title, message);
    main.append(animation, text);
    body.appendChild(main);

    const presentation = configuracion.presentacion_fases || 'actual';
    const historial = Array.isArray(estado.historial_fases) ? estado.historial_fases : [];

    if (presentation === 'lista' || presentation === 'linea') {
        const phases = document.createElement('div');
        phases.className = 'busqueda-real-phases' + (presentation === 'lista' ? ' is-list' : '');

        historial.forEach(function (item, index) {
            const chip = document.createElement('span');
            const icon = document.createElement('i');
            const esActual = item.fase === fase && index === historial.length - 1;
            chip.className = 'busqueda-real-phase ' + (esActual ? 'is-current' : 'is-complete');
            icon.className = esActual ? 'ri-loader-4-line' : 'ri-check-line';
            chip.appendChild(icon);
            chip.append(document.createTextNode(mensajeFaseBusqueda(item.fase, configuracion, item.detalle)));
            phases.appendChild(chip);
        });

        body.appendChild(phases);
    }

    card.appendChild(body);
    container.replaceChildren(card);
}

function mensajeFaseBusqueda(fase, configuracion, detalle) {
    const personalizado = configuracion.mensajes_fases?.[fase];
    return personalizado || detalle || 'Procesando tu búsqueda...';
}

function renderEstadoBusquedaSemantica(tipo, titulo, mensaje) {
    const container = document.getElementById('busquedaSemanticaResultado');

    if (!container) {
        return;
    }

    const card = document.createElement('div');
    const body = document.createElement('div');
    const icon = document.createElement('i');
    const heading = document.createElement('h5');
    const description = document.createElement('p');

    card.className = 'card busqueda-semantica-status busqueda-semantica-status--' + tipo;
    body.className = 'card-body text-center py-5';
    icon.className = tipo === 'error' ? 'ri-error-warning-line fs-32 text-danger' : 'ri-information-line fs-32 text-primary';
    heading.className = 'mt-3 mb-2';
    description.className = 'text-muted mb-0';
    heading.textContent = titulo;
    description.textContent = mensaje;

    body.append(icon, heading, description);
    card.appendChild(body);
    container.replaceChildren(card);
}

function initBusquedaInteligente() {
    const form = document.getElementById('busquedaInteligenteForm');

    if (!form || form.dataset.inicializado === 'true') {
        return;
    }

    form.dataset.inicializado = 'true';
    initDictadoProblemaIA();

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            form.reportValidity();
            return;
        }

        const imagen = form.querySelector('input[name="imagen"]')?.files?.[0];

        if (imagen && imagen.size > 8 * 1024 * 1024) {
            mostrarAlertaBusquedaInteligente('Imagen demasiado grande', 'La imagen no debe superar los 8MB.', 'error');
            return;
        }

        const usarUbicacion = form.querySelector('input[name="usar_ubicacion"]')?.checked;

        if (usarUbicacion && navigator.geolocation) {
            setEstadoBusquedaInteligente(true);

            navigator.geolocation.getCurrentPosition(function (position) {
                form.querySelector('#latClienteIA').value = position.coords.latitude;
                form.querySelector('#lonClienteIA').value = position.coords.longitude;
                enviarBusquedaInteligente(form);
            }, function () {
                form.querySelector('#latClienteIA').value = '';
                form.querySelector('#lonClienteIA').value = '';
                mostrarAlertaBusquedaInteligente('Ubicacion no disponible', 'Se realizara la busqueda sin coordenadas.', 'info');
                enviarBusquedaInteligente(form);
            }, {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 60000
            });

            return;
        }

        enviarBusquedaInteligente(form);
    });
}

function initDictadoProblemaIA() {
    const button = document.getElementById('btnDictarProblemaIA');
    const textarea = document.getElementById('descripcionProblemaIA');
    const status = document.getElementById('estadoDictadoProblemaIA');

    if (!button || !textarea || button.dataset.inicializado === 'true') {
        return;
    }

    button.dataset.inicializado = 'true';

    if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
        button.disabled = true;
        status.textContent = 'El dictado por voz no esta disponible en este navegador.';
        return;
    }

    let recorder = null;
    let stream = null;
    let chunks = [];
    let stopTimer = null;

    button.addEventListener('click', async function () {
        if (recorder?.state === 'recording') {
            recorder.stop();
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chunks = [];

            const mimeType = obtenerMimeTypeGrabacion();
            recorder = mimeType
                ? new MediaRecorder(stream, { mimeType: mimeType })
                : new MediaRecorder(stream);

            recorder.addEventListener('dataavailable', function (event) {
                if (event.data.size > 0) {
                    chunks.push(event.data);
                }
            });

            recorder.addEventListener('stop', async function () {
                clearTimeout(stopTimer);
                liberarMicrofono(stream);

                const audioType = recorder.mimeType || chunks[0]?.type || 'audio/webm';
                const audio = new Blob(chunks, { type: audioType });

                if (audio.size === 0) {
                    setEstadoDictado(button, status, 'idle', 'No se capturo audio. Intenta nuevamente.');
                    return;
                }

                await transcribirAudioWhisper(textarea, button, status, audio);
            }, { once: true });

            recorder.addEventListener('error', function () {
                clearTimeout(stopTimer);
                liberarMicrofono(stream);
                setEstadoDictado(button, status, 'idle', 'No se pudo completar la grabacion.');
            }, { once: true });

            recorder.start();
            setEstadoDictado(button, status, 'recording', 'Escuchando... Pulsa Detener cuando termines.');

            stopTimer = setTimeout(function () {
                if (recorder?.state === 'recording') {
                    recorder.stop();
                }
            }, 60000);
        } catch (error) {
            liberarMicrofono(stream);
            setEstadoDictado(button, status, 'idle', 'No se pudo acceder al microfono. Revisa el permiso del navegador.');
        }
    });
}

function obtenerMimeTypeGrabacion() {
    const tipos = [
        'audio/webm;codecs=opus',
        'audio/ogg;codecs=opus',
        'audio/mp4',
        'audio/webm'
    ];

    return tipos.find(function (tipo) {
        return MediaRecorder.isTypeSupported(tipo);
    }) || '';
}

function liberarMicrofono(stream) {
    stream?.getTracks().forEach(function (track) {
        track.stop();
    });
}

async function transcribirAudioWhisper(textarea, button, status, audio) {
    const formData = new FormData();
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const extension = audio.type.includes('ogg') ? 'ogg' : (audio.type.includes('mp4') ? 'mp4' : 'webm');

    formData.append('audio', audio, 'descripcion-problema.' + extension);
    setEstadoDictado(button, status, 'processing', 'Transcribiendo tu voz con Whisper...');

    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await response.json().catch(function () {
            return {};
        });

        if (!response.ok || !data.ok) {
            const validationMessage = data.errors
                ? Object.values(data.errors).flat()[0]
                : null;

            throw new Error(validationMessage || data.mensaje || 'No se pudo transcribir el audio.');
        }

        const textoActual = textarea.value.trim();
        const textoTranscrito = String(data.texto || '').trim();
        const textoCompleto = [textoActual, textoTranscrito].filter(Boolean).join(' ').slice(0, 1000);

        textarea.value = textoCompleto;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.focus();
        setEstadoDictado(button, status, 'idle', 'Transcripcion completada. Puedes editar el texto antes de buscar.');
    } catch (error) {
        setEstadoDictado(button, status, 'idle', error.message);
        mostrarAlertaBusquedaInteligente('No se pudo transcribir', error.message, 'error');
    }
}

function setEstadoDictado(button, status, state, message) {
    const isRecording = state === 'recording';
    const isProcessing = state === 'processing';

    button.disabled = isProcessing;
    button.classList.toggle('is-recording', isRecording);
    button.innerHTML = isRecording
        ? '<i class="ri-stop-circle-line align-bottom me-1"></i> Detener'
        : (isProcessing
            ? '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Transcribiendo'
            : '<i class="ri-mic-line align-bottom me-1"></i> Dictar');

    status.classList.toggle('is-recording', isRecording);
    status.classList.toggle('is-processing', isProcessing);
    status.textContent = message;
}

function enviarBusquedaInteligente(form) {
    const resultContainer = document.getElementById('busquedaInteligenteResultado');
    const formData = new FormData(form);
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    setEstadoBusquedaInteligente(true);

    if (resultContainer) {
        resultContainer.innerHTML = `
            <div class="card busqueda-ia-loading">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h5 class="mb-1">Analizando tu solicitud</h5>
                    <p class="text-muted mb-0">Clasificando la imagen y buscando proveedores compatibles...</p>
                </div>
            </div>
        `;
    }

    fetch(form.dataset.url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(async function (response) {
            const data = await response.json().catch(function () {
                return {};
            });

            if (!response.ok || !data.ok) {
                throw new Error(data.mensaje || 'No se pudo completar la busqueda inteligente.');
            }

            return data;
        })
        .then(function (data) {
            if (resultContainer) {
                resultContainer.innerHTML = data.html || '';
            }

            initSolicitudWizards();
            cargarZonasSolicitudLaPaz(resultContainer || document);

            if (typeof window.iniciarMapasLaPaz === 'function') {
                window.iniciarMapasLaPaz();
            }

            resultContainer?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(function (error) {
            if (resultContainer) {
                resultContainer.innerHTML = '';
            }

            mostrarAlertaBusquedaInteligente('Busqueda no disponible', error.message, 'error');
        })
        .finally(function () {
            setEstadoBusquedaInteligente(false);
        });
}

function setEstadoBusquedaInteligente(isLoading) {
    const button = document.getElementById('btnBusquedaInteligente');

    if (!button) {
        return;
    }

    button.disabled = isLoading;
    button.innerHTML = isLoading
        ? '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Buscando...'
        : '<i class="ri-search-eye-line align-bottom me-1"></i> Buscar con IA';
}

function mostrarAlertaBusquedaInteligente(title, text, icon) {
    if (typeof Swal === 'undefined') {
        alert(text);
        return;
    }

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: icon === 'error' ? '#f06548' : '#405189'
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
        inicializarSolicitudWizard(form);
    });
}

function initProveedorDetailSelectors(scope = document) {
    scope.querySelectorAll('.proveedor-detail-option').forEach(function (option) {
        if (option.dataset.detailInitialized === 'true') {
            return;
        }

        option.dataset.detailInitialized = 'true';

        option.addEventListener('click', function () {
            const group = option.dataset.detailGroup;

            if (!group) {
                return;
            }

            const container = option.closest('.tab-pane') || option.closest('.modal') || document;
            const preview = container.querySelector('[data-detail-preview="' + group + '"]');

            if (!preview) {
                return;
            }

            container.querySelectorAll('.proveedor-detail-option[data-detail-group="' + group + '"]').forEach(function (item) {
                item.classList.toggle('is-active', item === option);
            });

            const image = preview.querySelector('[data-detail-image]');
            const empty = preview.querySelector('[data-detail-empty]');
            const title = preview.querySelector('[data-detail-title]');
            const description = preview.querySelector('[data-detail-description]');
            const metaPrimary = preview.querySelector('[data-detail-meta-primary]');
            const metaSecondary = preview.querySelector('[data-detail-meta-secondary]');
            const link = preview.querySelector('[data-detail-link]');
            const galleryPreview = preview.querySelector('[data-detail-gallery-preview]');
            const imageUrl = option.dataset.detailImage || '';
            const iconClass = option.dataset.detailIcon || 'ri-file-text-line';

            if (title) {
                title.textContent = option.dataset.detailTitle || '';
            }

            if (description) {
                description.textContent = option.dataset.detailDescription || '';
            }

            if (metaPrimary) {
                metaPrimary.textContent = option.dataset.detailMetaPrimary || '';
            }

            if (metaSecondary) {
                metaSecondary.textContent = option.dataset.detailMetaSecondary || '';
            }

            if (image) {
                if (imageUrl) {
                    image.src = imageUrl;
                    image.alt = option.dataset.detailTitle || 'Detalle';
                    image.classList.remove('d-none');
                    empty?.classList.add('d-none');
                } else {
                    image.src = '';
                    image.alt = '';
                    image.classList.add('d-none');

                    if (empty) {
                        empty.innerHTML = '<i class="' + iconClass + '"></i>';
                        empty.classList.remove('d-none');
                    }
                }
            }

            if (link) {
                const href = option.dataset.detailLink || '';

                if (href) {
                    link.href = href;
                    link.classList.remove('disabled');
                    link.removeAttribute('aria-disabled');
                } else {
                    link.href = 'javascript:void(0);';
                    link.classList.add('disabled');
                    link.setAttribute('aria-disabled', 'true');
                }
            }

            if (galleryPreview) {
                let gallery = [];

                try {
                    gallery = JSON.parse(option.dataset.detailGallery || '[]');
                } catch (error) {
                    gallery = [];
                }

                galleryPreview.innerHTML = '';

                gallery.forEach(function (item, index) {
                    if (!item.url) {
                        return;
                    }

                    const thumb = document.createElement('button');
                    thumb.type = 'button';
                    thumb.className = 'proveedor-detail-gallery-thumb' + (index === 0 ? ' is-active' : '');
                    thumb.setAttribute('data-gallery-image', item.url);
                    thumb.innerHTML = '<img src="' + item.url + '" alt="' + (item.titulo || option.dataset.detailTitle || 'Imagen del trabajo') + '">';

                    thumb.addEventListener('click', function () {
                        galleryPreview.querySelectorAll('.proveedor-detail-gallery-thumb').forEach(function (button) {
                            button.classList.remove('is-active');
                        });

                        thumb.classList.add('is-active');

                        if (image) {
                            image.src = item.url;
                            image.alt = item.titulo || option.dataset.detailTitle || 'Detalle';
                            image.classList.remove('d-none');
                            empty?.classList.add('d-none');
                        }
                    });

                    galleryPreview.appendChild(thumb);
                });
            }
        });
    });
}

function inicializarSolicitudWizard(form) {
    if (!form) {
        return;
    }

    if (form.dataset.wizardInicializado === 'true') {
        actualizarAyudaTipoAtencion(form);
        actualizarTituloSolicitud(form);
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
}

function prepararSolicitudModal(modalContent) {
    if (!modalContent) {
        return;
    }

    modalContent.querySelectorAll('[data-solicitud-wizard]').forEach(function (form) {
        inicializarSolicitudWizard(form);
    });

    cargarZonasSolicitudLaPaz(modalContent);
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

function cargarZonasSolicitudLaPaz(scope = document) {
    const selects = Array.from(scope.querySelectorAll('[data-zonas-lapaz-select]'))
        .filter(function (select) {
            return select.dataset.zonasSolicitudCargadas !== 'true';
        });

    if (!selects.length) {
        return;
    }

    obtenerZonasSolicitudLaPaz()
        .then(function (zonas) {
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

function obtenerZonasSolicitudLaPaz() {
    if (zonasSolicitudLaPazCache) {
        return Promise.resolve(zonasSolicitudLaPazCache);
    }

    if (!zonasSolicitudLaPazPromise) {
        zonasSolicitudLaPazPromise = fetch('/assets/js/maps/LaPaz/zonasGAMPL.geojson')
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('No se pudo cargar las zonas.');
                }

                return response.json();
            })
            .then(function (geojson) {
                zonasSolicitudLaPazCache = extraerZonasSolicitudLaPaz(geojson);
                return zonasSolicitudLaPazCache;
            })
            .catch(function () {
                zonasSolicitudLaPazCache = [];
                return zonasSolicitudLaPazCache;
            });
    }

    return zonasSolicitudLaPazPromise;
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

    if (select.zonaSolicitudChoices) {
        select.zonaSolicitudChoices.destroy();
        select.zonaSolicitudChoices = null;
        delete select.dataset.choicesInicializado;
    }

    zonas.forEach(function (zona) {
        opciones.push('<option value="' + escaparHtmlSolicitud(zona) + '"' + (zona === selected ? ' selected' : '') + '>' + escaparHtmlSolicitud(zona) + '</option>');
    });

    if (selected && !zonas.includes(selected)) {
        opciones.push('<option value="' + escaparHtmlSolicitud(selected) + '" selected>' + escaparHtmlSolicitud(selected) + '</option>');
    }

    select.innerHTML = opciones.join('');
    select.value = selected;
    select.dataset.zonasSolicitudCargadas = 'true';

    if (typeof Choices !== 'undefined' && select.dataset.choicesInicializado !== 'true') {
        select.dataset.choicesInicializado = 'true';
        select.zonaSolicitudChoices = new Choices(select, {
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
