<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';      // Servidor SMTP (ej: Gmail)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'serafin@threelab.tech';   // Tu correo
    $mail->Password   = '5XaVjPL+';        // Contraseña del correo
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Destinatarios
    $mail->setFrom('serafin@threelab.tech', 'Serafin Martinez Sandoval');
    $mail->addAddress($correo);     // A quién se envía

    // Contenido
    $mail->isHTML(true);                                  // Formato HTML
    $mail->Subject = 'Token para tu inicio de sesión';
    $mail->Body    = $username.' Token de verificación <br> <center> <b>'.$token.'</b> </center>';
    $mail->AltBody = $username.'Token de verificación'. $token;

    $mail->send();
    echo 'Correo enviado exitosamente.';
} catch (Exception $e) {
    echo "Error al enviar el mensaje: {$mail->ErrorInfo}";
}
?>