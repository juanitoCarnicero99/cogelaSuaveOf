<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event_id'])) {
    $user_id = $_SESSION['user_id'];
    $delete_id = intval($_POST['delete_event_id']);
    $stmt = $mysqli->prepare("DELETE FROM calendar_events WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
}
header("Location: journaling_app.php");
exit(); 