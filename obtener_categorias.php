<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'pdo.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? $_GET['query'] : '';

// Validar que haya texto para buscar
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Consulta que busca por nombre o código
$sql = "SELECT contador_categoria, codigo, nombre FROM categorias WHERE (codigo LIKE :query OR nombre LIKE :query) AND estado = 'activo'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => '%' . $query . '%']);

$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);


echo json_encode($categorias);
?>
