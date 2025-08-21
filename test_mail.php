<?php
$to = "edcorgris@gmail.com"; // Cambia esto por tu correo
$subject = "Prueba de mail() en PHP";
$message = "Hola, esto es una prueba de la función mail() en XAMPP.";
$headers = "From: prueba@localhost.com";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ El correo se envió correctamente.";
} else {
    echo "❌ Error: No se pudo enviar el correo.";
}
?>
