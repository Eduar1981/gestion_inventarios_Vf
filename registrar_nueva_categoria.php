<?php
require 'pdo.php';
session_start();

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['documento']) || !in_array($_SESSION['rol'], ['superadmin', 'administrador'])) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');

    if (!$codigo || !$nombre) {
        echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
        exit;
    }

    $documento_operador = $_SESSION['documento'];
    $estado = 'activo';
    $tiempo_registro = date('Y-m-d H:i:s');

    try {
        $checkSql = "SELECT COUNT(*) FROM categorias WHERE codigo = :codigo";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':codigo', $codigo);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con este código.']);
            exit;
        }

        $sql = "INSERT INTO categorias (codigo, nombre, tiempo_registro, documento_operador, estado)
                VALUES (:codigo, :nombre, :tiempo_registro, :documento_operador, :estado)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tiempo_registro', $tiempo_registro);
        $stmt->bindParam(':documento_operador', $documento_operador);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Categoría registrada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la categoría.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    exit;
}
?>
