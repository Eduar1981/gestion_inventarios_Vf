<?php
require 'pdo.php'; // Asegúrate de que la ruta es correcta

session_start(); // Iniciar sesión

// Verificar si se ha pasado el parámetro 'contador_usuarios' en la URL
if (isset($_GET['contador_usuarios'])) {
    $contador_usuarios = $_GET['contador_usuarios'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    $_SESSION['error'] = "No tienes permisos para eliminar usuarios.";
    header('Location: ver_usuarios.php');
    exit();
}

    // Preparar y ejecutar la consulta para actualizar el estado del usuario a 'inactivo'
    $stmt = $pdo->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE contador_usuarios = :contador_usuarios");

    if ($stmt->execute(['contador_usuarios' => $contador_usuarios])) {
        // Mensaje de éxito
        $_SESSION['mensaje'] = "Usuario eliminado exitosamente.";
    } else {
        // Mensaje de error
        $_SESSION['mensaje'] = "Error al eliminar el usuario.";
    }
} else {
    $_SESSION['mensaje'] = "ID de usuario no proporcionado.";
}

// Redirigir a la lista de usuarios después de la eliminación
header('Location: ver_usuarios.php');
exit();
?>
