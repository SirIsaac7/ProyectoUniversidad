document.addEventListener('DOMContentLoaded', function () {
    const successMessageElement = document.getElementById('calificaciones-success-message');

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

    inicializarCalificacionCliente();
    inicializarGraficaCalificacionesProveedor();
});

function inicializarGraficaCalificacionesProveedor() {
    inicializarGraficaDistribucionCalificaciones();
    inicializarGraficaAspectosCalificaciones();
}

function coloresCalificaciones() {
    return {
        textColor: getComputedStyle(document.documentElement).getPropertyValue('--vz-body-color').trim() || '#495057',
        gridColor: getComputedStyle(document.documentElement).getPropertyValue('--vz-border-color').trim() || 'rgba(148, 163, 184, .25)'
    };
}

function inicializarGraficaDistribucionCalificaciones() {
    const chartElement = document.getElementById('proveedorDistribucionCalificacionesChart');

    if (!chartElement || typeof ApexCharts === 'undefined') {
        return;
    }

    const series = JSON.parse(chartElement.dataset.series || '[]');
    const labels = JSON.parse(chartElement.dataset.labels || '[]');
    const total = Number(chartElement.dataset.total || 0);
    const colors = coloresCalificaciones();

    const chart = new ApexCharts(chartElement, {
        series: [{
            name: 'Resenas',
            data: series
        }],
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false },
            fontFamily: 'inherit',
            foreColor: colors.textColor
        },
        colors: ['#0ab39c', '#299cdb', '#f7b84b', '#f06548', '#405189'],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '42%',
                distributed: true
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (value, options) {
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                return value + ' (' + percentage + '%)';
            },
            offsetY: -20,
            style: {
                fontWeight: 700,
                colors: [colors.textColor]
            }
        },
        legend: { show: false },
        grid: {
            borderColor: colors.gridColor,
            strokeDashArray: 4
        },
        xaxis: {
            categories: labels,
            labels: {
                rotate: 0,
                trim: true
            }
        },
        yaxis: {
            min: 0,
            tickAmount: Math.min(Math.max(total, 1), 5),
            title: {
                text: 'Cantidad'
            },
            labels: {
                formatter: function (value) {
                    return Number.isInteger(value) ? value : '';
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return value + (value === 1 ? ' resena' : ' resenas');
                }
            }
        }
    });

    chart.render();
}

function inicializarGraficaAspectosCalificaciones() {
    const chartElement = document.getElementById('proveedorAspectosCalificacionesChart');

    if (!chartElement || typeof ApexCharts === 'undefined') {
        return;
    }

    const series = JSON.parse(chartElement.dataset.series || '[]');
    const labels = JSON.parse(chartElement.dataset.labels || '[]');
    const total = Number(chartElement.dataset.total || 0);
    const colors = coloresCalificaciones();

    const chart = new ApexCharts(chartElement, {
        series: series,
        labels: labels,
        chart: {
            type: 'donut',
            height: 300,
            toolbar: { show: false },
            fontFamily: 'inherit',
            foreColor: colors.textColor
        },
        colors: ['#405189', '#0ab39c', '#f7b84b', '#6950ff', '#299cdb', '#f06548'],
        stroke: {
            width: 2,
            colors: ['var(--vz-card-bg)']
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '64%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '13px',
                            color: colors.textColor
                        },
                        value: {
                            show: true,
                            fontSize: '26px',
                            fontWeight: 700,
                            color: colors.textColor,
                            formatter: function (value) {
                                return value;
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            color: colors.textColor,
                            formatter: function () {
                                return total;
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            show: true,
            position: 'right',
            formatter: function (seriesName, options) {
                const value = options.w.globals.series[options.seriesIndex];
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                return seriesName + ' - ' + value + ' (' + percentage + '%)';
            }
        },
        yaxis: {
            labels: { show: false }
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return value + (value === 1 ? ' valoracion' : ' valoraciones');
                }
            }
        }
    });

    chart.render();
}

function inicializarCalificacionCliente() {
    document.querySelectorAll('[data-calificacion-wizard]').forEach(function (form) {
        prepararWizardCalificacion(form);
    });
}

function prepararWizardCalificacion(form) {
    let step = 1;
    const maxStep = 3;
    const prevButton = form.querySelector('.js-wizard-prev');
    const nextButton = form.querySelector('.js-wizard-next');
    const submitButton = form.querySelector('.js-wizard-submit');

    actualizarWizardCalificacion(form, step);

    form.querySelectorAll('[data-star-group]').forEach(function (group) {
        const valueInput = group.parentElement.querySelector('.js-star-value') || group.previousElementSibling;

        group.querySelectorAll('.calificacion-star-button').forEach(function (button) {
            button.addEventListener('click', function () {
                const value = Number(button.dataset.value);

                if (!valueInput) {
                    return;
                }

                valueInput.value = String(value);
                group.querySelectorAll('.calificacion-star-button').forEach(function (star) {
                    star.classList.toggle('is-active', Number(star.dataset.value) <= value);
                });

                const error = group.closest('.calificacion-wizard-pane, .calificacion-aspect-card')?.querySelector('.js-star-error');
                error?.classList.add('d-none');
            });
        });
    });

    prevButton?.addEventListener('click', function () {
        if (step <= 1) {
            return;
        }

        step -= 1;
        actualizarWizardCalificacion(form, step);
    });

    nextButton?.addEventListener('click', function () {
        if (!validarPasoCalificacion(form, step)) {
            return;
        }

        if (step < maxStep) {
            step += 1;
            actualizarWizardCalificacion(form, step);
        }
    });
}

