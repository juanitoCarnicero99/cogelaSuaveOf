<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = $_POST['event_id'];
    $nombre_entrada = $_POST['nombre_entrada'];
    $fecha_entrada = $_POST['fecha_entrada'];
    $hora_entrada = $_POST['hora_entrada'];
    $descripcion_entrada = $_POST['descripcion_entrada'];
    $color_entrada = $_POST['color_entrada'];
    $stmt = $mysqli->prepare("INSERT INTO event_entries (event_id, user_id, nombre, fecha, hora, descripcion, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $event_id, $user_id, $nombre_entrada, $fecha_entrada, $hora_entrada, $descripcion_entrada, $color_entrada);
    $stmt->execute();
}
header("Location: journaling_app.php");
exit(); 