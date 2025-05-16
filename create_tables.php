<?php
include 'db.php';

// Crear tabla de amigos
$sql = "CREATE TABLE IF NOT EXISTS amigos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    amigo_id INT NOT NULL,
    estado ENUM('pendiente', 'aceptado') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_aceptacion DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (amigo_id) REFERENCES usuarios(id),
    UNIQUE KEY unique_amistad (usuario_id, amigo_id)
)";

if ($mysqli->query($sql)) {
    echo "Tabla de amigos creada correctamente<br>";
} else {
    echo "Error al crear la tabla de amigos: " . $mysqli->error . "<br>";
}

// Crear tabla de mensajes
$sql = "CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remitente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (remitente_id) REFERENCES usuarios(id),
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id)
)";

if ($mysqli->query($sql)) {
    echo "Tabla de mensajes creada correctamente<br>";
} else {
    echo "Error al crear la tabla de mensajes: " . $mysqli->error . "<br>";
}

echo "<br>Proceso completado. <a href='encontrar_personas.php'>Volver a Encontrar Personas</a>";
?> 