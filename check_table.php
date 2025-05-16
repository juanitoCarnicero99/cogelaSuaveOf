<?php
include 'db.php';

$result = $mysqli->query("SHOW COLUMNS FROM usuarios");
if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Error: " . $mysqli->error;
}
?> 