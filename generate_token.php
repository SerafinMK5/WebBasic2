<?php
require 'connect.php'; // Conexión a la base de datos

// Función para generar un token aleatorio
function generar_token() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    // Generar el token
    $token = generar_token();

    // Actualizar el token en la base de datos
    $stmt = $pdo->prepare("UPDATE usuarios SET token = :token WHERE username = :username");
    if ($stmt->execute(['token' => $token, 'username' => $username])) {
        echo "Token generado exitosamente para $username.<br>";
        echo "Token: $token (puedes enviarlo por correo o mostrarlo temporalmente)";
    } else {
        echo "Error al generar el token.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Token</title>
</head>
<body>
    <h2>Generar Token para 2FA</h2>
    <form action="generate_token.php" method="POST">
        <label for="username">Usuario:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <button type="submit">Generar Token</button>
    </form>
</body>
</html>

