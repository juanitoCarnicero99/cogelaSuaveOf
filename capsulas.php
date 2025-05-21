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

// Palabras clave y recomendaciones
$emociones = [
    'ansiedad' => [
        'nombre' => 'Ansiedad',
        'descripcion' => 'La ansiedad es una emoción caracterizada por sentimientos de tensión, preocupación y cambios físicos como aumento del ritmo cardíaco. (Fuente: APA)',
        'acciones' => [
            'Practica ejercicios de respiración profunda.',
            'Haz una pausa y realiza una caminata corta.',
            'Habla con alguien de confianza sobre cómo te sientes.',
            'Prueba una meditación guiada.'
        ],
        'color' => '#ffb74d' // Naranja (negativa leve)
    ],
    'depresion' => [
        'nombre' => 'Depresión',
        'descripcion' => 'La depresión implica sentimientos persistentes de tristeza y pérdida de interés. Puede afectar cómo te sientes, piensas y manejas las actividades diarias. (Fuente: OMS)',
        'acciones' => [
            'Habla con un profesional o consejero.',
            'Realiza actividades que antes disfrutabas, aunque no tengas ganas.',
            'Mantén una rutina de sueño y alimentación saludable.',
            'Busca apoyo en amigos o familiares.'
        ],
        'color' => '#e57373' // Rojo claro (muy negativa)
    ],
    'estrés' => [
        'nombre' => 'Estrés',
        'descripcion' => 'El estrés es una respuesta natural del cuerpo ante desafíos o demandas. Puede ser positivo o negativo dependiendo de la situación. (Fuente: APA)',
        'acciones' => [
            'Haz pausas activas durante el día.',
            'Organiza tus tareas y prioriza lo importante.',
            'Dedica tiempo a actividades relajantes.',
            'Comparte tus preocupaciones con alguien.'
        ],
        'color' => '#ffb74d' // Naranja (negativa leve)
    ],
    'ira' => [
        'nombre' => 'Ira',
        'descripcion' => 'La ira es una emoción intensa de desagrado o enfado, a menudo como respuesta a una amenaza o injusticia. (Fuente: APA)',
        'acciones' => [
            'Cuenta hasta diez antes de responder.',
            'Practica técnicas de relajación.',
            'Expresa tus sentimientos de manera asertiva.',
            'Haz ejercicio físico para liberar tensión.'
        ],
        'color' => '#c62828' // Rojo fuerte (muy negativa)
    ],
    'celos' => [
        'nombre' => 'Celos',
        'descripcion' => 'Los celos son una emoción compleja que surge ante la percepción de una amenaza hacia una relación valiosa. (Fuente: APA)',
        'acciones' => [
            'Habla abiertamente sobre tus sentimientos.',
            'Trabaja en tu autoestima.',
            'Evita compararte con los demás.',
            'Busca actividades que te hagan sentir bien contigo mismo.'
        ],
        'color' => '#f06292' // Rosa fuerte (negativa)
    ],
    'culpa' => [
        'nombre' => 'Culpa',
        'descripcion' => 'La culpa es una emoción que surge cuando sentimos que hemos hecho algo mal o hemos fallado a nuestros valores. (Fuente: APA)',
        'acciones' => [
            'Reflexiona sobre lo ocurrido y aprende de la experiencia.',
            'Pide disculpas si es necesario.',
            'Perdónate a ti mismo.',
            'Habla con alguien de confianza.'
        ],
        'color' => '#ba68c8' // Morado (negativa)
    ],
    'vergüenza' => [
        'nombre' => 'Vergüenza',
        'descripcion' => 'La vergüenza es una emoción dolorosa relacionada con la percepción de haber hecho algo inapropiado o ridículo. (Fuente: APA)',
        'acciones' => [
            'Recuerda que todos cometemos errores.',
            'Habla sobre tus sentimientos con alguien de confianza.',
            'Practica la autocompasión.',
            'Enfócate en tus cualidades positivas.'
        ],
        'color' => '#ba68c8' // Morado (negativa)
    ],
    'soledad' => [
        'nombre' => 'Soledad',
        'descripcion' => 'La soledad es la sensación de estar aislado o desconectado de los demás, incluso estando acompañado. (Fuente: OMS)',
        'acciones' => [
            'Busca actividades grupales o voluntariado.',
            'Conéctate con amigos o familiares.',
            'Habla sobre tus sentimientos.',
            'Considera adoptar una mascota.'
        ],
        'color' => '#90a4ae' // Gris azulado (negativa)
    ],
    'miedo' => [
        'nombre' => 'Miedo',
        'descripcion' => 'El miedo es una emoción básica que surge ante la percepción de peligro o amenaza. (Fuente: APA)',
        'acciones' => [
            'Identifica la causa de tu miedo.',
            'Habla sobre ello con alguien de confianza.',
            'Enfrenta tus miedos poco a poco.',
            'Practica técnicas de relajación.'
        ],
        'color' => '#7986cb' // Azul oscuro (negativa)
    ],
    'tristeza' => [
        'nombre' => 'Tristeza',
        'descripcion' => 'La tristeza es una emoción natural que surge ante la pérdida, el fracaso o la decepción. (Fuente: OMS)',
        'acciones' => [
            'Permítete sentir y expresar tu tristeza.',
            'Busca apoyo en tus seres queridos.',
            'Realiza actividades que te reconforten.',
            'Escribe sobre tus sentimientos.'
        ],
        'color' => '#64b5f6' // Azul claro (negativa)
    ],
    'alegría' => [
        'nombre' => 'Alegría',
        'descripcion' => 'La alegría es una emoción positiva que se experimenta ante situaciones agradables o satisfactorias. (Fuente: APA)',
        'acciones' => [
            'Comparte tu alegría con otros.',
            'Disfruta el momento presente.',
            'Agradece las cosas buenas de tu vida.',
            'Haz algo creativo o divertido.'
        ],
        'color' => '#81c784' // Verde (positiva)
    ],
    'esperanza' => [
        'nombre' => 'Esperanza',
        'descripcion' => 'La esperanza es la expectativa positiva de que algo bueno sucederá en el futuro. (Fuente: APA)',
        'acciones' => [
            'Visualiza tus metas.',
            'Rodéate de personas optimistas.',
            'Haz planes para el futuro.',
            'Recuerda logros pasados.'
        ],
        'color' => '#ffd54f' // Amarillo (positiva)
    ],
    'envidia' => [
        'nombre' => 'Envidia',
        'descripcion' => 'La envidia es el deseo de poseer lo que otro tiene, acompañado de resentimiento. (Fuente: APA)',
        'acciones' => [
            'Reconoce tus propios logros.',
            'Evita compararte constantemente.',
            'Transforma la envidia en motivación.',
            'Practica la gratitud.'
        ],
        'color' => '#fbc02d' // Amarillo oscuro (negativa)
    ],
    'apatía' => [
        'nombre' => 'Apatía',
        'descripcion' => 'La apatía es la falta de interés, entusiasmo o preocupación por las cosas. (Fuente: APA)',
        'acciones' => [
            'Establece pequeñas metas diarias.',
            'Busca actividades nuevas.',
            'Habla con un profesional si persiste.',
            'Conéctate con amigos.'
        ],
        'color' => '#bdbdbd' // Gris (negativa)
    ],
    'enojo' => [
        'nombre' => 'Enojo',
        'descripcion' => 'El enojo es una emoción intensa de desagrado o enfado, a menudo como respuesta a una amenaza o injusticia. (Fuente: APA)',
        'acciones' => [
            'Cuenta hasta diez antes de responder.',
            'Practica técnicas de relajación.',
            'Expresa tus sentimientos de manera asertiva.',
            'Haz ejercicio físico para liberar tensión.'
        ],
        'color' => '#c62828' // Rojo fuerte (muy negativa)
    ],
    'resentimiento' => [
        'nombre' => 'Resentimiento',
        'descripcion' => 'El resentimiento es una emoción persistente de disgusto o enfado por una ofensa pasada. (Fuente: APA)',
        'acciones' => [
            'Habla sobre tus sentimientos.',
            'Practica el perdón.',
            'Enfócate en el presente.',
            'Busca ayuda profesional si es necesario.'
        ],
        'color' => '#d84315' // Naranja oscuro (negativa)
    ],
    'soledad' => [
        'nombre' => 'Soledad',
        'descripcion' => 'La soledad es la sensación de estar aislado o desconectado de los demás, incluso estando acompañado. (Fuente: OMS)',
        'acciones' => [
            'Busca actividades grupales o voluntariado.',
            'Conéctate con amigos o familiares.',
            'Habla sobre tus sentimientos.',
            'Considera adoptar una mascota.'
        ],
        'color' => '#90a4ae' // Gris azulado (negativa)
    ],
    'culpabilidad' => [
        'nombre' => 'Culpabilidad',
        'descripcion' => 'La culpabilidad es la sensación de responsabilidad por un daño real o imaginario. (Fuente: APA)',
        'acciones' => [
            'Reflexiona sobre la situación.',
            'Haz las paces contigo mismo.',
            'Repara el daño si es posible.',
            'Habla con alguien de confianza.'
        ],
        'color' => '#ba68c8' // Morado (negativa)
    ],
    'frustración' => [
        'nombre' => 'Frustración',
        'descripcion' => 'La frustración surge cuando no se logra un objetivo o deseo. (Fuente: APA)',
        'acciones' => [
            'Identifica lo que puedes controlar.',
            'Busca alternativas o soluciones.',
            'Tómate un descanso.',
            'Habla sobre tu frustración.'
        ],
        'color' => '#ff8a65' // Naranja claro (negativa)
    ],
    'inseguridad' => [
        'nombre' => 'Inseguridad',
        'descripcion' => 'La inseguridad es la falta de confianza en uno mismo o en el entorno. (Fuente: APA)',
        'acciones' => [
            'Reconoce tus logros y capacidades.',
            'Habla sobre tus miedos.',
            'Rodéate de personas que te apoyen.',
            'Trabaja en tu autoestima.'
        ],
        'color' => '#bdbdbd' // Gris (negativa)
    ],
    'aburrimiento' => [
        'nombre' => 'Aburrimiento',
        'descripcion' => 'El aburrimiento es la sensación de falta de interés o estimulación. (Fuente: APA)',
        'acciones' => [
            'Busca nuevas actividades o pasatiempos.',
            'Cambia tu rutina.',
            'Aprende algo nuevo.',
            'Haz ejercicio físico.'
        ],
        'color' => '#ffd54f' // Amarillo (neutra)
    ],
    'gratitud' => [
        'nombre' => 'Gratitud',
        'descripcion' => 'La gratitud es el reconocimiento y aprecio por lo que se recibe, ya sea tangible o intangible. (Fuente: APA)',
        'acciones' => [
            'Haz una lista de cosas por las que estás agradecido.',
            'Expresa tu gratitud a los demás.',
            'Reflexiona sobre lo positivo en tu vida.',
            'Ayuda a alguien más.'
        ],
        'color' => '#4db6ac' // Turquesa (positiva)
    ],
    'amor' => [
        'nombre' => 'Amor',
        'descripcion' => 'El amor es una emoción compleja que implica afecto, cuidado y apego hacia otros. (Fuente: APA)',
        'acciones' => [
            'Expresa tu amor a quienes te rodean.',
            'Dedica tiempo de calidad a tus seres queridos.',
            'Practica la empatía y la comprensión.',
            'Cuida de ti mismo también.'
        ],
        'color' => '#f06292' // Rosa fuerte (positiva)
    ],
    'orgullo' => [
        'nombre' => 'Orgullo',
        'descripcion' => 'El orgullo es una emoción positiva que surge al lograr metas o recibir reconocimiento. (Fuente: APA)',
        'acciones' => [
            'Reconoce tus logros.',
            'Comparte tus éxitos con otros.',
            'Sigue esforzándote por tus metas.',
            'Ayuda a otros a alcanzar sus objetivos.'
        ],
        'color' => '#ffd54f' // Amarillo (positiva)
    ],
    'nostalgia' => [
        'nombre' => 'Nostalgia',
        'descripcion' => 'La nostalgia es una emoción agridulce que mezcla alegría y tristeza al recordar el pasado. (Fuente: APA)',
        'acciones' => [
            'Comparte tus recuerdos con alguien.',
            'Crea nuevos recuerdos positivos.',
            'Permítete sentir la emoción sin juzgarla.',
            'Haz un álbum de fotos o recuerdos.'
        ],
        'color' => '#64b5f6' // Azul claro (neutra)
    ],
    // Puedes seguir agregando más emociones...
];

