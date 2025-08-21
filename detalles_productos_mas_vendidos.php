<?php
require 'pdo.php';

session_start();

header('Content-Type: application/json');

// 📌 Consulta de productos más vendidos en la quincena
$sql_top_quincena = "
    SELECT p.nombre, SUM(dv.cantidad_productos) AS total_vendidos
    FROM detalle_venta dv
    JOIN productos p ON dv.cont_producto = p.cont_producto
    WHERE dv.cont_venta IN (
        SELECT v.cont_venta FROM ventas v
        WHERE v.tiempo_registro >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)
    )
    GROUP BY p.nombre
    ORDER BY total_vendidos DESC
    LIMIT 5;
";
$productos_quincena = $pdo->query($sql_top_quincena)->fetchAll(PDO::FETCH_ASSOC);

// 📌 Consulta de productos más vendidos en el mes
$sql_top_mes = "
    SELECT p.nombre, SUM(dv.cantidad_productos) AS total_vendidos
    FROM detalle_venta dv
    JOIN productos p ON dv.cont_producto = p.cont_producto
    WHERE dv.cont_venta IN (
        SELECT v.cont_venta FROM ventas v
        WHERE v.tiempo_registro >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    )
    GROUP BY p.nombre
    ORDER BY total_vendidos DESC
    LIMIT 5;
";
$productos_mes = $pdo->query($sql_top_mes)->fetchAll(PDO::FETCH_ASSOC);

// 📌 Verificar qué tipo de consulta se requiere
$tipo = $_GET['tipo'] ?? '';

if ($tipo === 'quincena') {
    echo json_encode($productos_quincena);
} elseif ($tipo === 'mes') {
    echo json_encode($productos_mes);
} else {
    echo json_encode(['error' => 'Tipo de consulta no válido']);
}
?>
