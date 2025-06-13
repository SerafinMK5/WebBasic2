<?php
require 'connect.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $token = $_POST['token'];

    if (empty($username) || empty($token)) {
        echo "Por favor, ingrese todos los campos.";
        exit;
    }

    // Verificar si el token es correcto
    $stmt = $pdo->prepare("SELECT token FROM tokenUsuario WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $stored_token = $stmt->fetchColumn();

    if ($stored_token !== false && hash_equals($stored_token, $token)) {
        echo "Token verificado correctamente. Acceso permitido.";

        // Eliminar token usado
        $stmt = $pdo->prepare("DELETE FROM tokenUsuario WHERE username = :username");
        $stmt->execute(['username' => $username]);

        // Resetear intentos fallidos a 0 porque fue exitoso (opcional, pero recomendable)
        $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0 WHERE username = :username");
        $stmt->execute(['username' => $username]);

    } else {
        // Obtener intentos fallidos actuales del usuario
        $stmt = $pdo->prepare("SELECT intentos_fallidos FROM usuarios WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $intentos_actuales = $user['intentos_fallidos'] + 1;

            if ($intentos_actuales >= 3) {
                // Bloquear usuario por 5 minutos y resetear intentos
                $stmt = $pdo->prepare("UPDATE usuarios SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 5 MINUTE), intentos_fallidos = 0 WHERE username = :username");
                $stmt->execute(['username' => $username]);
                echo "Usuario bloqueado por 5 minutos.";
            } else {
                // Incrementar intentos fallidos
                $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = :intentos WHERE username = :username");
                $stmt->execute(['intentos' => $intentos_actuales, 'username' => $username]);
                echo "Token incorrecto. Intento $intentos_actuales de 3.";
            }
        } else {
            echo "Usuario no encontrado.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Token</title>

</head>
<body>
    <style>
        body {
    font-family: sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    background-color: #f4f4f4;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    margin-right: 100px;
}

form {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 300px; /* Ajusta el ancho según necesites */
}

label {
    display: block;
    margin-bottom: 8px;
    color: #555;
}

input[type="text"] {
    width: calc(100% - 16px);
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

button[type="submit"] {
    background-color: #007bff;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
    width: 100%;
}

button[type="submit"]:hover {
    background-color: #0056b3; 
}

br {
    display: none;
}
    </style>
    <h2>Verificar Token</h2>
    <form action="verify_token.php" method="POST">
        <label for="username">Usuario:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="token">Token:</label>
        <input type="text" name="token" id="token" required>
        <br>
        <button type="submit">Verificar Token</button>
    </form>
</body>
</html>
