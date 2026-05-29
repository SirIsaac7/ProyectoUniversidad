document.addEventListener('DOMContentLoaded', function () {
    const panelButtons = document.querySelectorAll('.js-mi-portafolio-panel-toggle');
    const panels = document.querySelectorAll('.mi-portafolio-side-panel');
    const placeholder = document.getElementById('miPortafolioSidePlaceholder');
    const searchInput = document.getElementById('miPortafolioSearch');
    const cards = Array.from(document.querySelectorAll('[data-portafolio-card]'));
    const emptySearch = document.getElementById('miPortafolioEmptySearch');
    const tabButtons = Array.from(document.querySelectorAll('[data-portafolio-tab]'));
    const tabPanels = Array.from(document.querySelectorAll('[data-portafolio-tab-panel]'));
    let activeTab = 'activos';

    document.querySelectorAll('.js-mi-portafolio-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const label = input.closest('.mi-portafolio-upload-zone')?.querySelector('.mi-portafolio-file-name');
            const files = Array.from(input.files || []);

            if (!label) {
                return;
            }

            if (!files.length) {
                label.textContent = input.required ? 'Ninguna imagen seleccionada' : 'Sin nuevas imagenes';
                return;
            }

            label.textContent = files.length === 1
                ? files[0].name
                : `${files.length} imagenes seleccionadas`;
        });
    });

    const filterCards = function () {
        const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : '';
        let visibleCards = 0;

        cards.forEach(function (card) {
            const searchText = card.dataset.searchText || '';
            const cardStatus = card.dataset.portafolioStatus === 'inactivo' ? 'inactivos' : 'activos';
            const isInCurrentTab = cardStatus === activeTab;
            const shouldShow = isInCurrentTab && (searchTerm === '' || searchText.includes(searchTerm));

            card.classList.toggle('d-none', !shouldShow);

            if (shouldShow) {
                visibleCards += 1;
            }
        });

        emptySearch?.classList.toggle('d-none', visibleCards > 0 || searchTerm === '');
    };

    searchInput?.addEventListener('input', function () {
        filterCards();
    });

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeTab = button.dataset.portafolioTab || 'activos';

            tabButtons.forEach(function (item) {
                item.classList.toggle('is-active', item === button);
            });

            tabPanels.forEach(function (panel) {
                panel.classList.toggle('d-none', panel.dataset.portafolioTabPanel !== activeTab);
            });

            filterCards();
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