function actualizarWizardCalificacion(form, step) {
    form.querySelectorAll('[data-step]').forEach(function (pane) {
        pane.classList.toggle('is-active', Number(pane.dataset.step) === step);
    });

    form.querySelectorAll('[data-step-indicator]').forEach(function (indicator) {
        const indicatorStep = Number(indicator.dataset.stepIndicator);
        indicator.classList.toggle('is-active', indicatorStep === step);
        indicator.classList.toggle('is-complete', indicatorStep < step);
    });

    const prevButton = form.querySelector('.js-wizard-prev');
    const nextButton = form.querySelector('.js-wizard-next');
    const submitButton = form.querySelector('.js-wizard-submit');

    if (prevButton) {
        prevButton.disabled = step === 1;
    }

    nextButton?.classList.toggle('d-none', step === 3);
    submitButton?.classList.toggle('d-none', step !== 3);
}

function validarPasoCalificacion(form, step) {
    const pane = form.querySelector(`[data-step="${step}"]`);
    let valid = true;

    pane?.querySelectorAll('.js-star-value[required]').forEach(function (input) {
        const error = input.closest('.calificacion-wizard-pane, .calificacion-aspect-card')?.querySelector('.js-star-error');

        if (!input.value) {
            valid = false;
            error?.classList.remove('d-none');
        } else {
            error?.classList.add('d-none');
        }
    });

    return valid;
}

document.addEventListener('click', function (event) {
    const option = event.target.closest('.js-calificacion-cita-option');

    if (!option) {
        return;
    }

    const target = document.getElementById(option.dataset.target);
    const card = option.closest('.cliente-cita-calificacion-card');
    const shouldOpen = target?.classList.contains('d-none');

    document.querySelectorAll('.js-calificacion-cita-option').forEach(function (button) {
        button.classList.remove('is-active');
    });

    document.querySelectorAll('.cliente-cita-calificacion-card').forEach(function (item) {
        item.classList.remove('is-open');
    });

    document.querySelectorAll('.cliente-cita-calificacion-detail').forEach(function (detail) {
        detail.classList.add('d-none');
    });

    if (shouldOpen && target && card) {
        option.classList.add('is-active');
        card.classList.add('is-open');
        target.classList.remove('d-none');
        target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-cliente-calificacion-toggle');

    if (!button) {
        return;
    }

    const target = document.getElementById(button.dataset.target);
    const card = button.closest('.cliente-calificacion-card');

    if (!target || !card) {
        return;
    }

    const shouldOpen = target.classList.contains('d-none');

    document.querySelectorAll('.cliente-calificacion-detail').forEach(function (detail) {
        detail.classList.add('d-none');
    });

    document.querySelectorAll('.cliente-calificacion-card').forEach(function (item) {
        item.classList.remove('is-open');
    });

    target.classList.toggle('d-none', !shouldOpen);
    card.classList.toggle('is-open', shouldOpen);
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-proveedor-calificacion-toggle');

    if (!button) {
        return;
    }

    const target = document.getElementById(button.dataset.target);
    const card = button.closest('.proveedor-calificacion-card');

    if (!target || !card) {
        return;
    }

    const shouldOpen = target.classList.contains('d-none');

    document.querySelectorAll('.proveedor-calificacion-detail').forEach(function (detail) {
        detail.classList.add('d-none');
    });

    document.querySelectorAll('.proveedor-calificacion-card').forEach(function (item) {
        item.classList.remove('is-open');
    });

    target.classList.toggle('d-none', !shouldOpen);
    card.classList.toggle('is-open', shouldOpen);
});

document.addEventListener('click', function (event) {
    const toggleButton = event.target.closest('.js-toggle-calificacion-livewire');
    const deleteButton = event.target.closest('.js-delete-calificacion-livewire');

    if (toggleButton) {
        const calificacionId = Number(toggleButton.dataset.calificacionId);
        const estado = toggleButton.dataset.estado;

        if (!calificacionId || !estado) {
            return;
        }

        if (typeof Swal === 'undefined') {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarEstadoCalificacion', { calificacionId, estado });
            }
            return;
        }

        Swal.fire({
            title: estado === 'oculta' ? 'Ocultar calificación' : 'Mostrar calificación',
            text: estado === 'oculta'
                ? 'La reseña dejará de mostrarse públicamente.'
                : 'La reseña volverá a mostrarse públicamente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f7b84b',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarEstadoCalificacion', { calificacionId, estado });
            }
        });
    }

    if (deleteButton) {
        const calificacionId = Number(deleteButton.dataset.calificacionId);

        if (!calificacionId) {
            return;
        }

        if (typeof Swal === 'undefined') {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarEliminarCalificacion', { calificacionId });
            }
            return;
        }

        Swal.fire({
            title: 'Eliminar calificación',
            text: 'Se eliminará este registro de calificación.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && typeof Livewire !== 'undefined') {
                Livewire.dispatch('confirmarEliminarCalificacion', { calificacionId });
            }
        });
    }
});

document.addEventListener('livewire:init', function () {
    Livewire.on('calificacion-actualizada', function (event) {
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
