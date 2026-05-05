document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tabla-permisos');

    if (tabla && typeof $ !== 'undefined') {
        $('#tabla-permisos').DataTable({
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

        const successMessage = tabla.dataset.successMessage;

        if (successMessage && typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Exito',
                text: successMessage,
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#0ab39c'
            });
        }
    }

    document.querySelectorAll('.form-delete-permiso').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            const nombre = button?.dataset.permisoNombre || 'este permiso';

            if (typeof Swal === 'undefined') {
                form.submit();
                return;
            }

            Swal.fire({
                title: 'Estas seguro?',
                text: `Se procedera a eliminar ${nombre}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
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
});
