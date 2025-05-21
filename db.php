<?php
$mysqli = new mysqli("sql111.infinityfree.com", "if0_39037484", "JJBautis160r2v", "if0_39037484_cogela_suave_db");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}
?>