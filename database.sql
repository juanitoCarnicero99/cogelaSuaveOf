-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS cogela_suave_db;
USE cogela_suave_db;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apodo VARCHAR(50) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    fecha_nacimiento DATE,
    carrera VARCHAR(100),
    descripcion_personal TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Tabla de amigos
CREATE TABLE IF NOT EXISTS amigos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    amigo_id INT NOT NULL,
    estado ENUM('pendiente', 'aceptado') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_aceptacion DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (amigo_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_amistad (usuario_id, amigo_id)
);

-- Tabla de mensajes
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remitente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (remitente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de entradas del diario
CREATE TABLE IF NOT EXISTS journal_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    entry TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    estado_animo ENUM('feliz', 'triste', 'ansioso', 'tranquilo', 'enojado', 'neutral') DEFAULT 'neutral',
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de eventos del calendario
CREATE TABLE IF NOT EXISTS calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    end_date DATETIME,
    color VARCHAR(20) DEFAULT '#7C9A92',
    tipo_evento ENUM('personal', 'terapia', 'medicamento', 'ejercicio', 'otro') DEFAULT 'personal',
    recordatorio BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de etiquetas para las entradas del diario
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(20) DEFAULT '#7C9A92'
);

-- Tabla de relación entre entradas y etiquetas
CREATE TABLE IF NOT EXISTS journal_tags (
    journal_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (journal_id, tag_id),
    FOREIGN KEY (journal_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Tabla de recordatorios
CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_recordatorio DATETIME NOT NULL,
    estado ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES calendar_events(id) ON DELETE CASCADE
);

-- Tabla de entradas asociadas a eventos
CREATE TABLE IF NOT EXISTS event_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    descripcion TEXT,
    color VARCHAR(20) DEFAULT '#FF9900',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES calendar_events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar algunas etiquetas predefinidas
INSERT INTO tags (nombre, color) VALUES
('Ansiedad', '#E57373'),
('Depresión', '#64B5F6'),
('Estrés', '#FFB74D'),
('Ejercicio', '#81C784'),
('Meditación', '#BA68C8'),
('Terapia', '#4DB6AC'),
('Medicamentos', '#FF8A65'),
('Sueño', '#7986CB'),
('Alimentación', '#4DD0E1'),
('Social', '#FFD54F');

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_journal_user_date ON journal_entries(user_id, created_at);
CREATE INDEX idx_events_user_date ON calendar_events(user_id, event_date);
CREATE INDEX idx_reminders_user_date ON reminders(user_id, fecha_recordatorio);
CREATE INDEX idx_amigos_usuarios ON amigos(usuario_id, amigo_id);
CREATE INDEX idx_mensajes_usuarios ON mensajes(remitente_id, destinatario_id); 