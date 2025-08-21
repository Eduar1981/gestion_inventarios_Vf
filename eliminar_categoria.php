<?php
require 'pdo.php'; // Asegúrate de que la ruta es correcta

session_start(); // Iniciar sesión

// Verificar si se ha pasado el parámetro 'contador_clientes' en la URL
if (isset($_GET['contador_categoria'])) {
    $contador_categoria = $_GET['contador_categoria'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    $_SESSION['error'] = "No tienes permisos para eliminar categorias.";
    header('Location: ver_categorias.php');
    exit();
}

    // Preparar y ejecutar la consulta para actualizar el estado de la categoria 'inactivo'
    $stmt = $pdo->prepare("UPDATE categorias SET estado = 'inactivo' WHERE contador_categoria = :contador_categoria");

    if ($stmt->execute(['contador_categoria' => $contador_categoria])) {
        // Mensaje de éxito
        $_SESSION['mensaje'] = "Categoria eliminada exitosamente.";
    } else {
        // Mensaje de error
        $_SESSION['mensaje'] = "Error al eliminar la categoria.";
    }
} else {
    $_SESSION['mensaje'] = "ID de categoria no proporcionado.";
}

// Redirigir a la lista de categorias después de la eliminación
header('Location: ver_categorias.php');
exit();
?>
