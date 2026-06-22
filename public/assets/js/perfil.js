document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('perfil-success-message');
    const errorMessageElement = document.getElementById('perfil-error-message');

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

    if (typeof Swal !== 'undefined' && errorMessageElement?.dataset.message) {
        Swal.fire({
            title: 'Atencion',
            text: errorMessageElement.dataset.message,
            icon: 'warning',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#f7b84b'
        });
    }
});
