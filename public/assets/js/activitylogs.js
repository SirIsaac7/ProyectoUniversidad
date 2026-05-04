document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tabla-activitylogs');
    const botonesVerCambios = document.querySelectorAll('.js-ver-cambios');

    if (tabla && typeof $ !== 'undefined') {
        $('#tabla-activitylogs').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [[0, 'desc']],
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                zeroRecords: 'No se encontraron resultados',
                emptyTable: 'No hay datos disponibles en la tabla',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }

    if (!botonesVerCambios.length) {
        return;
    }

    const detailFields = {
        id: document.getElementById('activity-detail-id'),
        log: document.getElementById('activity-detail-log'),
        evento: document.getElementById('activity-detail-evento'),
        descripcion: document.getElementById('activity-detail-descripcion'),
        usuario: document.getElementById('activity-detail-usuario'),
        subject: document.getElementById('activity-detail-subject'),
        fecha: document.getElementById('activity-detail-fecha'),
        panel1Col: document.getElementById('activity-detail-panel-1-col'),
        panel2Col: document.getElementById('activity-detail-panel-2-col'),
        panel1Title: document.getElementById('activity-detail-panel-1-title'),
        panel2Title: document.getElementById('activity-detail-panel-2-title'),
        panel1Body: document.getElementById('activity-detail-panel-1-body'),
        panel2Body: document.getElementById('activity-detail-panel-2-body')
    };

    function parseJson(value) {
        try {
            return JSON.parse(value || '{}');
        } catch (error) {
            return {};
        }
    }

    function isEmptyObject(value) {
        return !value || Object.keys(value).length === 0;
    }

    function formatJson(value, emptyMessage) {
        if (isEmptyObject(value)) {
            return emptyMessage;
        }

        return JSON.stringify(value, null, 2);
    }

    function setEventoBadge(evento, classes) {
        detailFields.evento.innerHTML = `<span class="badge ${classes}">${evento}</span>`;
    }

    function showPanel1(title, content) {
        detailFields.panel1Col.classList.remove('d-none');
        detailFields.panel1Col.classList.remove('col-12');
        detailFields.panel1Col.classList.add('col-md-6');
        detailFields.panel1Title.textContent = title;
        detailFields.panel1Body.textContent = content;
    }

    function showPanel2(title, content) {
        detailFields.panel2Col.classList.remove('d-none');
        detailFields.panel2Col.classList.remove('col-12');
        detailFields.panel2Col.classList.add('col-md-6');
        detailFields.panel2Title.textContent = title;
        detailFields.panel2Body.textContent = content;
    }

    function hidePanel2AndExpandPanel1() {
        detailFields.panel2Col.classList.add('d-none');
        detailFields.panel1Col.classList.remove('col-md-6');
        detailFields.panel1Col.classList.add('col-12');
    }

    botonesVerCambios.forEach(function (button) {
        button.addEventListener('click', function () {
            const evento = button.dataset.evento || 'N/A';
            const eventoClasses = button.dataset.eventoClasses || 'bg-primary-subtle text-primary';
            const oldValues = parseJson(button.dataset.old);
            const attributesValues = parseJson(button.dataset.attributes);
            const propertiesValues = parseJson(button.dataset.properties);

            detailFields.id.textContent = button.dataset.id || '-';
            detailFields.log.textContent = button.dataset.log || '-';
            setEventoBadge(evento, eventoClasses);
            detailFields.descripcion.textContent = button.dataset.descripcion || '-';
            detailFields.usuario.textContent = button.dataset.usuario || '-';
            detailFields.subject.textContent = button.dataset.subject || '-';
            detailFields.fecha.textContent = button.dataset.fecha || '-';

            detailFields.panel2Col.classList.remove('d-none');
            detailFields.panel1Col.classList.remove('col-12');
            detailFields.panel1Col.classList.add('col-md-6');
            detailFields.panel2Col.classList.add('col-md-6');

            if (evento === 'created') {
                showPanel1('Datos registrados', formatJson(attributesValues, 'Sin datos registrados'));
                showPanel2('Propiedades del evento', formatJson(propertiesValues, 'Sin propiedades adicionales'));
                return;
            }

            if (evento === 'updated') {
                showPanel1('Valores anteriores', formatJson(oldValues, 'Sin cambios anteriores'));
                showPanel2('Valores nuevos', formatJson(attributesValues, 'Sin valores nuevos'));
                return;
            }

            if (evento === 'deleted') {
                const deletedData = !isEmptyObject(oldValues) ? oldValues : attributesValues;

                showPanel1('Datos eliminados', formatJson(deletedData, 'Sin datos eliminados'));
                hidePanel2AndExpandPanel1();
                return;
            }

            if (evento === 'login' || evento === 'logout') {
                showPanel1('Detalles de autenticacion', formatJson(propertiesValues, 'Sin detalles de autenticacion'));
                hidePanel2AndExpandPanel1();
                return;
            }

            const firstData = !isEmptyObject(attributesValues) ? attributesValues : propertiesValues;
            const secondData = oldValues;

            showPanel1('Detalles', formatJson(firstData, 'Sin detalles'));
            if (!isEmptyObject(secondData)) {
                showPanel2('Datos relacionados', formatJson(secondData, 'Sin datos relacionados'));
            } else {
                hidePanel2AndExpandPanel1();
            }
        });
    });
});
