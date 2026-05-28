document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('mi-perfil-proveedor-success-message');
    const radioSlider = document.querySelector('.js-mi-radio-slider');
    const radioInput = document.getElementById('radio_cobertura_km');
    const radioValue = document.getElementById('miRadioCoberturaValue');

    const showInfo = function (title, text) {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'info',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#405189'
        });
    };

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

    radioSlider?.addEventListener('input', function () {
        if (radioInput) {
            radioInput.value = radioSlider.value;
        }

        if (radioValue) {
            radioValue.textContent = radioSlider.value;
        }
    });

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

    document.querySelector('.js-mi-ubicacion-actual')?.addEventListener('click', function () {
        if (!navigator.geolocation) {
            showInfo('Ubicacion no disponible', 'Tu navegador no permite obtener la ubicacion actual.');
            return;
        }

        navigator.geolocation.getCurrentPosition(function (position) {
            const latitudInput = document.getElementById('latitud');
            const longitudInput = document.getElementById('longitud');

            if (latitudInput) {
                latitudInput.value = position.coords.latitude.toFixed(7);
            }

            if (longitudInput) {
                longitudInput.value = position.coords.longitude.toFixed(7);
            }
        }, function () {
            showInfo('Ubicacion no disponible', 'Activa el permiso de ubicacion del navegador o ingresa las coordenadas manualmente.');
        }, {
            enableHighAccuracy: true,
            timeout: 8000,
            maximumAge: 60000
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
