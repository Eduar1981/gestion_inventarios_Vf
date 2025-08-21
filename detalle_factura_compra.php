<?php
require 'pdo.php';

header('Content-Type: application/json');

// Obtener el número de factura del POST
$num_fact = $_POST['num_fact_comp'] ?? '';

if (!$num_fact) {
    echo json_encode(['error' => 'No se recibió el número de factura']);
    exit;
}

// 1️⃣ Obtener datos de la factura y el proveedor
$sql_factura = "SELECT 
                    f.num_fact_comp,
                    f.fecha_compra,
                    f.fecha_pago_fact_comp,
                    f.precio_compra_total,
                    f.doc_proveedor,
                    p.nom_comercial
                FROM factura_compra_proveedores f
                INNER JOIN proveedores p ON f.doc_proveedor = p.doc_proveedor
                WHERE f.num_fact_comp = ?";

$stmt_factura = $pdo->prepare($sql_factura);
$stmt_factura->execute([$num_fact]);
$factura = $stmt_factura->fetch(PDO::FETCH_ASSOC);

if (!$factura) {
    echo json_encode(['error' => 'Factura no encontrada']);
    exit;
}

// 2️⃣ Obtener productos asociados a la factura
$sql_productos = "SELECT 
                    nombre, 
                    descripcion, 
                    precio_compra, 
                    cantidad
                FROM productos
                WHERE num_fact_comp = ?";

$stmt_productos = $pdo->prepare($sql_productos);
$stmt_productos->execute([$num_fact]);
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// 3️⃣ Preparar respuesta JSON
$response = [
    'factura' => [
        'num_fact_comp' => $factura['num_fact_comp'],
        'nom_comercial' => $factura['nom_comercial'],
        'fecha_compra' => $factura['fecha_compra'],
        'fecha_pago_fact_comp' => $factura['fecha_pago_fact_comp'],
        'precio_compra_total' => floatval($factura['precio_compra_total']),
    ],
    'productos' => []
];

// 4️⃣ Agregar productos (si hay)
foreach ($productos as $prod) {
    $response['productos'][] = [
        'nombre' => $prod['nombre'],
        'descripcion' => $prod['descripcion'],
        'precio_compra' => floatval($prod['precio_compra']),
        'cantidad' => intval($prod['cantidad']),
    ];
}

// 5️⃣ Enviar la respuesta
echo json_encode($response);
?>
