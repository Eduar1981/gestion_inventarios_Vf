<?php
require 'pdo.php';

session_start();

header('Content-Type: application/json');

if (isset($_GET['autocomplete'])) {
    // Autocompletado
    $query = htmlspecialchars($_GET['autocomplete'], ENT_QUOTES, 'UTF-8');

    $sql = "SELECT cont_producto, codigo_producto, referencia, nombre, precio_venta 
            FROM productos 
            WHERE (LOWER(nombre) LIKE LOWER(:query) 
               OR LOWER(codigo_producto) LIKE LOWER(:query))
               AND estado = 'activo' 
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':query' => "%$query%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['productos' => $resultados]);
    exit;
} elseif (isset($_GET['producto'])) {
    // Búsqueda completa
    $producto = htmlspecialchars($_GET['producto'], ENT_QUOTES, 'UTF-8');

    $sql = "SELECT `cont_producto`, `codigo_producto`, `referencia`, `nombre`,`precio_compra`, `cantidad` FROM `productos` WHERE `codigo_producto` = :producto OR `nombre` = :producto AND estado = 'activo'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':producto' => $finca]);
    $productoDetalles = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($productoDetalles) {
        echo json_encode(['producto' => $productoDetalles]);
    } else {
        echo json_encode(['error' => 'No se encontró el producto.']);
    }
    exit;
}

?>