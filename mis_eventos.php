<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<div style="padding:30px;text-align:center;">Debes iniciar sesión para ver tus eventos.</div>';
    exit();
}
include 'db.php';
$user_id = $_SESSION['user_id'];
// Obtener eventos
$events = $mysqli->query("SELECT * FROM calendar_events WHERE user_id = $user_id");
$calendar_events = [];
while ($event = $events->fetch_assoc()) {
    $calendar_events[] = $event;
}
?>
<div class="eventos-panel">
    <h2 style="color:var(--primary-color);margin-bottom:18px;">Mis eventos</h2>
    <form method="POST" action="agregar_evento.php" class="event-form" style="margin-bottom:18px;">
        <input type="text" name="event_title" placeholder="Título del evento" required>
        <input type="datetime-local" name="event_date" required>
        <select name="event_color" required>
            <option value="#7C9A92">Verde</option>
            <option value="#E57373">Rojo</option>
            <option value="#64B5F6">Azul</option>
            <option value="#FFB74D">Naranja</option>
            <option value="#BA68C8">Morado</option>
        </select>
        <button type="submit">Agregar Evento</button>
    </form>
    <div id="calendar" style="background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:18px;max-width:100%;overflow-x:auto;"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        height: 600,
        contentHeight: 600,
        aspectRatio: 1.7,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($calendar_events); ?>,
        eventClick: function(info) {
            alert('Evento: ' + info.event.title + '\nFecha: ' + new Date(info.event.start).toLocaleString('es-CO'));
        }
    });
    calendar.render();
});
</script>
<style>
.eventos-panel {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    background: var(--accent-color);
    border-radius: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    padding: 32px 28px;
}
#calendar {
    min-width: 320px;
    min-height: 400px;
    width: 100%;
    margin: 0 auto;
}
</style> 