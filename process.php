<?php
header("Content-Security-Policy: default-src 'self';");

session_start();
if ($_POST['token'] !== $_SESSION['token']) {
    die('CSRF detectado');
}

$nombre = htmlspecialchars(trim($_POST['nombre']));
$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
$mensaje = htmlspecialchars(trim($_POST['mensaje']));

require 'connect.php';
$stmt = $pdo->prepare('INSERT INTO usuarios (nombre, correo, mensaje) VALUES (?, ?, ?)');
$stmt->execute([$nombre, $correo, $mensaje]);
echo "Datos guardados correctamente.";

?>