document.addEventListener('DOMContentLoaded', function () {
    const calendarElement = document.getElementById('miHorariosCalendar');
    const diaSemanaSelect = document.getElementById('miHorarioDiaSemana');

    if (!calendarElement || typeof FullCalendar === 'undefined') {
        return;
    }

    const horarios = JSON.parse(calendarElement.dataset.horarios || '[]');
    const dayMap = {
        1: 1,
        2: 2,
        3: 3,
        4: 4,
        5: 5,
        6: 6,
        7: 0
    };

    const toDateString = function (date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    const buildEvents = function (start, end) {
        const events = [];
        const cursor = new Date(start);

        cursor.setHours(0, 0, 0, 0);

        while (cursor < end) {
            horarios.forEach(function (horario) {
                if (cursor.getDay() !== dayMap[horario.dia_semana]) {
                    return;
                }

                const date = toDateString(cursor);
                const disponible = Boolean(horario.disponible);
                const title = disponible
                    ? `${horario.hora_inicio} - ${horario.hora_fin} Disponible`
                    : 'Dia no disponible';

                events.push({
                    id: `${horario.id}-${date}`,
                    title: title,
                    start: disponible ? `${date}T${horario.hora_inicio}:00` : date,
                    end: disponible ? `${date}T${horario.hora_fin}:00` : null,
                    allDay: !disponible,
                    classNames: [disponible ? 'mi-event-disponible' : 'mi-event-no-disponible'],
                    extendedProps: {
                        horarioId: horario.id,
                        rangoHorario: disponible ? `${horario.hora_inicio} - ${horario.hora_fin}` : '',
                        estadoTexto: disponible ? 'Disponible' : 'No disponible'
                    }
                });
            });

            cursor.setDate(cursor.getDate() + 1);
        }

        return events;
    };

    const calendar = new FullCalendar.Calendar(calendarElement, {
        locale: 'es',
        themeSystem: 'bootstrap',
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,
        nowIndicator: true,
        navLinks: true,
        dayMaxEvents: 3,
        headerToolbar: {
            left: 'today prev,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Dia',
            list: 'Agenda'
        },
        events: function (info, successCallback) {
            successCallback(buildEvents(info.start, info.end));
        },
        eventContent: function (arg) {
            const wrapper = document.createElement('div');
            wrapper.className = 'mi-calendar-event-content';

            if (arg.event.extendedProps.rangoHorario) {
                const time = document.createElement('span');
                time.className = 'mi-calendar-event-time';
                time.textContent = arg.event.extendedProps.rangoHorario;
                wrapper.appendChild(time);
            }

            const status = document.createElement('span');
            status.className = 'mi-calendar-event-status';
            status.textContent = arg.event.extendedProps.estadoTexto || arg.event.title;
            wrapper.appendChild(status);

            return { domNodes: [wrapper] };
        },
        dateClick: function (info) {
            if (!diaSemanaSelect) {
                return;
            }

            const selectedDay = new Date(info.dateStr + 'T00:00:00').getDay();
            const diaSemana = selectedDay === 0 ? 7 : selectedDay;

            diaSemanaSelect.value = String(diaSemana);
            diaSemanaSelect.dispatchEvent(new Event('change'));
        },
        eventClick: function (info) {
            const horarioId = info.event.extendedProps.horarioId;
            const horarioElement = document.getElementById(`mi-horario-${horarioId}`);

            if (!horarioElement) {
                return;
            }

            horarioElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            horarioElement.classList.remove('is-calendar-highlighted');
            void horarioElement.offsetWidth;
            horarioElement.classList.add('is-calendar-highlighted');

            window.setTimeout(function () {
                horarioElement.classList.remove('is-calendar-highlighted');
            }, 2600);
        }
    });

    calendar.render();
});

document.addEventListener('DOMContentLoaded', function () {
    const createWidget = document.getElementById('miHorarioCreateWidget');
    const openFormButton = document.getElementById('miHorarioOpenForm');
    const openEmptyFormButton = document.getElementById('miHorarioOpenEmptyForm');

    if (!createWidget || (!openFormButton && !openEmptyFormButton)) {
        return;
    }

    const openForm = function () {
        createWidget.classList.add('is-open');

        const firstInput = createWidget.querySelector('select, input');
        if (firstInput) {
            window.setTimeout(function () {
                firstInput.focus();
            }, 250);
        }
    };

    openFormButton?.addEventListener('click', openForm);
    openEmptyFormButton?.addEventListener('click', openForm);
});

document.addEventListener('DOMContentLoaded', function () {
    const horariosList = document.querySelector('.mi-horarios-habituales-list');

    if (!horariosList || typeof bootstrap === 'undefined') {
        return;
    }

    const horarioItems = Array.from(horariosList.querySelectorAll('.mi-horario-item'));
    const editCollapses = Array.from(horariosList.querySelectorAll('[id^="editar-horario-"]'));

    const mostrarTodosLosHorarios = function () {
        horarioItems.forEach(function (item) {
            item.classList.remove('is-hidden-by-edit');
        });
    };

    editCollapses.forEach(function (collapseElement) {
        collapseElement.addEventListener('show.bs.collapse', function () {
            const currentItem = collapseElement.closest('.mi-horario-item');

            editCollapses.forEach(function (otherCollapse) {
                if (otherCollapse === collapseElement || !otherCollapse.classList.contains('show')) {
                    return;
                }

                bootstrap.Collapse.getOrCreateInstance(otherCollapse, {
                    toggle: false
                }).hide();
            });

            horarioItems.forEach(function (item) {
                item.classList.toggle('is-hidden-by-edit', item !== currentItem);
            });
        });

        collapseElement.addEventListener('hidden.bs.collapse', function () {
            const hasOpenCollapse = editCollapses.some(function (otherCollapse) {
                return otherCollapse.classList.contains('show');
            });

            if (!hasOpenCollapse) {
                mostrarTodosLosHorarios();
            }
        });
    });
});
