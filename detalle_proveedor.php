<?php
require 'pdo.php';
session_start();

// ✅ Encabezado correcto para JSON
header('Content-Type: application/json');

// ✅ NO permitir errores visibles (evita romper JSON)
ini_set('display_errors', 0);
error_reporting(0);

if (isset($_POST['cont_provee'])) {
    $cont_provee = $_POST['cont_provee'];

    // ✅ Incluye num_fact_comp si lo usas en el modal
    $stmt = $pdo->prepare("
        SELECT 
            cont_provee,
            nom_comercial,
            tipo_persona,
            tipo_documento,
            doc_proveedor,
            nom_representante,
            ape_representante,
            celular,
            tel_fijo,
            correo,
            direccion,
            ciudad
        FROM proveedores 
        WHERE cont_provee = :cont_provee 
        AND estado = 'activo'
    ");

    $stmt->bindParam(':cont_provee', $cont_provee, PDO::PARAM_INT);
    $stmt->execute();

    $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($proveedor) {
        echo json_encode($proveedor, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['error' => 'Proveedor no encontrado']);
    }
} else {
    echo json_encode(['error' => 'Falta el parámetro cont_provee']);
}