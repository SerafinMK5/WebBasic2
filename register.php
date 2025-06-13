<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
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

input[type="text"],
input[type="mail"],
input[type="password"] {
    width: calc(100% - 16px);
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

button[type="submit"] {
    background-color: #007bff; /* Un color verde para "Registrar" */
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
    background-color: #0056b3; /* Un verde más oscuro para el hover */
}

br {
    display: none; /* Opcional: Oculta los saltos de línea */
}
    </style>
    <h2>Registrar Usuario</h2>
    <form action="register.php" method="POST">
        <label for="username">Usuario:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="mail">Correo:</label>
        <input type="mail" name="mail" id="mail" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Registrar</button>
    </form>
</body>
</html>

<?php
// Lógica para registrar usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'connect.php'; // Archivo de conexión

    $username = $_POST['username'];
    $mail = $_POST['mail'];
    $password = $_POST['password'];

    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user_exists = $stmt->fetchColumn();
    // Verifica si el correo ya esta en uso
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :mail");
    $stmt->execute(['mail' => $mail]);
    $mail_exists = $stmt->fetchColumn();

    if ($user_exists) {
        echo "El nombre de usuario ya está registrado.";
    } else if ($mail_exists) {
        echo "El correo ya está registrado.";
    } 
    else {
        // Hashear la contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar el nuevo usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO usuarios (username, correo, password_hash) VALUES (:username, :correo, :password_hash)");
        if ($stmt->execute(['username' => $username, 'correo'=>$mail, 'password_hash' => $password_hash])) {
            echo "Usuario registrado exitosamente.";
        } else {
            echo "Error al registrar el usuario.";
        }
    }
}
?>
