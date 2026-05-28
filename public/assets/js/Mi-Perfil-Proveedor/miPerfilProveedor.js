document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('mi-perfil-proveedor-success-message');

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    if (typeof Swal !== 'undefined' && successMessageElement?.dataset.message) {
        Swal.fire({
            title: 'Exito',
            text: successMessageElement.dataset.message,
            icon: 'success',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#0ab39c'
        });
    }

    document.querySelectorAll('.js-mi-imagen-preview-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const preview = input.closest('form')?.querySelector('.js-mi-imagen-preview');
            const file = input.files?.[0];

            if (!preview || !file) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', function () {
                preview.src = reader.result;
                preview.classList.remove('d-none');
            });

            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('.js-confirm-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (typeof Swal === 'undefined') {
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: 'Estas seguro?',
                text: 'El registro seleccionado ya no estará disponible en tu perfil.',
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
