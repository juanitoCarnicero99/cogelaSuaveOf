<?php
$mysqli = new mysqli("localhost", "root", "root", "cogela_suave_db");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}
?>