// Analizar las entradas del diario
$query = "SELECT entry FROM journal_entries WHERE user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$entradas = $result->fetch_all(MYSQLI_ASSOC);

$conteo_emociones = [];
foreach ($emociones as $clave => $info) {
    $conteo_emociones[$clave] = 0;
}

foreach ($entradas as $entrada) {
    $texto = mb_strtolower($entrada['entry'], 'UTF-8');
    foreach ($emociones as $clave => $info) {
        if (strpos($texto, $clave) !== false) {
            $conteo_emociones[$clave]++;
        }
    }
}

$emociones_detectadas = array_filter($conteo_emociones, function($v) { return $v > 0; });

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cápsulas de Bienestar - Cogela Suave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .capsulas-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .capsulas-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .capsula-emocion {
            margin-bottom: 30px;
            padding: 25px 20px;
            border-radius: 12px;
            background: #f5f8fd;
            box-shadow: 0 2px 8px rgba(76,144,226,0.07);
        }
        .capsula-emocion h3 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        .capsula-emocion .descripcion {
            color: #444;
            margin-bottom: 12px;
        }
        .capsula-emocion ul {
            margin: 0;
            padding-left: 20px;
        }
        .capsula-emocion ul li {
            margin-bottom: 7px;
            color: #333;
        }
        .capsula-emocion .badge {
            display: inline-block;
            background: #1976d2;
            color: #fff;
            border-radius: 10px;
            padding: 2px 12px;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .capsulas-empty {
            text-align: center;
            color: #888;
            margin-top: 40px;
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
            <a href="capsulas.php" class="nav-item active">
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
        <div class="capsulas-container">
            <div class="capsulas-header">
                <h2><i class="fas fa-lightbulb"></i> Cápsulas de Bienestar</h2>
                <p>Recomendaciones personalizadas según tus emociones registradas en el diario.</p>
            </div>
            <?php if (count($emociones_detectadas) > 0): ?>
                <?php foreach ($emociones_detectadas as $clave => $cantidad): ?>
                    <div class="capsula-emocion" style="background: <?php echo $emociones[$clave]['color']; ?>20; border-left: 8px solid <?php echo $emociones[$clave]['color']; ?>;">
                        <span class="badge" style="background: <?php echo $emociones[$clave]['color']; ?>;">
                            <?php echo $emociones[$clave]['nombre']; ?> detectada <?php echo $cantidad; ?> vez<?php echo $cantidad > 1 ? 'es' : ''; ?>
                        </span>
                        <h3><?php echo $emociones[$clave]['nombre']; ?></h3>
                        <div class="descripcion"><?php echo $emociones[$clave]['descripcion']; ?></div>
                        <strong>Acciones sugeridas:</strong>
                        <ul>
                            <?php foreach ($emociones[$clave]['acciones'] as $accion): ?>
                                <li><?php echo $accion; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="capsulas-empty">
                    <i class="fas fa-smile-beam" style="font-size: 2em;"></i>
                    <p>No se detectaron emociones negativas frecuentes en tus entradas recientes. ¡Sigue así!</p>
                </div>
            <?php endif; ?>
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
    </script>
</body>
</html> 