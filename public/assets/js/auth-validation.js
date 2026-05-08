document.addEventListener('DOMContentLoaded', function () {
    const updatePasswordFeedbacks = function (context) {
        context.querySelectorAll('.js-auth-password-feedback').forEach(function (feedback) {
            const input = document.getElementById(feedback.dataset.passwordFeedbackFor);
            const form = input?.closest('form');
            const shouldShowError = form?.classList.contains('was-validated') && !input.checkValidity();

            feedback.classList.toggle('d-block', Boolean(shouldShowError));
        });
    };

    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
            updatePasswordFeedbacks(form);
        });

        form.querySelectorAll('.password-input').forEach(function (input) {
            input.addEventListener('input', function () {
                updatePasswordFeedbacks(form);
            });
        });
    });
});
