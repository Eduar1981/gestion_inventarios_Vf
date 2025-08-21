<?php
session_start(); // Asegúrate de iniciar la sesión

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'pdo.php';

    // Validar y sanitizar los datos obtenidos del formulario
    $contador_categoria = filter_input(INPUT_POST, 'contador_categoria', FILTER_VALIDATE_INT);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);

    // Verificar que los datos sean válidos
    if (!$contador_categoria || !$codigo || !$nombre) {
        $_SESSION['mensaje'] = 'Uno o más campos son inválidos.';
        header("Location: ver_categorias.php");
        exit();
    }

    // Preparar la consulta para actualizar los datos
    $stmt = $pdo->prepare("UPDATE `categorias` 
                           SET `codigo` = :codigo,
                               `nombre` = :nombre
                           WHERE `contador_categoria` = :contador_categoria");

    $stmt->bindParam(':codigo', $codigo);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':contador_categoria', $contador_categoria, PDO::PARAM_INT);

    // Ejecutar la consulta y verificar si fue exitosa
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Actualización realizada con éxito.";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar la categoría.";
    }

    // Redirigir de nuevo a la lista de categorías con el mensaje de éxito o error
    header("Location: ver_categorias.php");
    exit();
}
?>
