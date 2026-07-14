function scrollToCalendarSection(id) {
    const target = document.getElementById(id);

    if (! target) {
        return;
    }

    window.requestAnimationFrame(() => {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function initCalendarScroll() {
    if (! document.getElementById('calendar-due-dates') && ! document.getElementById('calendar-sessions')) {
        return;
    }

    const hash = window.location.hash.replace('#', '');

    if (hash === 'calendar-due-dates' || hash === 'calendar-sessions') {
        scrollToCalendarSection(hash);

        return;
    }

    const params = new URLSearchParams(window.location.search);

    if (params.has('due_date')) {
        scrollToCalendarSection('calendar-due-dates');
    } else if (params.has('session_date')) {
        scrollToCalendarSection('calendar-sessions');
    }
}

document.addEventListener('DOMContentLoaded', initCalendarScroll);
