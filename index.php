<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Usuarios</title>
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
    display: none; /* Opcional: Oculta los saltos de línea para un diseño más limpio */
}
    </style>
    <h2>Inicio Usuario</h2>
    <form action="login.php" method="POST">
        <label for="username">Usuario:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Entrar</button>
    </form> 
</body>
</html>
<?php
function generar_token() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'connect.php'; // Conexión a la base de datos

    // 📍 Obtener y ajustar IP
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }

    $username = $_POST['username'];
    $password = $_POST['password'];
    $ahora = new DateTime();

    // 🔒 Verificar si la IP está bloqueada
    $stmt = $pdo->prepare("SELECT * FROM ips_bloqueadas WHERE ip = ?");
    $stmt->execute([$ip]);
    $ip_data = $stmt->fetch();

    if ($ip_data && $ip_data['bloqueado_hasta'] && $ahora < new DateTime($ip_data['bloqueado_hasta'])) {
        echo "⛔ Acceso bloqueado desde esta IP hasta " . $ip_data['bloqueado_hasta'];
        exit;
    }

    // 🔐 Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT password_hash, bloqueado_hasta, intentos_fallidos FROM usuarios WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        // ⛔ Verificar si el usuario está bloqueado
        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
            echo "⛔ Usuario bloqueado. Intenta nuevamente después de: " . $user['bloqueado_hasta'];
            exit;
        }

        // ✅ Verificación de contraseña
        if (password_verify($password, $user['password_hash'])) {
            // ✅ Éxito: solo permitir si la IP NO está bloqueada
            if (!$ip_data || !$ip_data['bloqueado_hasta'] || $ahora >= new DateTime($ip_data['bloqueado_hasta'])) {
                // ✔ Restablecer fallos del usuario e IP
                $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE username = :username")
                    ->execute(['username' => $username]);
                $pdo->prepare("DELETE FROM ips_bloqueadas WHERE ip = ?")->execute([$ip]);

                // 🎟 Generar token y guardarlo
                $token = generar_token();
                $stmt = $pdo->prepare("INSERT INTO tokenUsuario VALUES (null, :username, :token, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 201)");
                $stmt->execute(['username' => $username, 'token' => $token]);

                // 📧 Obtener correo del usuario y enviar token
                $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $correo = $stmt->fetchColumn();
                require 'correo.php';

                echo "✅ Token enviado al correo.";
            } else {
                echo "⛔ Acceso bloqueado por IP, aunque la contraseña sea correcta.";
            }
        } else {
            // ❌ Contraseña incorrecta
            $stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE username = :username");
            $stmt->execute(['username' => $username]);

            // ❌ Bloquear usuario si supera los intentos
            if ($user['intentos_fallidos'] + 1 >= 3) {
                $stmt = $pdo->prepare("UPDATE usuarios SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 5 MINUTE), intentos_fallidos = 0 WHERE username = :username");
                $stmt->execute(['username' => $username]);
                echo "⛔ Usuario bloqueado por 5 minutos.";
            } else {
                echo "❌ Contraseña incorrecta.";
            }

            // ❌ Incrementar intentos fallidos por IP<
            if ($ip_data) {
                $nuevo_intento = $ip_data['intentos'] + 1;
                $stmt = $pdo->prepare("UPDATE ips_bloqueadas SET intentos = ?, bloqueado_hasta = IF(? >= 5, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NULL) WHERE ip = ?");
                $stmt->execute([$nuevo_intento, $nuevo_intento, $ip]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO ips_bloqueadas (ip, intentos) VALUES (?, 1)");
                $stmt->execute([$ip]);
            }
        }
    } else {
        echo "❌ Usuario no encontrado.";

        // ❌ Registrar intentos fallidos de IP aunque el usuario no exista
        if ($ip_data) {
            $nuevo_intento = $ip_data['intentos'] + 1;
            $stmt = $pdo->prepare("UPDATE ips_bloqueadas SET intentos = ?, bloqueado_hasta = IF(? >= 5, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NULL) WHERE ip = ?");
            $stmt->execute([$nuevo_intento, $nuevo_intento, $ip]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ips_bloqueadas (ip, intentos) VALUES (?, 1)");
            $stmt->execute([$ip]);
        }
    }
}
?>
