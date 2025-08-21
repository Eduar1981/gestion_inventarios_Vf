<?php
require 'pdo.php';
header('Content-Type: application/json');

$searchTerm = $_GET['q'] ?? '';
$tipo_busqueda = 'general'; // tipo de búsqueda por defecto

try {
    if (strlen($searchTerm) >= 3) {
        // Normalizar separadores de fecha
        $searchTerm = str_replace('/', '-', $searchTerm);

        // Buscar por fecha completa dd-mm-aaaa
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $searchTerm)) {
            $tipo_busqueda = 'fecha_completa';
            $dateParts = explode('-', $searchTerm);
            if (count($dateParts) === 3) {
                $searchTerm = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // yyyy-mm-dd
            }

        // Buscar por mes y año mm-aaaa
        } elseif (preg_match('/^\d{2}-\d{4}$/', $searchTerm)) {
            $tipo_busqueda = 'mes_anio';
            [$mes, $anio] = explode('-', $searchTerm);
        }

        // Consulta para búsqueda por mes y año
        if ($tipo_busqueda === 'mes_anio') {
            $consulta = $pdo->prepare("
                SELECT f.cont_fact_compra, f.num_fact_comp, f.fecha_compra, f.fecha_pago_fact_comp,
                       p.nom_comercial, p.doc_proveedor
                FROM factura_compra_proveedores f
                INNER JOIN proveedores p ON f.doc_proveedor = p.doc_proveedor
                WHERE f.estado = 'activo'
                  AND (
                      (MONTH(f.fecha_compra) = :mes AND YEAR(f.fecha_compra) = :anio)
                      OR
                      (MONTH(f.fecha_pago_fact_comp) = :mes AND YEAR(f.fecha_pago_fact_comp) = :anio)
                  )
                ORDER BY f.cont_fact_compra DESC
                LIMIT 20
            ");
            $consulta->bindValue(':mes', $mes, PDO::PARAM_INT);
            $consulta->bindValue(':anio', $anio, PDO::PARAM_INT);

        // Consulta para búsqueda general o por fecha exacta
        } else {
            $consulta = $pdo->prepare("
                SELECT f.cont_fact_compra, f.num_fact_comp, f.fecha_compra, f.fecha_pago_fact_comp,
                       p.nom_comercial, p.doc_proveedor
                FROM factura_compra_proveedores f
                INNER JOIN proveedores p ON f.doc_proveedor = p.doc_proveedor
                WHERE f.estado = 'activo' AND (
                    LOWER(f.num_fact_comp) LIKE LOWER(:search) OR
                    DATE_FORMAT(f.fecha_compra, '%Y-%m-%d') LIKE :search OR
                    DATE_FORMAT(f.fecha_pago_fact_comp, '%Y-%m-%d') LIKE :search OR
                    LOWER(p.doc_proveedor) LIKE LOWER(:search) OR
                    LOWER(p.nom_comercial) LIKE LOWER(:search)
                )
                ORDER BY f.cont_fact_compra DESC
                LIMIT 20
            ");
            $consulta->bindValue(':search', '%' . $searchTerm . '%');
        }

    } else {
        // Mostrar últimas 20 facturas si no hay búsqueda
        $consulta = $pdo->prepare("
            SELECT f.cont_fact_compra, f.num_fact_comp, f.fecha_compra, f.fecha_pago_fact_comp, f.precio_compra_total,
                   p.nom_comercial, p.doc_proveedor
            FROM factura_compra_proveedores f
            INNER JOIN proveedores p ON f.doc_proveedor = p.doc_proveedor
            WHERE f.estado = 'activo'
            ORDER BY f.cont_fact_compra DESC
            LIMIT 20
        ");
    }

    $consulta->execute();
    $facturas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($facturas);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al buscar facturas: ' . $e->getMessage()]);
}
