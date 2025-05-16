<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['event_title'];
    $date = $_POST['event_date'];
    $color = $_POST['event_color'];
    $stmt = $mysqli->prepare("INSERT INTO calendar_events (user_id, title, event_date, color) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $date, $color);
    $stmt->execute();
}
header("Location: journaling_app.php");
exit(); 