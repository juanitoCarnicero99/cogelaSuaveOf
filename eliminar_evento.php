<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event_id'])) {
    $event_id = $_POST['delete_event_id'];
    $user_id = $_SESSION['user_id'];

    // Verificar que el evento pertenece al usuario
    $query = "DELETE FROM calendar_events WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $event_id, $user_id);

    if ($stmt->execute()) {
        header("Location: journaling_app.php?success=2");
    } else {
        header("Location: journaling_app.php?error=2");
    }
    exit();
}

header("Location: journaling_app.php");
exit();
?> 