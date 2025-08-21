<?php
require 'pdo.php'; // Asegúrate de que la ruta es correcta

session_start(); // Iniciar sesión

// Verificar si se ha pasado el parámetro 'cont_producto' en la URL
if (isset($_GET['cont_producto'])) {
    $cont_producto = $_GET['cont_producto'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    $_SESSION['error'] = "No tienes permisos para eliminar productos.";
    header('Location: ver_productos.php');
    exit();
}

    // Preparar y ejecutar la consulta para actualizar el estado del usuario a 'inactivo'
    $stmt = $pdo->prepare("UPDATE productos SET estado = 'inactivo' WHERE cont_producto = :cont_producto");

    if ($stmt->execute(['cont_producto' => $cont_producto])) {
        // Mensaje de éxito
        $_SESSION['mensaje'] = "Producto eliminado exitosamente.";
    } else {
        // Mensaje de error
        $_SESSION['mensaje'] = "Error al eliminar el producto.";
    }
} else {
    $_SESSION['mensaje'] = "ID de producto no proporcionado.";
}

// Redirigir a la lista de proveedores después de la eliminación
header('Location: ver_productos.php');
exit();
?>