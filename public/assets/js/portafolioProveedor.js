document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('portafolio-proveedor-success-message');
    const errorMessageElement = document.getElementById('portafolio-proveedor-error-message');
    const perfilProveedorSelect = document.querySelector('.js-perfil-proveedor-select');
    const perfilProveedorFeedback = document.querySelector('.js-perfil-proveedor-feedback');
    const wizardForms = document.querySelectorAll('.js-portafolio-wizard');
    const imagenesFeedback = document.querySelector('.js-imagenes-feedback');

    const updateSelectValidation = function (select, feedback) {
        if (!select || !feedback) {
            return;
        }

        const shouldShowError = select.closest('form')?.classList.contains('was-validated')
            && !select.checkValidity();

        feedback.classList.toggle('d-block', shouldShowError);
        select.classList.toggle('is-invalid', shouldShowError);
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const hasPortfolioImages = form.dataset.requireImages === 'true'
                ? validatePortfolioImages(form)
                : true;

            if (!form.checkValidity() || !hasPortfolioImages) {
                event.preventDefault();
                event.stopPropagation();

                const firstInvalid = form.querySelector(':invalid');
                const invalidPane = firstInvalid?.closest('.tab-pane');

                if (!hasPortfolioImages) {
                    const imagesTab = form.querySelector('[data-bs-target="#portafolio-imagenes"]');
                    if (imagesTab) {
                        imagesTab.disabled = false;
                        imagesTab.click();
                    }
                } else if (invalidPane) {
                    const tabButton = form.querySelector(`[data-bs-target="#${invalidPane.id}"]`);
                    tabButton?.click();
                }
            }

            form.classList.add('was-validated');
            updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);

            if (form.checkValidity() && hasPortfolioImages) {
                form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    button.disabled = true;
                    button.dataset.originalText = button.innerHTML;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                });
            }
        });
    });

    const updateWizardSteps = function (form, activeButton) {
        const stepButtons = Array.from(form.querySelectorAll('.custom-nav [data-bs-toggle="pill"]'));
        const activeIndex = stepButtons.indexOf(activeButton);

        stepButtons.forEach(function (button, index) {
            button.classList.toggle('done', index < activeIndex);
            button.disabled = index > activeIndex;
        });

        const progressBarId = activeButton.dataset.progressbar;
        const progressWrapper = progressBarId ? document.getElementById(progressBarId) : null;
        const progressBar = progressWrapper?.querySelector('.progress-bar');

        if (progressBar && stepButtons.length > 1) {
            const progressValue = (activeIndex / (stepButtons.length - 1)) * 100;
            progressBar.style.width = `${progressValue}%`;
            progressBar.setAttribute('aria-valuenow', String(progressValue));
        }
    };

    const validateActiveStep = function (form) {
        const activePane = form.querySelector('.tab-pane.show.active');

        if (!activePane) {
            return true;
        }

        const controls = Array.from(activePane.querySelectorAll('input, select, textarea'))
            .filter(function (control) {
                return !control.disabled && control.willValidate;
            });

        const isValid = controls.every(function (control) {
            return control.checkValidity();
        });

        const imagesAreValid = activePane.id === 'portafolio-imagenes' && form.dataset.requireImages === 'true'
            ? validatePortfolioImages(form)
            : true;
        const stepIsValid = isValid && imagesAreValid;

        form.classList.toggle('was-validated', !stepIsValid);
        updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);

        return stepIsValid;
    };

    const validatePortfolioImages = function (form) {
        const imageInputs = Array.from(form.querySelectorAll('.js-portafolio-imagen-input'));
        const hasImage = imageInputs.some(function (input) {
            return input.files && input.files.length > 0;
        });

        imagenesFeedback?.classList.toggle('d-block', !hasImage);
        imageInputs.forEach(function (input) {
            input.classList.toggle('is-invalid', !hasImage);
        });

        return hasImage;
    };

    wizardForms.forEach(function (form) {
        form.querySelectorAll('.nexttab').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!validateActiveStep(form)) {
                    return;
                }

                const nextTab = document.getElementById(button.dataset.nexttab);

                if (nextTab) {
                    form.classList.remove('was-validated');
                    nextTab.disabled = false;
                    nextTab.click();
                }
            });
        });

        form.querySelectorAll('.previestab').forEach(function (button) {
            button.addEventListener('click', function () {
                const previousTab = document.getElementById(button.dataset.previous);

                if (previousTab) {
                    form.classList.remove('was-validated');
                    previousTab.disabled = false;
                    previousTab.click();
                }
            });
        });

        form.querySelectorAll('.custom-nav [data-bs-toggle="pill"]').forEach(function (button) {
            button.addEventListener('show.bs.tab', function (event) {
                if (button.disabled) {
                    event.preventDefault();
                }
            });

            button.addEventListener('shown.bs.tab', function () {
                updateWizardSteps(form, button);
            });
        });

        const activeButton = form.querySelector('.custom-nav [data-bs-toggle="pill"].active');

        if (activeButton) {
            updateWizardSteps(form, activeButton);
        }
    });

    perfilProveedorSelect?.addEventListener('change', function () {
        updateSelectValidation(perfilProveedorSelect, perfilProveedorFeedback);
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
            title: 'Error',
            text: errorMessageElement.dataset.message,
            icon: 'error',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#f06548'
        });
    }

    if (perfilProveedorSelect && typeof Choices !== 'undefined') {
        new Choices(perfilProveedorSelect, {
            searchEnabled: true,
            searchPlaceholderValue: 'Buscar proveedor',
            placeholder: true,
            placeholderValue: 'Selecciona un proveedor',
            shouldSort: false,
            itemSelectText: '',
            noResultsText: 'No se encontraron resultados',
            noChoicesText: 'No hay proveedores disponibles'
        });
    }

    document.querySelectorAll('.js-imagen-titulo-custom-switch').forEach(function (switchElement) {
        const card = switchElement.closest('.js-portafolio-imagen-card');
        const titleInput = card?.querySelector('.js-imagen-titulo-input');
        const titleBadge = card?.querySelector('.js-imagen-titulo-badge');

        const updateTitleBadge = function (isCustom) {
            if (!titleBadge) {
                return;
            }

            titleBadge.textContent = isCustom
                ? titleBadge.dataset.customInfo
                : titleBadge.dataset.defaultInfo;
            titleBadge.classList.toggle('bg-warning-subtle', isCustom);
            titleBadge.classList.toggle('text-warning', isCustom);
            titleBadge.classList.toggle('bg-info-subtle', !isCustom);
            titleBadge.classList.toggle('text-info', !isCustom);
        };

        if (!titleInput) {
            return;
        }

        titleInput.readOnly = !switchElement.checked;
        updateTitleBadge(switchElement.checked);

        switchElement.addEventListener('change', function () {
            if (!switchElement.checked) {
                titleInput.value = titleInput.dataset.defaultTitle || titleInput.value;
                titleInput.readOnly = true;
                updateTitleBadge(false);
                return;
            }

            if (typeof Swal === 'undefined') {
                titleInput.readOnly = false;
                updateTitleBadge(true);
                titleInput.focus();
                return;
            }

            Swal.fire({
                title: 'Activar personalizacion?',
                text: 'Podras cambiar el titulo por uno propio para esta imagen.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, personalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0ab39c',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    titleInput.readOnly = false;
                    updateTitleBadge(true);
                    titleInput.focus();
                    titleInput.select();
                    return;
                }

                switchElement.checked = false;
                titleInput.readOnly = true;
                titleInput.value = titleInput.dataset.defaultTitle || titleInput.value;
                updateTitleBadge(false);
            });
        });
    });

    document.querySelectorAll('.js-portafolio-imagen-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const form = input.closest('form');
            const card = input.closest('.js-portafolio-imagen-card');
            const preview = card?.querySelector('.js-portafolio-imagen-preview');
            const file = input.files?.[0];

            if (form) {
                validatePortfolioImages(form);
            }

            if (!preview || !file) {
                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', function () {
                preview.src = reader.result;
            });

            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('.js-portafolio-imagenes-sortable').forEach(function (container) {
        if (typeof Sortable === 'undefined') {
            return;
        }

        new Sortable(container, {
            animation: 180,
            handle: '.js-portafolio-imagen-handle',
            draggable: '.js-portafolio-imagen-card',
            ghostClass: 'portafolio-imagen-card-ghost',
            chosenClass: 'portafolio-imagen-card-chosen'
        });
    });
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-toggle-portafolio-proveedor-livewire');

    if (!button) {
        return;
    }

    const portafolioProveedorId = Number(button.dataset.portafolioProveedorId);
    const titulo = button.dataset.titulo || 'este trabajo';
    const accion = button.dataset.accion || 'cambiar el estado de';

    if (!portafolioProveedorId) {
        return;
    }

    if (typeof Swal === 'undefined') {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoPortafolioProveedor', { portafolioProveedorId: portafolioProveedorId });
        }
        return;
    }

    Swal.fire({
        title: 'Estas seguro?',
        text: `Se procedera a ${accion} ${titulo}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f06548',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed && typeof Livewire !== 'undefined') {
            Livewire.dispatch('confirmarCambioEstadoPortafolioProveedor', { portafolioProveedorId: portafolioProveedorId });
        }
    });
});

document.addEventListener('livewire:init', function () {
    Livewire.on('portafolio-proveedor-estado-cambiado', function (event) {
        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: 'Exito',
            text: event.message,
            icon: 'success',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#0ab39c'
        });
    });
});
