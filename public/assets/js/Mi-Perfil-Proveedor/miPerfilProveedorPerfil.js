document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('.js-mi-perfil-cover-input');
    const preview = document.querySelector('.js-mi-perfil-cover-preview');
    const fileName = document.querySelector('.js-mi-perfil-file-name');
    const editPanel = document.getElementById('miPerfilEditPanel');
    const summaryPanel = document.getElementById('miPerfilResumenPanel');
    const editButtons = document.querySelectorAll('.js-mi-perfil-edit-toggle');
    const cancelButton = document.querySelector('.js-mi-perfil-edit-cancel');

    const showEditPanel = function () {
        editPanel?.classList.remove('d-none');
        summaryPanel?.classList.add('d-none');
        editPanel?.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    };

    const hideEditPanel = function () {
        editPanel?.classList.add('d-none');
        summaryPanel?.classList.remove('d-none');
    };

    editButtons.forEach(function (button) {
        button.addEventListener('click', showEditPanel);
    });

    cancelButton?.addEventListener('click', hideEditPanel);

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        if (fileName) {
            fileName.textContent = file.name;
        }

        const reader = new FileReader();

        reader.addEventListener('load', function () {
            preview.src = reader.result;
        });

        reader.readAsDataURL(file);
    });
});
