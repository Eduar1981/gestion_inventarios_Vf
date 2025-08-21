<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300); // Aumenta el tiempo de ejecución a 5 minutos

header('Content-Type: application/json');

include 'pdo.php';

// 📌 Función para verificar stock bajo
function checkLowStock($pdo) {
    $query = "SELECT cont_producto, nombre, cantidad, cantidad_minima 
              FROM productos 
              WHERE cantidad <= cantidad_minima AND estado = 'activo'";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 📌 Obtener productos con stock bajo
$lowStockProducts = checkLowStock($pdo);

if (!empty($lowStockProducts)) {
    error_log("📢 Se encontraron productos con stock bajo.");
}

// 📌 Enviar la respuesta en formato JSON
echo json_encode($lowStockProducts, JSON_PRETTY_PRINT);

?>

