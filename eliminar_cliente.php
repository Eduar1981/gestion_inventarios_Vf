<?php
require 'pdo.php'; // Asegúrate de que la ruta es correcta

session_start(); // Iniciar sesión

// Verificar si se ha pasado el parámetro 'contador_clientes' en la URL
if (isset($_GET['contador_clientes'])) {
    $contador_clientes = $_GET['contador_clientes'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    $_SESSION['error'] = "No tienes permisos para eliminar clientes.";
    header('Location: ver_clientes.php');
    exit();
}

    // Preparar y ejecutar la consulta para actualizar el estado del cliente a 'inactivo'
    $stmt = $pdo->prepare("UPDATE clientes SET estado = 'inactivo' WHERE contador_clientes = :contador_clientes");

    if ($stmt->execute(['contador_clientes' => $contador_clientes])) {
        // Mensaje de éxito
        $_SESSION['mensaje'] = "Cliente eliminado exitosamente.";
    } else {
        // Mensaje de error
        $_SESSION['mensaje'] = "Error al eliminar el cliente.";
    }
} else {
    $_SESSION['mensaje'] = "ID de cliente no proporcionado.";
}

// Redirigir a la lista de clientes después de la eliminación
header('Location: ver_clientes.php');
exit();
?>