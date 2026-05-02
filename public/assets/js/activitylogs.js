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
        old: document.getElementById('activity-detail-old'),
        attributes: document.getElementById('activity-detail-attributes')
    };

    function formatJson(value, emptyMessage) {
        try {
            const parsed = JSON.parse(value || '{}');

            if (!parsed || Object.keys(parsed).length === 0) {
                return emptyMessage;
            }

            return JSON.stringify(parsed, null, 2);
        } catch (error) {
            return emptyMessage;
        }
    }

    botonesVerCambios.forEach(function (button) {
        button.addEventListener('click', function () {
            detailFields.id.textContent = button.dataset.id || '-';
            detailFields.log.textContent = button.dataset.log || '-';
            detailFields.evento.textContent = button.dataset.evento || '-';
            detailFields.descripcion.textContent = button.dataset.descripcion || '-';
            detailFields.usuario.textContent = button.dataset.usuario || '-';
            detailFields.subject.textContent = button.dataset.subject || '-';
            detailFields.fecha.textContent = button.dataset.fecha || '-';
            detailFields.old.textContent = formatJson(button.dataset.old, 'Sin cambios anteriores');
            detailFields.attributes.textContent = formatJson(button.dataset.attributes, 'Sin valores nuevos');
        });
    });
});
