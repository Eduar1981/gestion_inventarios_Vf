<?php
require 'pdo.php';

session_start(); // Asegúrarse de iniciar la sesión

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    

    // Validar y sanitizar los datos obtenidos del formulario
    $contador_clientes = filter_input(INPUT_POST, 'contador_clientes', FILTER_VALIDATE_INT);
    $tipo_persona = filter_input(INPUT_POST, 'tipo_persona', FILTER_SANITIZE_STRING);
    $tipo_documento = filter_input(INPUT_POST, 'tipo_documento', FILTER_SANITIZE_STRING);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
    $fecha_nacimiento = filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_STRING);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING);
    $ciudad = filter_input(INPUT_POST, 'ciudad', FILTER_SANITIZE_STRING);
    $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
    $nom_comercial = filter_input(INPUT_POST, 'nom_comercial', FILTER_SANITIZE_STRING);

    if (!$contador_clientes || !$tipo_persona || !$tipo_documento || !$nombre || !$apellido || !$fecha_nacimiento || !$correo || !$celular || !$ciudad || !$direccion || !$nom_comercial) {
        $_SESSION['mensaje'] = 'Uno o más campos son inválidos.';
        header("Location: ver_clientes.php");
        exit();
    }

    $stmt = $pdo->prepare("UPDATE `clientes` 
                            SET `tipo_persona` = :tipo_persona,
                                `tipo_documento` = :tipo_documento,  
                                `nombre` = :nombre,
                                `apellido` = :apellido, 
                                `fecha_nacimiento` = :fecha_nacimiento, 
                                `correo` = :correo, 
                                `celular` = :celular, 
                                `ciudad` = :ciudad,
                                `direccion` = :direccion,
                                `nom_comercial` = :nom_comercial
                            WHERE `contador_clientes` = :contador_clientes");

    $stmt->bindParam(':tipo_persona', $tipo_persona);
    $stmt->bindParam(':tipo_documento', $tipo_documento);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':celular', $celular);
    $stmt->bindParam(':ciudad', $ciudad);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':nom_comercial', $nom_comercial);
    $stmt->bindParam(':contador_clientes', $contador_clientes, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Actualización realizada con éxito.";
        header("Location: ver_clientes.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al actualizar el cliente.";
        header("Location: editar_cliente.php?contador_clientes=$contador_clientes");
        exit();
    }    
    
}
?>