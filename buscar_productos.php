<?php
require 'pdo.php'; // Conexión a la base de datos
header('Content-Type: application/json');

if (!isset($_GET['query']) || strlen($_GET['query']) < 3) {
    echo json_encode([]);
    exit;
}

$buscar = "%" . strtolower($_GET['query']) . "%"; // Convertir la entrada a minúsculas

$stmt = $pdo->prepare("
    SELECT cont_producto, nombre, cantidad, precio_venta 
    FROM productos 
    WHERE estado = 'activo' 
    AND LOWER(nombre) LIKE ?
    LIMIT 5
");
$stmt->execute([$buscar]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);
?>
