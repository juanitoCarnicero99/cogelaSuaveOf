<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT entry, created_at FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = [
        'entry' => htmlspecialchars($row['entry']),
        'created_at' => $row['created_at']
    ];
}

header('Content-Type: application/json');
echo json_encode($entries);
?> 