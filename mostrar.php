<?php
header("Content-Security-Policy: default-src 'self';");

require 'connect.php'; // Conexión a la base de datos.

$stmt = $pdo->query('SELECT nombre, mensaje FROM usuarios');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<p><strong>" . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') . ":</strong> " .
         htmlspecialchars($row['mensaje'], ENT_QUOTES, 'UTF-8') . "</p>";
}
?>