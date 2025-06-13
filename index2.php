<?php
header("Content-Security-Policy: default-src 'self';");

session_start();
$_SESSION['token'] = bin2hex(random_bytes(32));
?>
<form method="post" action="process.php">
    Nombre: <input type="text" name="nombre">
    Correo: <input type="email" name="correo">
    Mensaje: <textarea name="mensaje"></textarea>
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
    <input type="submit" value="Enviar">
</form>
