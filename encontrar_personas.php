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

// Obtener datos del usuario actual
$user_id = $_SESSION['user_id'];
$user_result = $mysqli->query("SELECT * FROM usuarios WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Obtener todos los usuarios excepto el actual y los que ya son amigos
$query = "SELECT u.id, u.apodo, u.email, u.nombre, u.apellido, u.descripcion_personal 
          FROM usuarios u 
          WHERE u.id != ? 
          AND u.id NOT IN (
              SELECT amigo_id FROM amigos 
              WHERE usuario_id = ? AND estado = 'aceptado'
              UNION
              SELECT usuario_id FROM amigos 
              WHERE amigo_id = ? AND estado = 'aceptado'
          )
          ORDER BY u.apodo ASC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontrar Personas - Cogela Suave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .user-card {
            position: relative;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
        }

        .user-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .like-btn {
            color: #4CAF50;
        }

        .dislike-btn {
            color: #f44336;
        }

        .action-btn:hover {
            transform: scale(1.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .modal-avatar {
            font-size: 80px;
            color: #4a90e2;
        }

        .modal-details {
            text-align: center;
        }

        .modal-name {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .modal-nickname {
            color: #666;
            margin-bottom: 15px;
        }

        .modal-email {
            color: #4a90e2;
        }

        .modal-description {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
            font-style: italic;
        }

        .hidden {
            display: none;
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
            <a href="encontrar_personas.php" class="nav-item active">
                <i class="fas fa-users"></i>
                <span>Encontrar Personas</span>
            </a>
            <a href="capsulas.php" class="nav-item">
                <i class="fas fa-lightbulb"></i>
                <span>Cápsulas</span>
            </a>
            <a href="chat.php" class="nav-item">
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
        <div class="container">
            <div class="section-description">
                <h2>Encontrar Personas</h2>
                <p class="description-text">Mediante esta función puedes encontrar usuarios con gustos afines para así poder compartir lo que sientes con confianza.</p>
            </div>
            
            <div class="users-grid">
                <?php foreach ($usuarios as $usuario): ?>
                <div class="user-card" data-user-id="<?php echo $usuario['id']; ?>">
                    <div class="user-actions">
                        <button class="action-btn like-btn" onclick="handleLike(event, <?php echo $usuario['id']; ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="action-btn dislike-btn" onclick="handleDislike(event, <?php echo $usuario['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <h3 class="user-name"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h3>
                        <p class="user-nickname">@<?php echo htmlspecialchars($usuario['apodo']); ?></p>
                        <p class="user-email"><?php echo htmlspecialchars($usuario['email']); ?></p>
                        <?php if (!empty($usuario['descripcion_personal'])): ?>
                        <p class="user-description"><?php echo htmlspecialchars($usuario['descripcion_personal']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del usuario -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div class="modal-user-info">
                <div class="modal-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="modal-details">
                    <h2 class="modal-name"></h2>
                    <p class="modal-nickname"></p>
                    <p class="modal-email"></p>
                    <p class="modal-description"></p>
                </div>
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

        // Funciones para el manejo de likes y dislikes
        function handleLike(event, userId) {
            event.stopPropagation();
            fetch('handle_friend_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `amigo_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = event.target.closest('.user-card');
                    card.classList.add('hidden');
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            });
        }

        function handleDislike(event, userId) {
            event.stopPropagation();
            const card = event.target.closest('.user-card');
            card.classList.add('hidden');
        }

        // Funciones para el modal
        const modal = document.getElementById('userModal');
        const userCards = document.querySelectorAll('.user-card');

        userCards.forEach(card => {
            card.addEventListener('click', () => {
                const name = card.querySelector('.user-name').textContent;
                const nickname = card.querySelector('.user-nickname').textContent;
                const email = card.querySelector('.user-email').textContent;
                const description = card.querySelector('.user-description')?.textContent || '';

                modal.querySelector('.modal-name').textContent = name;
                modal.querySelector('.modal-nickname').textContent = nickname;
                modal.querySelector('.modal-email').textContent = email;
                modal.querySelector('.modal-description').textContent = description;

                modal.style.display = 'block';
            });
        });

        function closeModal() {
            modal.style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html> 