<?php
require 'pdo.php';
header('Content-Type: application/json');

$query = $_GET['query'] ?? '';

if (strlen($query) <3) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "SELECT cont_provee, nom_comercial, doc_proveedor 
    FROM proveedores 
    WHERE estado = 'activo' 
    AND (
        LOWER(doc_proveedor) LIKE LOWER(:query) 
        OR LOWER(nom_comercial) LIKE LOWER(:query)
    )
    LIMIT 10";
    $stmt = $pdo->prepare($sql);    
    $stmt->execute(['query' => '%' . strtolower($query) . '%']);

    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($proveedores);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al buscar proveedores']);
}
?>