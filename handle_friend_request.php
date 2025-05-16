<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['amigo_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$amigo_id = $_POST['amigo_id'];

// Verificar si ya existe una solicitud
$check_query = "SELECT * FROM amigos WHERE (usuario_id = ? AND amigo_id = ?) OR (usuario_id = ? AND amigo_id = ?)";
$stmt = $mysqli->prepare($check_query);
$stmt->bind_param("iiii", $usuario_id, $amigo_id, $amigo_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Si la solicitud está pendiente y viene del otro usuario, aceptarla
    if ($row['estado'] == 'pendiente' && $row['amigo_id'] == $usuario_id) {
        $update_query = "UPDATE amigos SET estado = 'aceptado', fecha_aceptacion = NOW() WHERE id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Solicitud aceptada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ya existe una solicitud pendiente']);
    }
} else {
    // Crear nueva solicitud
    $insert_query = "INSERT INTO amigos (usuario_id, amigo_id) VALUES (?, ?)";
    $stmt = $mysqli->prepare($insert_query);
    $stmt->bind_param("ii", $usuario_id, $amigo_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Solicitud enviada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar solicitud']);
    }
}
?> 