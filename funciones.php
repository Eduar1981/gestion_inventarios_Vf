<?php
function mostrar_mensaje() {
    if (isset($_SESSION['mensaje'])) {
        // Definir el tipo de mensaje (éxito o error)
        $tipo = $_SESSION['tipo_mensaje'] ?? 'success'; // Default a 'success'

        // Mostrar el mensaje con una clase CSS dependiendo del tipo
        echo "<div class='alert alert-{$tipo}'>";
        echo htmlspecialchars($_SESSION['mensaje']);
        echo "</div>";

        // Eliminar el mensaje de la sesión para que no persista
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
    }
}
?>
