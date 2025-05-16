<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$user_result = $mysqli->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Obtener amigos
$query = "SELECT u.id, u.apodo, u.nombre, u.apellido 
          FROM usuarios u 
          INNER JOIN amigos a ON (a.amigo_id = u.id OR a.usuario_id = u.id)
          WHERE (a.usuario_id = ? OR a.amigo_id = ?) 
          AND a.estado = 'aceptado'
          AND u.id != ?
          ORDER BY u.apodo ASC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$amigos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener mensajes si hay un amigo seleccionado
$mensajes = [];
$amigo_seleccionado = null;
if (isset($_GET['amigo_id'])) {
    $amigo_id = $_GET['amigo_id'];
    $query = "SELECT m.*, u.apodo as remitente_apodo 
              FROM mensajes m 
              INNER JOIN usuarios u ON m.remitente_id = u.id 
              WHERE (m.remitente_id = ? AND m.destinatario_id = ?) 
              OR (m.remitente_id = ? AND m.destinatario_id = ?)
              ORDER BY m.fecha_envio ASC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iiii", $user_id, $amigo_id, $amigo_id, $user_id);
    $stmt->execute();
    $mensajes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Marcar mensajes como leídos
    $update = "UPDATE mensajes SET leido = TRUE 
               WHERE remitente_id = ? AND destinatario_id = ? AND leido = FALSE";
    $stmt = $mysqli->prepare($update);
    $stmt->bind_param("ii", $amigo_id, $user_id);
    $stmt->execute();

    // Obtener información del amigo seleccionado
    foreach ($amigos as $amigo) {
        if ($amigo['id'] == $amigo_id) {
            $amigo_seleccionado = $amigo;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Cogela Suave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .chat-container {
            display: flex;
            height: calc(100vh - 60px);
            background: #f5f5f5;
        }

        .friends-list {
            width: 300px;
            background: white;
            border-right: 1px solid #ddd;
            overflow-y: auto;
        }

        .friend-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .friend-item:hover {
            background-color: #f5f5f5;
        }

        .friend-item.active {
            background-color: #e3f2fd;
        }

        .friend-name {
            font-weight: 500;
            color: #333;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .friend-apodo {
            background: #e3f2fd;
            color: #1976d2;
            font-size: 0.95em;
            padding: 2px 10px;
            border-radius: 12px;
            margin-left: 5px;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 15px;
            background: white;
            border-bottom: 1px solid #ddd;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            position: relative;
        }

        .message.sent {
            align-self: flex-end;
            background-color: #4a90e2;
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.received {
            align-self: flex-start;
            background-color: #e9ecef;
            color: #333;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }

        .message.sent .message-time {
            color: #e3e3e3;
        }

        .message.received .message-time {
            color: #666;
        }

        .chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .chat-input input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }

        .chat-input input:focus {
            border-color: #4a90e2;
        }

        .chat-input button {
            width: 40px;
            height: 40px;
            padding: 0;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-input button i {
            font-size: 16px;
        }

        .chat-input button:hover {
            background: #357abd;
            transform: scale(1.05);
        }

        .no-chat-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            font-size: 1.2rem;
        }

        .unread-badge {
            background: #4a90e2;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
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
            <a href="journaling_app.php" class="nav-item">
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
            <a href="chat.php" class="nav-item active">
                <i class="fas fa-user-friends"></i>
                <span>Mis Amigos</span>
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
        <div class="chat-container">
            <div class="friends-list">
                <?php foreach ($amigos as $amigo): ?>
                <a href="?amigo_id=<?php echo $amigo['id']; ?>" class="friend-item <?php echo ($amigo_seleccionado && $amigo_seleccionado['id'] == $amigo['id']) ? 'active' : ''; ?>">
                    <div class="friend-name">
                        <?php echo htmlspecialchars($amigo['nombre'] . ' ' . $amigo['apellido']); ?>
                        <span class="friend-apodo">@<?php echo htmlspecialchars($amigo['apodo']); ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="chat-area">
                <?php if ($amigo_seleccionado): ?>
                <div class="chat-header">
                    <h3><?php echo htmlspecialchars($amigo_seleccionado['nombre'] . ' ' . $amigo_seleccionado['apellido']); ?></h3>
                    <p>@<?php echo htmlspecialchars($amigo_seleccionado['apodo']); ?></p>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($mensajes as $mensaje): ?>
                    <div class="message <?php echo $mensaje['remitente_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <div class="message-content">
                            <?php echo htmlspecialchars($mensaje['mensaje']); ?>
                        </div>
                        <div class="message-time">
                            <?php echo date('H:i', strtotime($mensaje['fecha_envio'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form class="chat-input" id="messageForm">
                    <input type="text" id="messageInput" placeholder="Escribe un mensaje..." required>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                <?php else: ?>
                <div class="no-chat-selected">
                    <p>Selecciona un amigo para comenzar a chatear</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
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
            phraseElem.style.opacity = 0;
            setTimeout(() => {
                phraseIndex = (phraseIndex + 1) % phrases.length;
                phraseElem.textContent = phrases[phraseIndex];
                phraseElem.style.opacity = 1;
            }, 500);
        }, 6000);

        // Funcionalidad del chat
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        const chatMessages = document.getElementById('chatMessages');

        function enviarMensaje() {
            const message = messageInput.value.trim();
            if (message) {
                const amigoId = new URLSearchParams(window.location.search).get('amigo_id');
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `amigo_id=${amigoId}&mensaje=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Agregar mensaje al chat
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message sent';
                        messageDiv.innerHTML = `
                            <div class="message-content">${message}</div>
                            <div class="message-time">${new Date().toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</div>
                        `;
                        chatMessages.appendChild(messageDiv);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                        messageInput.value = '';
                        messageInput.focus();
                    } else {
                        alert('Error al enviar el mensaje. Por favor, intenta de nuevo.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al enviar el mensaje. Por favor, intenta de nuevo.');
                });
            }
        }

        if (messageForm) {
            // Enviar mensaje al hacer clic en el botón
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                enviarMensaje();
            });

            // Enviar mensaje al presionar Enter
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    enviarMensaje();
                }
            });
        }

        // Auto-scroll al último mensaje
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Actualizar mensajes cada 5 segundos
        setInterval(() => {
            const amigoId = new URLSearchParams(window.location.search).get('amigo_id');
            if (amigoId) {
                fetch(`get_messages.php?amigo_id=${amigoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.messages) {
                        const lastMessage = chatMessages.lastElementChild;
                        const lastMessageId = lastMessage ? lastMessage.dataset.messageId : 0;
                        
                        data.messages.forEach(message => {
                            if (message.id > lastMessageId) {
                                const messageDiv = document.createElement('div');
                                messageDiv.className = 'message received';
                                messageDiv.dataset.messageId = message.id;
                                messageDiv.innerHTML = `
                                    <div class="message-content">${message.mensaje}</div>
                                    <div class="message-time">${new Date(message.fecha_envio).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</div>
                                `;
                                chatMessages.appendChild(messageDiv);
                            }
                        });
                        
                        if (data.messages.length > 0) {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    }
                });
            }
        }, 5000);
    </script>
</body>
</html> 