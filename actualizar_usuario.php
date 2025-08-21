<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Incluye el archivo de conexión a la base de datos
    require 'pdo.php';

    // Validar y sanitizar los datos obtenidos del formulario
    $contador_usuarios = filter_input(INPUT_POST, 'codigo', FILTER_VALIDATE_INT);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
    $fecha_nacimiento = filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_STRING);
    $tipo_doc = filter_input(INPUT_POST, 'tipo_documento', FILTER_SANITIZE_STRING); // 'tipo_documento' en el formulario
    $documento = filter_input(INPUT_POST, 'documento', FILTER_SANITIZE_NUMBER_INT);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING); // Corregir el nombre
    $ciudad = filter_input(INPUT_POST, 'ciudad', FILTER_SANITIZE_STRING); // Cambiar a texto
    $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
    $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);

    // Validación adicional de los campos obligatorios
    if (!$contador_usuarios || !$nombre || !$apellido || !$fecha_nacimiento || !$tipo_doc || !$documento || !$correo || !$celular || !$ciudad || !$direccion || !$rol) {
        die('Uno o más campos son inválidos.');
    }

    // Actualizar los datos en la base de datos solo si los datos son válidos
    $stmt = $pdo->prepare("UPDATE `usuarios` 
                            SET `nombre` = :nombre, 
                                `apellido` = :apellido, 
                                `fecha_nacimiento` = :fecha_nacimiento, 
                                `tipo_doc` = :tipo_doc, 
                                `documento` = :documento, 
                                `correo` = :correo, 
                                `celular` = :celular, 
                                `ciudad` = :ciudad,
                                `direccion` = :direccion,
                                `rol` = :rol
                            WHERE `contador_usuarios` = :contador_usuarios");

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento); // Cambiar a fecha_nacimiento
    $stmt->bindParam(':tipo_doc', $tipo_doc); // Cambiar a tipo_doc
    $stmt->bindParam(':documento', $documento);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':celular', $celular); // Corregir el error tipográfico
    $stmt->bindParam(':ciudad', $ciudad);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':contador_usuarios', $contador_usuarios, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header('Location: ver_usuarios.php');
        exit();
    } else {
        echo "Error al actualizar los datos.";
    }
}
?>

