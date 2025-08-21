<?php
require 'pdo.php'; 
session_start(); 

// Verifica que el usuario tenga permiso
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    $_SESSION['error'] = "No tienes permisos para eliminar proveedores.";
    header('Location: ver_proveedores.php');
    exit();
}

// Verifica que venga el ID
if (isset($_GET['cont_provee'])) {
    $cont_provee = $_GET['cont_provee'];

    // Ejecutar actualización del estado a 'inactivo'
    $stmt = $pdo->prepare("UPDATE proveedores SET estado = 'inactivo' WHERE cont_provee = :cont_provee");

    if ($stmt->execute(['cont_provee' => $cont_provee])) {
        $_SESSION['mensaje'] = "✅ Proveedor eliminado correctamente (estado inactivo).";
    } else {
        $_SESSION['mensaje'] = "❌ Error al eliminar el proveedor.";
    }

} else {
    $_SESSION['mensaje'] = "⚠️ ID de proveedor no proporcionado.";
}

header('Location: ver_proveedores.php');
exit();
