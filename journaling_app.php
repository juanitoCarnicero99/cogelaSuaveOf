<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

// Obtener datos del usuario
$user_id = $_SESSION['user_id'];
$user_result = $mysqli->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $user_result->fetch_assoc();

if (!$user) {
    echo '<div style="color: red; text-align: center; margin-top: 100px; font-size: 1.3em;">Error: El usuario no existe en la base de datos. Por favor, vuelve a iniciar sesión o regístrate de nuevo.</div>';
    exit();
}

// Obtener las entradas del diario del usuario
$entries = $mysqli->query("SELECT * FROM journal_entries WHERE user_id=" . $_SESSION['user_id'] . " ORDER BY created_at DESC");

// Obtener los eventos del calendario
$events = $mysqli->query("SELECT * FROM calendar_events WHERE user_id=" . $_SESSION['user_id']);
$calendar_events = [];
while ($event = $events->fetch_assoc()) {
    $calendar_events[] = $event;
}

// Obtener las entradas de eventos
$event_entries = $mysqli->query("SELECT * FROM event_entries WHERE user_id=" . $_SESSION['user_id']);
$event_entries_arr = [];
while ($ee = $event_entries->fetch_assoc()) {
    $event_entries_arr[] = $ee;
}

// Preparar los días a marcar en el calendario
$calendar_marks = [];
$journal_days = [];
$event_days = [];
$both_days = [];
$tooltips = [];

