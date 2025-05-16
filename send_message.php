<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['amigo_id']) || !isset($_POST['mensaje'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$remitente_id = $_SESSION['user_id'];
$destinatario_id = $_POST['amigo_id'];
$mensaje = $_POST['mensaje'];

// Verificar si son amigos
$check_query = "SELECT * FROM amigos WHERE ((usuario_id = ? AND amigo_id = ?) OR (usuario_id = ? AND amigo_id = ?)) AND estado = 'aceptado'";
$stmt = $mysqli->prepare($check_query);
$stmt->bind_param("iiii", $remitente_id, $destinatario_id, $destinatario_id, $remitente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No puedes enviar mensajes a este usuario']);
    exit();
}

// Insertar mensaje
$insert_query = "INSERT INTO mensajes (remitente_id, destinatario_id, mensaje) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($insert_query);
$stmt->bind_param("iis", $remitente_id, $destinatario_id, $mensaje);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar mensaje']);
}
?> 