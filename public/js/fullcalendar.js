document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Vue par défaut : mois
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        navLinks: true, // Permet de cliquer sur les jours/semaine pour changer de vue
        selectable: true, // Permet de sélectionner une plage (ex : pour création d'événement)
        selectMirror: true, // Affiche un aperçu de la sélection
        editable: true, // Permet de déplacer et redimensionner les événements
        dayMaxEvents: true, // Affiche un "+x événements" quand il y en a trop
        eventClick: function(info) {
            alert('Événement : ' + info.event.title + '\nDate : ' + info.event.start.toLocaleString());
        },
        events: [
            {
                title: 'Réunion projet',
                start: new Date().toISOString().slice(0,10), // Aujourd’hui
                color: '#378006' // Vert
            },
            {
                title: 'Conférence',
                start: new Date(new Date().setDate(new Date().getDate() + 3)).toISOString().slice(0,10),
                end: new Date(new Date().setDate(new Date().getDate() + 5)).toISOString().slice(0,10),
                color: '#ff5733' // Rouge-orangé
            },
            {
                title: 'Webinaire',
                start: new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().slice(0,10),
                color: '#337ab7' // Bleu
            }
        ]
    });

    calendar.render();
});
