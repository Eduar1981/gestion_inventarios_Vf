<?php
require 'pdo.php';

session_start(); // Asegúrate de iniciar la sesión

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    

    // Validar y sanitizar los datos obtenidos del formulario
    $cont_provee = filter_input(INPUT_POST, 'cont_provee', FILTER_VALIDATE_INT);
    $tipo_persona = filter_input(INPUT_POST, 'tipo_persona', FILTER_SANITIZE_STRING);
    $nom_comercial = filter_input(INPUT_POST, 'nom_comercial', FILTER_SANITIZE_STRING);
    $nom_representante = filter_input(INPUT_POST, 'nom_representante', FILTER_SANITIZE_STRING);
    $ape_representante = filter_input(INPUT_POST, 'ape_representante', FILTER_SANITIZE_STRING);
    $tipo_documento = filter_input(INPUT_POST, 'tipo_documento', FILTER_SANITIZE_STRING);
    $doc_proveedor = filter_input(INPUT_POST, 'doc_proveedor', FILTER_SANITIZE_NUMBER_INT);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING);
    $tel_fijo = filter_input(INPUT_POST, 'tel_fijo', FILTER_SANITIZE_STRING);
    $ciudad = filter_input(INPUT_POST, 'ciudad', FILTER_SANITIZE_STRING);
    $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);

    if (!$cont_provee || !$tipo_persona || !$nom_comercial || !$nom_representante || !$ape_representante || !$tipo_documento || !$doc_proveedor || !$correo || !$celular || !$tel_fijo || !$ciudad || !$direccion) {
        $_SESSION['mensaje'] = 'Uno o más campos son inválidos.';
        header("Location: ver_proveedores.php");
        exit();
    }

    $stmt = $pdo->prepare("UPDATE `proveedores` 
                            SET `nom_comercial` = :nom_comercial,
                                `tipo_persona` = :tipo_persona,
                                `tipo_documento` = :tipo_documento, 
                                `doc_proveedor` = :doc_proveedor, 
                                `nom_representante` = :nom_representante,
                                `ape_representante` = :ape_representante, 
                                `celular` = :celular, 
                                `tel_fijo` = :tel_fijo,
                                `correo` = :correo, 
                                `direccion` = :direccion,
                                `ciudad` = :ciudad
                                
                            WHERE `cont_provee` = :cont_provee");

    $stmt->bindParam(':nom_comercial', $nom_comercial);
    $stmt->bindParam(':tipo_persona', $tipo_persona);
    $stmt->bindParam(':tipo_documento', $tipo_documento);
    $stmt->bindParam(':doc_proveedor', $doc_proveedor);
    $stmt->bindParam(':nom_representante', $nom_representante);
    $stmt->bindParam(':ape_representante', $ape_representante);
    $stmt->bindParam(':celular', $celular);
    $stmt->bindParam(':tel_fijo', $tel_fijo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':ciudad', $ciudad);
    
    $stmt->bindParam(':cont_provee', $cont_provee, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Actualización realizada con éxito.";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el proveedor.";
    }

    $_SESSION['mensaje'] = "Error al intentar actualizar el proveedor.";
    header("Location: ver_proveedores.php");
    exit();
}

