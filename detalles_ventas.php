<?php
require 'pdo.php';

header('Content-Type: application/json');

$periodo = $_GET['periodo'] ?? '';

// 📌 Determinar la condición de tiempo según la selección del usuario
switch ($periodo) {
    case 'dia':
        $condicion = "DATE(tiempo_registro) = CURDATE()";
        break;
    case 'semana':
        $condicion = "YEARWEEK(tiempo_registro, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'quincena':
        $condicion = "MONTH(tiempo_registro) = MONTH(CURDATE()) 
                      AND YEAR(tiempo_registro) = YEAR(CURDATE()) 
                      AND ((DAY(tiempo_registro) BETWEEN 1 AND 15) OR (DAY(tiempo_registro) > 15 AND DAY(CURDATE()) > 15))";
        break;
    case 'mes':
        $condicion = "MONTH(tiempo_registro) = MONTH(CURDATE()) AND YEAR(tiempo_registro) = YEAR(CURDATE())";
        break;
    default:
        echo json_encode(['error' => 'Período no válido']);
        exit();
}

// 📌 Nueva consulta: Obtener número de ventas, método de pago y total pagado
$sql = "SELECT metodo_pago, COUNT(cont_venta) AS total_ventas, SUM(total_venta) AS total_pagado
        FROM ventas WHERE $condicion AND estado = 'activo' 
        GROUP BY metodo_pago";

$stmt = $pdo->query($sql);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📌 Si no hay datos, enviar un mensaje en JSON
if (empty($resultados)) {
    echo json_encode(['error' => 'No hay ventas registradas en este período.']);
    exit();
}

// 📌 Formatear total_pagado antes de enviarlo en JSON
foreach ($resultados as &$fila) {
    $fila['total_pagado'] = number_format($fila['total_pagado'], 0, ',', '.'); // Sin decimales y con punto de miles
}

// 📌 Devolver los datos en formato JSON
echo json_encode($resultados);
?>


