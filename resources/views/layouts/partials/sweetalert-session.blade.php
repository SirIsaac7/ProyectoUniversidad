@php
    $sweetAlertSession = null;

    if (session()->has('error')) {
        $sweetAlertSession = [
            'title' => 'Error',
            'text' => session('error'),
            'icon' => 'error',
            'confirmButtonColor' => '#f06548',
        ];
    } elseif (session()->has('warning')) {
        $sweetAlertSession = [
            'title' => 'Atencion',
            'text' => session('warning'),
            'icon' => 'warning',
            'confirmButtonColor' => '#f7b84b',
        ];
    } elseif (session()->has('success')) {
        $sweetAlertSession = [
            'title' => 'Exito',
            'text' => session('success'),
            'icon' => 'success',
            'confirmButtonColor' => '#0ab39c',
        ];
    } elseif (session()->has('info')) {
        $sweetAlertSession = [
            'title' => 'Informacion',
            'text' => session('info'),
            'icon' => 'info',
            'confirmButtonColor' => '#299cdb',
        ];
    }
@endphp

@if ($sweetAlertSession)
<script>
    (function () {
        const sweetAlertSession = @json($sweetAlertSession);

        document
            .querySelectorAll('[id$="-success-message"], [id$="-error-message"], [data-success-message], [data-error-message]')
            .forEach(function (element) {
                element.removeAttribute('data-success-message');
                element.removeAttribute('data-error-message');

                if (element.classList.contains('d-none')) {
                    element.remove();
                }
            });

        if (typeof Swal === 'undefined') {
            return;
        }

        Swal.fire({
            title: sweetAlertSession.title,
            text: sweetAlertSession.text,
            icon: sweetAlertSession.icon,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: sweetAlertSession.confirmButtonColor
        });
    })();
</script>
@endif
