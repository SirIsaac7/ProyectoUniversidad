document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('motorBusquedaPanel');

    if (!panel) {
        return;
    }

    const puedeConsultar = panel.dataset.puedeConsultar.trim() === '1';
    const puedeIndexar = panel.dataset.puedeIndexar.trim() === '1';
    let identificadorActivo = panel.dataset.identificadorActivo.trim();
    let pollingTimer = null;
    let consultandoProgreso = false;

    initPersonalizacionBusqueda();

    const frecuenciaIndexacion = document.getElementById('frecuenciaIndexacion');
    frecuenciaIndexacion?.addEventListener('change', actualizarCampoDiaSemana);
    actualizarCampoDiaSemana();

    function initPersonalizacionBusqueda() {
        const form = document.getElementById('personalizacionBusquedaForm');
        const preview = document.getElementById('vistaPreviaBusqueda');

        if (!form || !preview) {
            return;
        }

        const familias = {
            system: 'system-ui, -apple-system, "Segoe UI", sans-serif',
            inter: 'Inter, system-ui, sans-serif',
            poppins: 'Poppins, system-ui, sans-serif',
            roboto: 'Roboto, Arial, sans-serif',
            arial: 'Arial, Helvetica, sans-serif'
        };
        const variables = {
            color_primario: '--busqueda-primary',
            color_secundario: '--busqueda-secondary',
            color_boton: '--busqueda-button',
            color_fondo: '--busqueda-background',
            color_indicador: '--busqueda-indicator',
            color_fase_completada: '--busqueda-phase-complete',
            color_fase_actual: '--busqueda-phase-current',
            color_informativo: '--busqueda-info',
            color_error: '--busqueda-error',
            color_destacado: '--busqueda-highlight'
        };

        form.querySelectorAll('[data-personalizacion-field]').forEach(function (field) {
            field.addEventListener('input', actualizarVistaPrevia);
            field.addEventListener('change', actualizarVistaPrevia);
        });
        form.querySelectorAll('[data-personalizacion-mensaje]').forEach(function (field) {
            field.addEventListener('input', actualizarVistaPrevia);
        });

        document.querySelector('[data-restaurar-personalizacion]')?.addEventListener('submit', function (event) {
            if (!window.confirm('¿Restaurar todos los valores visuales predeterminados?')) {
                event.preventDefault();
            }
        });

        actualizarVistaPrevia();

        function actualizarVistaPrevia() {
            Object.entries(variables).forEach(function ([campo, variable]) {
                const input = form.elements[campo];

                if (input?.value) {
                    preview.style.setProperty(variable, input.value);
                    const value = form.querySelector('[data-color-value="' + campo + '"]');
                    if (value) value.textContent = input.value.toUpperCase();
                }
            });

            preview.style.setProperty('--busqueda-font', familias[form.elements.familia_tipografica?.value] || familias.system);
            preview.style.setProperty('--busqueda-title-size', limitarNumero(form.elements.tamano_titulos?.value, 18, 36, 22) + 'px');
            preview.style.setProperty('--busqueda-message-size', limitarNumero(form.elements.tamano_mensajes?.value, 12, 20, 14) + 'px');
            preview.style.setProperty('--busqueda-font-weight', form.elements.grosor_texto?.value || '600');
            preview.style.setProperty('--busqueda-radius', limitarNumero(form.elements.radio_bordes?.value, 0, 24, 12) + 'px');
            preview.className = 'motor-search-preview is-card-' + form.elements.diseno_tarjetas.value
                + ' is-spacing-' + form.elements.espaciado.value;

            const animation = preview.querySelector('[data-preview-animation]');
            animation.className = 'busqueda-animation is-' + form.elements.animacion.value;
            preview.querySelector('[data-preview-message]').textContent = form.querySelector('[data-personalizacion-mensaje="buscando_qdrant"]')?.value || '';
            preview.querySelector('[data-preview-phases]').hidden = form.elements.presentacion_fases.value === 'actual';
        }

        function limitarNumero(valor, minimo, maximo, predeterminado) {
            const numero = Number.parseInt(valor, 10);
            return Number.isFinite(numero) ? Math.min(maximo, Math.max(minimo, numero)) : predeterminado;
        }
    }

    document.getElementById('actualizarEstadoMotor')?.addEventListener('click', consultarEstadoServicios);
    document.getElementById('iniciarIndexacionMotor')?.addEventListener('click', confirmarIndexacion);

    if (puedeConsultar) {
        consultarEstadoServicios();
    }

    if (identificadorActivo && puedeConsultar) {
        activarSeguimiento(identificadorActivo);
    } else if (identificadorActivo) {
        actualizarBotonIndexacion(true);
    }

    function actualizarCampoDiaSemana() {
        const wrapper = document.getElementById('diaSemanaIndexacionWrapper');
        const select = document.getElementById('diaSemanaIndexacion');
        const esSemanal = frecuenciaIndexacion?.value === 'semanal';

        wrapper?.classList.toggle('d-none', !esSemanal);
        if (select) {
            select.required = esSemanal;
            select.disabled = !esSemanal;
        }
    }

    async function consultarEstadoServicios() {
        const button = document.getElementById('actualizarEstadoMotor');
        setServiciosCargando(true);
        setButtonLoading(button, true, 'Actualizando');

        try {
            const respuesta = await solicitarJson(panel.dataset.estadoUrl);
            renderEstadoServicios(respuesta.data);
        } catch (error) {
            renderEstadoIndeterminado();
            mostrarMensaje('danger', error.message || 'No fue posible consultar el estado del motor.');
        } finally {
            setServiciosCargando(false);
            setButtonLoading(button, false, 'Actualizar estados', 'ri-refresh-line');
        }
    }

    async function confirmarIndexacion() {
        if (!puedeIndexar || identificadorActivo) {
            return;
        }

        let confirmado = false;

        if (typeof Swal !== 'undefined') {
            const resultado = await Swal.fire({
                title: 'Iniciar indexacion',
                text: 'Se sincronizaran manualmente los proveedores con el indice inteligente.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, iniciar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#405189'
            });
            confirmado = resultado.isConfirmed;
        } else {
            confirmado = window.confirm('¿Deseas iniciar la indexacion manual de proveedores?');
        }

        if (!confirmado) {
            return;
        }

        await iniciarIndexacion();
    }

    async function iniciarIndexacion() {
        const button = document.getElementById('iniciarIndexacionMotor');
        setButtonLoading(button, true, 'Solicitando');

        try {
            const respuesta = await solicitarJson(panel.dataset.indexacionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            const identificador = String(respuesta.data?.identificador || '').trim();

            if (!identificador) {
                throw new Error('El motor no devolvio el identificador de la indexacion.');
            }

            mostrarMensaje('success', respuesta.data.mensaje || 'La indexacion fue aceptada.');
            activarSeguimiento(identificador);
        } catch (error) {
            const activo = String(error.payload?.identificador_activo || '').trim();

            if (error.status === 409 && activo && puedeConsultar) {
                activarSeguimiento(activo);
            }

            mostrarMensaje(error.status === 409 ? 'warning' : 'danger', error.message);
        } finally {
            if (!identificadorActivo) {
                setButtonLoading(button, false, 'Iniciar indexacion', 'ri-play-circle-line');
            }
        }
    }

    function activarSeguimiento(identificador) {
        identificadorActivo = identificador;
        panel.dataset.identificadorActivo = identificador;
        document.getElementById('seguimientoIndexacion')?.classList.remove('d-none');
        document.getElementById('indexacionIdentificador').textContent = identificador;
        actualizarBotonIndexacion(true);
        consultarProgreso();
    }

    async function consultarProgreso() {
        if (!identificadorActivo || consultandoProgreso || !puedeConsultar) {
            return;
        }

        consultandoProgreso = true;
        clearTimeout(pollingTimer);

        try {
            const url = panel.dataset.seguimientoUrl.replace('__IDENTIFICADOR__', encodeURIComponent(identificadorActivo));
            const respuesta = await solicitarJson(url);
            const indexacion = respuesta.data;
            renderSeguimiento(indexacion);

            if (indexacion.terminal) {
                identificadorActivo = '';
                panel.dataset.identificadorActivo = '';
                actualizarBotonIndexacion(false);
                return;
            }

            pollingTimer = setTimeout(consultarProgreso, 2500);
        } catch (error) {
            if (error.status === 404) {
                identificadorActivo = '';
                actualizarBotonIndexacion(false);
                mostrarMensaje('warning', 'La ejecucion ya no esta disponible. Es posible que FastAPI se haya reiniciado.');
                return;
            }

            mostrarMensaje('warning', 'Se perdio temporalmente la conexion durante el seguimiento. Se volvera a intentar.');
            pollingTimer = setTimeout(consultarProgreso, 5000);
        } finally {
            consultandoProgreso = false;
        }
    }

    function renderEstadoServicios(data) {
        const general = data.estado_general || 'indeterminado';
        const textos = {
            operativo: ['Motor operativo', 'FastAPI y sus dependencias responden correctamente.', 'active', 'Operativo'],
            degradado: ['Motor con incidencias', 'Uno o más servicios necesarios no se encuentran disponibles.', 'disconnected', 'Con incidencias'],
            indeterminado: ['Estado indeterminado', 'No se pudo determinar el estado de todos los servicios.', 'unknown', 'Indeterminado']
        };
        const estado = textos[general] || textos.indeterminado;

        document.getElementById('motorEstadoGeneral').textContent = estado[0];
        document.getElementById('motorEstadoDescripcion').textContent = estado[1];
        actualizarBadge(document.getElementById('motorEstadoGeneralBadge'), estado[2], estado[3]);
        document.getElementById('motorConsultadoEn').textContent = data.consultado_en
            ? 'Actualizado: ' + formatearFecha(data.consultado_en)
            : 'Actualizado ahora';

        Object.entries(data.servicios || {}).forEach(function ([clave, servicio]) {
            const card = document.querySelector('[data-motor-service="' + clave + '"]');

            if (!card) {
                return;
            }

            card.classList.remove('is-active', 'is-disconnected');
            if (servicio.categoria === 'activo') {
                card.classList.add('is-active');
            } else if (servicio.categoria === 'desconectado') {
                card.classList.add('is-disconnected');
            }
            actualizarBadge(
                card.querySelector('[data-service-badge]'),
                servicio.categoria === 'activo'
                    ? 'active'
                    : (servicio.categoria === 'desconectado' ? 'disconnected' : 'unknown'),
                servicio.categoria === 'activo' ? 'Activo' : (servicio.categoria === 'desconectado' ? 'Desconectado' : 'Indeterminado')
            );
            card.querySelector('[data-service-detail]').textContent = servicio.detalle || 'Estado informado: ' + servicio.estado;
        });
    }

    function renderEstadoIndeterminado() {
        document.getElementById('motorEstadoGeneral').textContent = 'Motor no disponible';
        document.getElementById('motorEstadoDescripcion').textContent = 'Laravel no pudo obtener el estado de FastAPI y sus dependencias.';
        actualizarBadge(document.getElementById('motorEstadoGeneralBadge'), 'unknown', 'Sin conexion');

        document.querySelectorAll('[data-motor-service]').forEach(function (card) {
            card.classList.remove('is-active', 'is-disconnected');
            actualizarBadge(card.querySelector('[data-service-badge]'), 'unknown', 'Indeterminado');
            card.querySelector('[data-service-detail]').textContent = 'No fue posible consultar este servicio.';
        });
    }

    function renderSeguimiento(indexacion) {
        document.getElementById('indexacionIdentificador').textContent = indexacion.identificador || identificadorActivo;
        document.getElementById('indexacionFase').textContent = indexacion.fase_actual || 'Fase no informada';
        document.getElementById('indexacionDescripcion').textContent = indexacion.descripcion_fase || 'El motor no proporciono una descripcion para esta fase.';
        document.getElementById('indexacionInicio').textContent = formatearFecha(indexacion.iniciado_en);
        document.getElementById('indexacionFin').textContent = formatearFecha(indexacion.finalizado_en);

        const estadoVisual = {
            pendiente: ['pending', indexacion.estado || 'Pendiente'],
            ejecucion: ['running', indexacion.estado || 'En ejecucion'],
            completado: ['completed', indexacion.estado || 'Completado'],
            error: ['error', indexacion.estado || 'Error'],
            desconocido: ['unknown', indexacion.estado || 'Desconocido']
        }[indexacion.categoria_estado] || ['unknown', indexacion.estado || 'Desconocido'];
        actualizarBadge(document.getElementById('indexacionEstadoBadge'), estadoVisual[0], estadoVisual[1]);

        const progressWrapper = document.getElementById('indexacionProgressWrapper');
        const progress = document.getElementById('indexacionProgress');
        const tienePorcentaje = typeof indexacion.porcentaje === 'number';
        progressWrapper.classList.toggle('d-none', !tienePorcentaje);

        if (tienePorcentaje) {
            progress.style.width = indexacion.porcentaje + '%';
            progressWrapper.setAttribute('aria-valuenow', indexacion.porcentaje);
            document.getElementById('indexacionPorcentaje').textContent = indexacion.porcentaje + '%';
        } else {
            document.getElementById('indexacionPorcentaje').textContent = '';
        }

        document.getElementById('indexacionConteo').textContent = construirConteo(indexacion);
        renderErrores(indexacion.errores || []);
        renderFases(indexacion.fases || []);

        if (indexacion.terminal) {
            renderResultados(indexacion.resultados || {});
            mostrarMensaje(
                indexacion.categoria_estado === 'completado' ? 'success' : 'danger',
                indexacion.categoria_estado === 'completado'
                    ? 'La indexacion finalizo correctamente.'
                    : 'La indexacion finalizo con errores.'
            );
        }
    }

    function renderResultados(resultados) {
        const metricas = [
            ['Total de proveedores', 'total_proveedores', 'ri-team-line'],
            ['Proveedores nuevos', 'proveedores_nuevos', 'ri-user-add-line'],
            ['Reindexados', 'proveedores_reindexados', 'ri-refresh-line'],
            ['Metadatos actualizados', 'metadatos_actualizados', 'ri-edit-circle-line'],
            ['Sin modificaciones', 'proveedores_sin_modificaciones', 'ri-subtract-line'],
            ['Eliminados del indice', 'proveedores_eliminados', 'ri-delete-bin-line']
        ];
        const container = document.getElementById('metricasIndexacion');
        container.replaceChildren();

        metricas.forEach(function ([titulo, clave, icono]) {
            const column = document.createElement('div');
            const card = document.createElement('article');
            const label = document.createElement('span');
            const icon = document.createElement('i');
            const value = document.createElement('strong');

            column.className = 'col-sm-6 col-xl-4';
            card.className = 'motor-metric-card';
            icon.className = icono;
            label.append(icon, document.createTextNode(titulo));
            value.textContent = resultados[clave] ?? '—';
            card.append(label, value);
            column.appendChild(card);
            container.appendChild(column);
        });

        document.getElementById('resultadosIndexacion').classList.remove('d-none');
    }

    function renderFases(fases) {
        const section = document.getElementById('fasesIndexacion');
        const container = document.getElementById('listaFasesIndexacion');
        container.replaceChildren();

        if (!fases.length) {
            section.classList.add('d-none');
            return;
        }

        fases.forEach(function (fase) {
            const item = document.createElement('article');
            const dot = document.createElement('span');
            const content = document.createElement('div');
            const title = document.createElement('h6');
            const description = document.createElement('p');
            const meta = document.createElement('div');

            const datos = typeof fase === 'object' && fase !== null ? fase : { nombre: String(fase) };
            item.className = 'motor-timeline-item';
            dot.className = 'motor-timeline-dot';
            dot.innerHTML = '<i class="ri-check-line"></i>';
            content.className = 'motor-timeline-content';
            title.className = 'mb-1';
            description.className = 'text-muted mb-2';
            meta.className = 'motor-timeline-meta';
            title.textContent = datos.nombre || datos.fase || 'Fase';
            description.textContent = datos.descripcion || datos.detalle || 'Sin descripcion adicional.';
            meta.textContent = [datos.estado, formatearFecha(datos.iniciado_en || datos.fecha_inicio), formatearFecha(datos.finalizado_en || datos.fecha_fin)]
                .filter(function (valor) { return valor && valor !== '—'; })
                .join(' · ');
            content.append(title, description, meta);
            item.append(dot, content);
            container.appendChild(item);
        });

        section.classList.remove('d-none');
    }

    function renderErrores(errores) {
        const container = document.getElementById('indexacionErrores');

        if (!errores.length) {
            container.classList.add('d-none');
            container.textContent = '';
            return;
        }

        container.replaceChildren();
        const title = document.createElement('strong');
        const list = document.createElement('ul');
        title.textContent = 'Errores informados por el motor:';
        list.className = 'mb-0 mt-2';
        errores.forEach(function (error) {
            const item = document.createElement('li');
            item.textContent = typeof error === 'string' ? error : JSON.stringify(error);
            list.appendChild(item);
        });
        container.append(title, list);
        container.classList.remove('d-none');
    }

    function construirConteo(indexacion) {
        if (indexacion.proveedores_procesados === null && indexacion.total_proveedores === null) {
            return 'Progreso numerico no informado';
        }

        return (indexacion.proveedores_procesados ?? 0) + ' de ' + (indexacion.total_proveedores ?? '—') + ' proveedores procesados';
    }

    async function solicitarJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                ...(options.headers || {})
            }
        });
        const data = await response.json().catch(function () { return {}; });

        if (!response.ok || !data.ok) {
            const error = new Error(data.mensaje || 'No fue posible completar la solicitud.');
            error.status = response.status;
            error.payload = data;
            throw error;
        }

        return data;
    }

    function mostrarMensaje(tipo, mensaje) {
        const container = document.getElementById('motorMensaje');
        container.className = 'alert alert-' + tipo;
        container.textContent = mensaje;
        container.classList.remove('d-none');
    }

    function actualizarBadge(element, estado, texto) {
        if (!element) {
            return;
        }

        element.className = 'motor-status-pill is-' + estado;
        element.replaceChildren();
        element.append(document.createElement('span'), document.createTextNode(texto));
    }

    function setServiciosCargando(cargando) {
        document.querySelectorAll('[data-motor-service]').forEach(function (card) {
            card.classList.toggle('is-loading', cargando);
        });
    }

    function setButtonLoading(button, loading, text, icon) {
        if (!button) {
            return;
        }

        button.disabled = loading;
        button.innerHTML = loading
            ? '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' + text
            : '<i class="' + icon + ' align-bottom me-1"></i>' + text;
    }

    function actualizarBotonIndexacion(enCurso) {
        const button = document.getElementById('iniciarIndexacionMotor');

        if (!button) {
            return;
        }

        button.disabled = enCurso;
        button.innerHTML = enCurso
            ? '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Indexacion en curso'
            : '<i class="ri-play-circle-line align-bottom me-1"></i> Iniciar indexacion';
    }

    function formatearFecha(valor) {
        if (!valor) {
            return '—';
        }

        const fecha = new Date(valor);

        if (Number.isNaN(fecha.getTime())) {
            return String(valor);
        }

        return new Intl.DateTimeFormat('es-BO', {
            dateStyle: 'short',
            timeStyle: 'medium'
        }).format(fecha);
    }
});
