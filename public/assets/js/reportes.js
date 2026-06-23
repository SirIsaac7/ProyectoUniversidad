(function () {
    'use strict';

    const tipoReporte = document.getElementById('tipo_reporte');
    const grupos = document.querySelectorAll('[data-reporte-opciones]');

    if (!tipoReporte || !grupos.length) {
        return;
    }

    const actualizarOpciones = () => {
        grupos.forEach((grupo) => {
            const visible = grupo.dataset.reporteOpciones === tipoReporte.value;
            grupo.classList.toggle('d-none', !visible);

            grupo.querySelectorAll('input, select, textarea').forEach((campo) => {
                campo.disabled = !visible;
            });
        });
    };

    tipoReporte.addEventListener('change', actualizarOpciones);
    actualizarOpciones();
})();
