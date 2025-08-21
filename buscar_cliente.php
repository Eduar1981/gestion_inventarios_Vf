<?php 
require 'pdo.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 🔍 Sugerencias con LIKE para el buscador dinámico
if (isset($_GET['query'])) {
    $query = trim($_GET['query']);

    try {
        $stmt = $pdo->prepare("SELECT documento, nombre, apellido FROM clientes 
                               WHERE documento LIKE ? OR nombre LIKE ? 
                               ORDER BY nombre ASC LIMIT 5");
        $stmt->execute(["%$query%", "%$query%"]);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($clientes);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error en la consulta de sugerencias']);
    }
    exit;
}

// 🧾 Consulta exacta por documento
if (isset($_GET['documento'])) {
    $documento = $_GET['documento'];

    try {
        $stmt = $pdo->prepare("SELECT documento, nombre, apellido, celular, tipo_persona, tipo_documento, fecha_nacimiento, correo, ciudad, direccion, nom_comercial 
                               FROM clientes WHERE documento = ? LIMIT 1");
        $stmt->execute([$documento]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            echo json_encode($cliente);
        } else {
            echo json_encode(['error' => 'Cliente no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error en la consulta']);
    }
    exit;
}

// ❌ Ningún parámetro válido
echo json_encode(['error' => 'Parámetro no válido']);

