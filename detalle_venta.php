<?php
require 'pdo.php';
session_start();

// Asegura que la salida sea JSON
header('Content-Type: application/json');

if (!isset($_POST['cont_venta'])) {
    echo json_encode(['error' => 'Faltan parámetros']);
    exit();
}

$cont_venta = $_POST['cont_venta'];

try {
    // Consulta para obtener los detalles de la venta específico con nombre de producto y nombre del vendedor
    $statement = $pdo->prepare("
        SELECT 
            v.cont_venta, 
            v.descripcion AS venta_descripcion, 
            v.total_venta, 
            v.metodo_pago, 
            v.iva,

            -- ✅ Concatenar nombre y apellido del vendedor
            CONCAT(u.nombre, ' ', u.apellido) AS nombre_vendedor, 

            v.estado AS venta_estado,
            (
                SELECT SUM(dv.cantidad_productos)
                FROM detalle_venta dv
                WHERE dv.cont_venta = v.cont_venta
            ) AS total_cantidad_productos,

            d.cont_detalle_venta, 
            d.cont_producto, 
            p.nombre AS nombre_producto, 
            d.descripcion AS detalle_descripcion, 
            d.cantidad_productos, 
            (d.precio_unitario * d.cantidad_productos) AS sub_total, 
            d.precio_unitario, 
            DATE_FORMAT(d.tiempo_registro, '%d/%m/%Y') AS detalle_tiempo_registro
        FROM ventas v
        INNER JOIN detalle_venta d ON v.cont_venta = d.cont_venta
        INNER JOIN productos p ON d.cont_producto = p.cont_producto
        INNER JOIN usuarios u ON v.documento_operador = u.documento
        WHERE v.cont_venta = :cont_venta;

    ");

    $statement->bindParam(':cont_venta', $cont_venta, PDO::PARAM_INT);
    $statement->execute();
    
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Verifica si se encontró la venta
    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'Venta no encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>

