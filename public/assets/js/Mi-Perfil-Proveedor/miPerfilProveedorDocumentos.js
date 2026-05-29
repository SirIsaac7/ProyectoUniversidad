document.addEventListener('DOMContentLoaded', function () {
    const panelButtons = document.querySelectorAll('.js-mi-documento-panel-toggle');
    const panels = document.querySelectorAll('.mi-documento-side-panel');
    const placeholder = document.getElementById('miDocumentoSidePlaceholder');
    const folderCards = Array.from(document.querySelectorAll('[data-folder-card]'));
    const folderPagination = document.getElementById('miDocumentosFolderPagination');
    const folderPageInfo = document.querySelector('[data-folder-page-info]');
    const documentRows = Array.from(document.querySelectorAll('[data-documento-row]'));
    const documentCount = document.getElementById('miDocumentosRegistradosCount');
    const emptyFilter = document.getElementById('miDocumentosEmptyFilter');
    const foldersPerPage = 8;
    let currentFolderPage = 1;
    let selectedFolderId = null;

    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (tooltipTrigger) {
            new bootstrap.Tooltip(tooltipTrigger);
        });
    }

    if (typeof Choices !== 'undefined') {
        document.querySelectorAll('.js-mi-documento-choices').forEach(function (select) {
            if (select.dataset.choicesInitialized === 'true') {
                return;
            }

            new Choices(select, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
                placeholder: true,
                searchPlaceholderValue: 'Buscar documento...',
                noResultsText: 'No se encontraron resultados',
                noChoicesText: 'No hay documentos disponibles'
            });

            select.dataset.choicesInitialized = 'true';
        });
    }

    document.querySelectorAll('.js-mi-documento-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const fileName = input.closest('.mi-documento-upload-zone')?.querySelector('.mi-documento-file-name');
            const file = input.files?.[0];

            if (!fileName) {
                return;
            }

            fileName.textContent = file ? file.name : 'Ningun archivo seleccionado';
        });
    });

    const renderFolderPage = function () {
        if (!folderCards.length) {
            return;
        }

        const totalPages = Math.ceil(folderCards.length / foldersPerPage);
        const start = (currentFolderPage - 1) * foldersPerPage;
        const end = start + foldersPerPage;

        folderCards.forEach(function (card, index) {
            card.classList.toggle('d-none', index < start || index >= end);
        });

        if (folderPagination) {
            folderPagination.classList.toggle('d-none', totalPages <= 1);
        }

        if (folderPageInfo) {
            folderPageInfo.textContent = `Pagina ${currentFolderPage} de ${totalPages}`;
        }

        document.querySelectorAll('[data-folder-page-action]').forEach(function (button) {
            const action = button.dataset.folderPageAction;
            button.disabled = (action === 'prev' && currentFolderPage === 1)
                || (action === 'next' && currentFolderPage === totalPages);
        });
    };

    document.querySelectorAll('[data-folder-page-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            const totalPages = Math.ceil(folderCards.length / foldersPerPage);

            if (button.dataset.folderPageAction === 'prev') {
                currentFolderPage = Math.max(1, currentFolderPage - 1);
            } else {
                currentFolderPage = Math.min(totalPages, currentFolderPage + 1);
            }

            renderFolderPage();
        });
    });

    renderFolderPage();

    const updateDocumentFilter = function () {
        let visibleRows = 0;

        documentRows.forEach(function (row) {
            const shouldShow = !selectedFolderId || row.dataset.tipoDocumentoId === selectedFolderId;

            row.classList.toggle('d-none', !shouldShow);

            if (shouldShow) {
                visibleRows += 1;
            }
        });

        if (documentCount) {
            documentCount.textContent = `${visibleRows} ${visibleRows === 1 ? 'documento' : 'documentos'}`;
        }

        emptyFilter?.classList.toggle('d-none', visibleRows > 0 || !selectedFolderId);
    };

    folderCards.forEach(function (folder) {
        folder.addEventListener('click', function () {
            const folderId = folder.dataset.tipoDocumentoId;
            const isSelected = selectedFolderId === folderId;

            selectedFolderId = isSelected ? null : folderId;

            folderCards.forEach(function (item) {
                item.classList.toggle('is-active', !isSelected && item === folder);
                item.classList.remove('is-folder-highlighted');
            });

            if (!isSelected) {
                folder.classList.add('is-folder-highlighted');

                window.setTimeout(function () {
                    folder.classList.remove('is-folder-highlighted');
                }, 2600);
            }

            updateDocumentFilter();
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
