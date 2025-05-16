<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $color = $_POST['event_color'];
    $description = $_POST['description'];

    // Combinar fecha y hora
    $event_datetime = $event_date . ' ' . $event_time;

    $query = "INSERT INTO calendar_events (user_id, title, event_date, color, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("issss", $user_id, $title, $event_datetime, $color, $description);

    if ($stmt->execute()) {
        header("Location: journaling_app.php?success=1");
    } else {
        header("Location: journaling_app.php?error=1");
    }
    exit();
}
?> 