<?php
header("Content-Security-Policy: default-src 'self';");

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sera', 'root', '147');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error al conectar: ' . $e->getMessage());
}
?>
