document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('mi-perfil-proveedor-success-message');
    const radioSlider = document.querySelector('.js-mi-radio-slider');
    const radioInput = document.getElementById('radio_cobertura_km');
    const radioValue = document.getElementById('miRadioCoberturaValue');
    const datosContainer = document.getElementById('miPerfilProveedorDatos');
    const seccionesContainer = document.getElementById('miPerfilProveedorSecciones');
    const secciones = ['mis-especialidades', 'mis-horarios', 'mi-ubicacion', 'mi-portafolio', 'mis-documentos'];

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

    const marcarEnlaceActivo = function (section) {
        document.querySelectorAll('.js-mi-perfil-sidebar-link').forEach(function (link) {
            const linkSection = link.dataset.miPerfilSection || '';

            link.classList.toggle('active', linkSection === section);
        });
    };

    const ocultarSecciones = function () {
        datosContainer?.classList.remove('d-none');
        seccionesContainer?.classList.add('d-none');
        seccionesContainer?.classList.remove('col-xl-12');
        seccionesContainer?.classList.add('col-xl-8');

        secciones.forEach(function (sectionId) {
            document.getElementById(sectionId)?.classList.remove('show', 'active');
        });

        marcarEnlaceActivo('');
    };

    const mostrarSeccion = function (section) {
        datosContainer?.classList.add('d-none');
        seccionesContainer?.classList.remove('d-none');
        seccionesContainer?.classList.remove('col-xl-8');
        seccionesContainer?.classList.add('col-xl-12');

        secciones.forEach(function (sectionId) {
            const pane = document.getElementById(sectionId);

            pane?.classList.toggle('show', sectionId === section);
            pane?.classList.toggle('active', sectionId === section);
        });

        marcarEnlaceActivo(section);
    };

    const activarSeccionActual = function () {
        const section = window.location.hash.replace('#', '');

        if (!section) {
            ocultarSecciones();
            return;
        }

        if (secciones.includes(section)) {
            mostrarSeccion(section);
        }
    };

    activarSeccionActual();
    window.addEventListener('hashchange', activarSeccionActual);

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
        radioInput.value = radioSlider.value;
        radioValue.textContent = radioSlider.value;
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
            document.getElementById('latitud').value = position.coords.latitude.toFixed(7);
            document.getElementById('longitud').value = position.coords.longitude.toFixed(7);
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
                text: 'Esta accion retirara el registro de tu perfil, pero no lo borrara definitivamente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, retirar',
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
