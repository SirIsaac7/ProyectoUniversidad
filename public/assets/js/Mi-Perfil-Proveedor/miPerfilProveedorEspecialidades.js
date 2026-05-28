document.addEventListener('DOMContentLoaded', function () {
    const panelButtons = document.querySelectorAll('.js-mi-especialidad-panel-toggle');
    const panels = document.querySelectorAll('.mi-especialidad-side-panel');
    const placeholder = document.getElementById('miEspecialidadSidePlaceholder');
    const searchInput = document.getElementById('miEspecialidadSearch');
    const especialidadCards = document.querySelectorAll('.mi-especialidad-card');

    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('.js-mi-especialidad-choices').forEach(function (select) {
            if (select.dataset.choicesInitialized === 'true') {
                return;
            }

            new Choices(select, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
                placeholder: true,
                searchPlaceholderValue: 'Buscar especialidad...',
                noResultsText: 'No se encontraron resultados',
                noChoicesText: 'No hay opciones disponibles'
            });

            select.dataset.choicesInitialized = 'true';
        });
    }

    searchInput?.addEventListener('input', function () {
        const searchTerm = searchInput.value.trim().toLowerCase();

        especialidadCards.forEach(function (card) {
            const searchText = card.dataset.searchText || '';
            card.classList.toggle('d-none', searchTerm !== '' && !searchText.includes(searchTerm));
        });
    });

    if (!panelButtons.length || !panels.length) {
        return;
    }

    const closePanels = function () {
        panels.forEach(function (panel) {
            panel.classList.add('d-none');
        });

        panelButtons.forEach(function (button) {
            button.classList.remove('is-active');
        });

        placeholder?.classList.remove('d-none');
    };

    panelButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.dataset.panelTarget;
            const targetPanel = document.getElementById(targetId);
            const isOpen = targetPanel && !targetPanel.classList.contains('d-none');

            closePanels();

            if (!targetPanel || isOpen) {
                return;
            }

            targetPanel.classList.remove('d-none');
            button.classList.add('is-active');
            placeholder?.classList.add('d-none');

            targetPanel.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        });
    });
});