// Marcar días con entradas de diario
foreach ($entries as $entry) {
    $date = date('Y-m-d', strtotime($entry['created_at']));
    $journal_days[$date][] = $entry['entry'];
}
// Marcar días con eventos
foreach ($calendar_events as $ev) {
    $date = date('Y-m-d', strtotime($ev['event_date']));
    $event_days[$date][] = $ev['title'];
}
// Marcar días con entradas de eventos
foreach ($event_entries_arr as $ee) {
    $date = $ee['fecha'];
    $event_days[$date][] = $ee['nombre'];
}
// Determinar días con ambos
foreach ($journal_days as $date => $arr) {
    if (isset($event_days[$date])) {
        $both_days[$date] = true;
    }
}
// Preparar eventos para el calendario
foreach ($journal_days as $date => $arr) {
    if (isset($both_days[$date])) {
        $calendar_marks[] = [
            'start' => $date,
            'display' => 'background',
            'color' => '#BA68C8', // Morado
            'title' => 'Entrada de diario y evento',
            'description' => 'Entradas de diario: ' . implode('; ', $arr) . (isset($event_days[$date]) ? '\nEventos: ' . implode('; ', $event_days[$date]) : '')
        ];
    } else {
        $calendar_marks[] = [
            'start' => $date,
            'display' => 'background',
            'color' => '#64B5F6', // Azul
            'title' => 'Entrada de diario',
            'description' => 'Entradas de diario: ' . implode('; ', $arr)
        ];
    }
}
foreach ($event_days as $date => $arr) {
    if (!isset($both_days[$date])) {
        $calendar_marks[] = [
            'start' => $date,
            'display' => 'background',
            'color' => '#FF9900', // Naranja
            'title' => 'Evento',
            'description' => 'Eventos: ' . implode('; ', $arr)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Diario Personal - Cogela Suave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
</head>
<body>
    <div class="topbar">
        <div class="app-title">Cogela Suave</div>
        <div class="motivational-phrase" id="motivationalPhrase">¡Hoy es un buen día para cuidar de ti!</div>
    </div>
    <div class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-username"><?php echo htmlspecialchars($user['apodo'] ?? ''); ?></span>
        </div>
        <nav class="sidebar-nav">
            <a href="journaling_app.php" class="nav-item active">
                <i class="fas fa-book"></i>
                <span>Mi Diario</span>
            </a>
            <a href="#" class="nav-item" id="sidebarEntriesBtn" onclick="showEntries()">
                <i class="fas fa-list"></i>
                <span>Mis Entradas</span>
            </a>
            <a href="#" class="nav-item" id="sidebarMyEventsBtn">
                <i class="fas fa-calendar-alt"></i>
                <span>Mis eventos</span>
            </a>
            <a href="encontrar_personas.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Encontrar Personas</span>
            </a>
            <a href="edit_profile.php" class="nav-item" title="Editar Perfil">
                <i class="fas fa-user-edit"></i>
                <span>Editar Perfil</span>
            </a>
            <a href="index.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container journal-container">
            <div id="diario-section" class="journal-section">
                <h2>Mi Diario Personal</h2>
                <form method="POST" action="agregar_entrada_diario.php" class="journal-form">
                    <textarea name="entry" placeholder="¿Cómo te sientes hoy? Escribe tus pensamientos aquí..." required></textarea>
                    <button type="submit">Guardar Entrada</button>
                </form>
            </div>
            <div id="eventos-section" class="calendar-section" style="display:none;">
                <h2>Mis Eventos</h2>
                <div class="event-form">
                    <form method="POST" action="agregar_evento.php" class="event-entry-form">
                        <h4>Agregar Nuevo Evento</h4>
                        <input type="text" name="title" placeholder="Título del evento" required>
                        <input type="date" name="event_date" required>
                        <input type="time" name="event_time" required>
                        <input type="color" name="event_color" value="#FF9900">
                        <textarea name="description" placeholder="Descripción del evento"></textarea>
                        <button type="submit">Agregar Evento</button>
                    </form>
                </div>
                <div id="calendar"></div>
                <button class="consultar-eventos-btn" id="consultarEventosBtn">Ver Todos los Eventos</button>
            </div>
        </div>
    </div>

    <!-- Modal para detalles de evento -->
    <div id="eventModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h3 id="modalTitle"></h3>
            <p><strong>Fecha:</strong> <span id="modalDate"></span></p>
            <p><strong>Descripción:</strong> <span id="modalDescription"></span></p>
        </div>
    </div>

    <!-- Modal para lista de eventos -->
    <div id="eventListModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeListModal">&times;</span>
            <h3>Todos los eventos</h3>
            <div id="eventListContent"></div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar evento -->
    <div id="confirmDeleteModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:350px;text-align:center;">
            <h3 style="color:var(--primary-color);margin-bottom:18px;">¿Estás seguro de que deseas eliminar este evento?</h3>
            <form id="deleteEventForm" method="POST" action="eliminar_evento.php">
                <input type="hidden" name="delete_event_id" id="delete_event_id_modal">
                <button type="submit" class="delete-btn-modal">Eliminar</button>
                <button type="button" class="cancel-btn-modal" id="cancelDeleteBtn">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para entradas anteriores -->
    <div id="entriesModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:500px;">
            <span class="close" id="closeEntriesModal">&times;</span>
            <div id="entriesModalContent" style="max-height:400px;overflow-y:auto;"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar el calendario
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: <?php echo json_encode($calendar_marks); ?>,
                eventDisplay: 'background',
                eventMouseEnter: function(info) {
                    if (info.event.extendedProps.description) {
                        var tooltip = document.createElement('div');
                        tooltip.className = 'fc-tooltip';
                        tooltip.innerHTML = '<strong>' + info.event.title + '</strong><br>' + info.event.extendedProps.description.replace(/\n/g, '<br>');
                        document.body.appendChild(tooltip);
                        tooltip.style.position = 'absolute';
                        tooltip.style.zIndex = 9999;
                        tooltip.style.background = '#fff';
                        tooltip.style.color = '#222';
                        tooltip.style.padding = '10px 16px';
                        tooltip.style.borderRadius = '8px';
                        tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
                        tooltip.style.top = (info.jsEvent.pageY + 10) + 'px';
                        tooltip.style.left = (info.jsEvent.pageX + 10) + 'px';
                        info.el._tooltip = tooltip;
                    }
                },
                eventMouseLeave: function(info) {
                    if (info.el._tooltip) {
                        document.body.removeChild(info.el._tooltip);
                        info.el._tooltip = null;
                    }
                },
                eventClick: function(info) {
                    document.getElementById('modalTitle').textContent = info.event.title;
                    document.getElementById('modalDate').textContent = new Date(info.event.start).toLocaleString('es-ES');
                    document.getElementById('modalDescription').textContent = info.event.extendedProps.description || 'Sin descripción';
                    document.getElementById('eventModal').style.display = 'block';
                }
            });
            calendar.render();

            // Manejar el botón de Mis Eventos
            document.getElementById('sidebarMyEventsBtn').onclick = function(e) {
                e.preventDefault();
                document.getElementById('diario-section').style.display = 'none';
                document.getElementById('eventos-section').style.display = 'block';
                document.querySelectorAll('a.nav-item').forEach(function(a) {
                    a.classList.remove('active');
                });
                this.classList.add('active');
                calendar.render();
            };

            // Manejar el botón de Ver Todos los Eventos
            document.getElementById('consultarEventosBtn').onclick = function() {
                var events = <?php echo json_encode($calendar_events); ?>;
                if (events.length === 0) {
                    document.getElementById('eventListContent').innerHTML = '<p>No hay eventos registrados.</p>';
                } else {
                    var html = '<div class="event-list">';
                    events.forEach(function(event) {
                        var date = new Date(event.event_date).toLocaleString('es-ES');
                        html += `
                            <div class="event-item" style="border-left: 4px solid ${event.color}">
                                <div class="event-title">${event.title}</div>
                                <div class="event-date">${date}</div>
                                <div class="event-description">${event.description || 'Sin descripción'}</div>
                                <button class="delete-event-btn" data-event-id="${event.id}">Eliminar</button>
                            </div>
                        `;
                    });
                    html += '</div>';
                    document.getElementById('eventListContent').innerHTML = html;

                    // Asignar eventos a los botones de eliminar
                    document.querySelectorAll('.delete-event-btn').forEach(function(btn) {
                        btn.onclick = function() {
                            var eventId = this.getAttribute('data-event-id');
                            document.getElementById('delete_event_id_modal').value = eventId;
                            document.getElementById('confirmDeleteModal').style.display = 'block';
                        };
                    });
                }
                document.getElementById('eventListModal').style.display = 'block';
            };
        });

        // Cerrar modales
        document.getElementById('closeModal').onclick = function() {
            document.getElementById('eventModal').style.display = 'none';
        };

        document.getElementById('closeListModal').onclick = function() {
            document.getElementById('eventListModal').style.display = 'none';
        };

        document.getElementById('cancelDeleteBtn').onclick = function() {
            document.getElementById('confirmDeleteModal').style.display = 'none';
        };

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            if (event.target == document.getElementById('eventModal')) {
                document.getElementById('eventModal').style.display = 'none';
            }
            if (event.target == document.getElementById('eventListModal')) {
                document.getElementById('eventListModal').style.display = 'none';
            }
            if (event.target == document.getElementById('confirmDeleteModal')) {
                document.getElementById('confirmDeleteModal').style.display = 'none';
            }
        };

        // Frases motivacionales
        const phrases = [
            '¡Hoy es un buen día para cuidar de ti!',
            'Recuerda respirar y tomarlo con calma.',
            'Cada paso cuenta, sigue adelante.',
            'Tu bienestar es lo más importante.',
            'Eres más fuerte de lo que crees.',
            'Permítete descansar y recargar energías.'
        ];
        let phraseIndex = 0;
        const phraseElem = document.getElementById('motivationalPhrase');
        setInterval(() => {
            // Fade out
            phraseElem.style.opacity = 0;
            setTimeout(() => {
                phraseIndex = (phraseIndex + 1) % phrases.length;
                phraseElem.textContent = phrases[phraseIndex];
                // Fade in
                phraseElem.style.opacity = 1;
            }, 500);
        }, 6000);

        // Mis Entradas
        function showEntries() {
            fetch('obtener_entradas.php')
                .then(response => response.json())
                .then(data => {
                    let content = '<h3>Mis Entradas Anteriores</h3>';
                    if (data.length === 0) {
                        content += '<p>No tienes entradas guardadas aún.</p>';
                    } else {
                        content += '<div class="entries-list">';
                        data.forEach(entry => {
                            const date = new Date(entry.created_at).toLocaleDateString('es-ES', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            content += `
                                <div class="entry-item">
                                    <div class="entry-date">${date}</div>
                                    <div class="entry-content">${entry.entry}</div>
                                </div>
                            `;
                        });
                        content += '</div>';
                    }
                    document.getElementById('entriesModalContent').innerHTML = content;
                    document.getElementById('entriesModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar las entradas');
                });
        }

        // Cerrar el modal de entradas
        document.getElementById('closeEntriesModal').addEventListener('click', function() {
            document.getElementById('entriesModal').style.display = 'none';
        });
    </script>
</body>
</html>