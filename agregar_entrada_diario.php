<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $entry = $_POST['entry'];
    $stmt = $mysqli->prepare("INSERT INTO journal_entries (user_id, entry, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $entry);
    $stmt->execute();
}
header("Location: journaling_app.php");
exit(); 