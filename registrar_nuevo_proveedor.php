<?php
require 'pdo.php';

session_start();

header('Content-Type: application/json');

/* file_put_contents("log_proveedor.txt", print_r($_POST, true), FILE_APPEND); */



// Verificar sesión
if (!isset($_SESSION['documento']) || !in_array($_SESSION['rol'], ['superadmin', 'administrador'])) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_comercial = trim($_POST['nom_comercial'] ?? '');
    $tipo_persona = trim($_POST['tipo_persona'] ?? '');
    $nom_representante = trim($_POST['nom_representante'] ?? '');
    $ape_representante = trim($_POST['ape_representante'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $doc_proveedor = trim($_POST['documento'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $tel_fijo = trim($_POST['tel_fijo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if (!$nom_comercial || !$tipo_persona || !$nom_representante || !$ape_representante || !$tipo_documento || !$doc_proveedor || !$ciudad || !$direccion || !$celular || !$tel_fijo || !$correo) {
        echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
        exit;
    }

    $documento_operador = $_SESSION['documento'];
    $estado = 'activo';
    $tiempo_registro = date('Y-m-d H:i:s');

    try {
        $checkSql = "SELECT COUNT(*) FROM proveedores WHERE doc_proveedor = :doc_proveedor";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':doc_proveedor', $doc_proveedor);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un proveedor con ese mismo número de documento.']);
            exit;
        }

        $sql = "INSERT INTO proveedores 
                (nom_comercial, tipo_persona, tipo_documento, doc_proveedor, nom_representante, ape_representante, celular, tel_fijo, correo, direccion, ciudad, tiempo_registro, documento_operador, estado) 
                VALUES (:nom_comercial, :tipo_persona, :tipo_documento, :doc_proveedor, :nom_representante, :ape_representante, :celular,  :tel_fijo, :correo, :direccion, :ciudad, :tiempo_registro, :documento_operador, :estado)";
        $stmt = $pdo->prepare($sql);
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
        $stmt->bindParam(':tiempo_registro', $tiempo_registro);
        $stmt->bindParam(':documento_operador', $documento_operador);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Proveedor registrado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el proveedor.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    exit;
}
?>