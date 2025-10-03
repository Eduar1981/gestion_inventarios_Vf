<?php
require 'pdo.php';
session_start();
header('Content-Type: application/json');

if (!isset($_POST['cont_venta'])) {
    echo json_encode(['error' => 'Faltan parámetros']);
    exit();
}

$cont_venta = $_POST['cont_venta'];

try {
    $sql = " SELECT 
                v.cont_venta,
                v.descripcion AS venta_descripcion,
                v.total_venta,
                v.metodo_pago,
                v.iva,
                v.recibido,
                v.cambio,

                -- 🔹 Cliente
                v.documento AS documento_cliente,
                COALESCE(CONCAT(c.nombre, ' ', c.apellido), 'Consumidor Final') AS nombre_cliente,

                -- 🔹 Vendedor
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_vendedor,
                v.estado AS venta_estado,

                (SELECT SUM(dv.cantidad_productos) FROM detalle_venta dv WHERE dv.cont_venta = v.cont_venta) AS total_cantidad_productos,
                (SELECT COALESCE(SUM(dv.descuento_en_pesos),0) FROM detalle_venta dv WHERE dv.cont_venta = v.cont_venta) AS descuento_total,

                -- detalle
                d.cont_detalle_venta,
                d.cont_producto,
                p.nombre AS nombre_producto,
                d.descripcion AS detalle_descripcion,
                d.cantidad_productos,
                (d.precio_unitario * d.cantidad_productos) AS sub_total,
                d.precio_unitario,
                d.descuento_en_pesos   AS descuento_item,
                d.porcentaje_descuento AS porcentaje_descuento_item,
                DATE_FORMAT(d.tiempo_registro, '%d/%m/%Y') AS detalle_tiempo_registro
            FROM ventas v
            INNER JOIN detalle_venta d ON v.cont_venta = d.cont_venta
            INNER JOIN productos p     ON d.cont_producto = p.cont_producto
            INNER JOIN usuarios u      ON v.documento_operador = u.documento
            INNER JOIN clientes c      ON v.documento = c.documento   -- 👈 aquí
            WHERE v.cont_venta = :cont_venta
            ORDER BY d.cont_detalle_venta ASC
            ";


    $statement = $pdo->prepare($sql);
    $statement->bindParam(':cont_venta', $cont_venta, PDO::PARAM_INT);
    $statement->execute();
    
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result ?: ['error' => 'Venta no encontrada']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}

