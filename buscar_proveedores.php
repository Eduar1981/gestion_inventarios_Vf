<?php
require 'pdo.php';
header('Content-Type: application/json');

// Obtener el término de búsqueda
$searchTerm = $_GET['q'] ?? '';

try {
    // Si el término de búsqueda tiene al menos 3 caracteres
    if (strlen($searchTerm) >= 3) {
        // Consulta mejorada que busca por varios campos
        $consulta = $pdo->prepare("
            SELECT cont_provee, nom_comercial, 
                   CONCAT(nom_representante, ' ', ape_representante) AS nombre, 
                   doc_proveedor, celular, correo 
            FROM proveedores 
            WHERE estado = 'activo' 
            AND (
                LOWER(nom_comercial) LIKE LOWER(:search) OR 
                LOWER(nom_representante) LIKE LOWER(:search) OR 
                LOWER(ape_representante) LIKE LOWER(:search) OR 
                LOWER(doc_proveedor) LIKE LOWER(:search)
            )
            ORDER BY cont_provee DESC
            LIMIT 15
        ");
        $consulta->bindValue(':search', '%' . $searchTerm . '%');
    } else {
        // Si no hay término de búsqueda, mostrar los proveedores iniciales
        $consulta = $pdo->prepare("
            SELECT cont_provee, nom_comercial, 
                   CONCAT(nom_representante, ' ', ape_representante) AS nombre, 
                   doc_proveedor, celular, correo 
            FROM proveedores 
            WHERE estado = 'activo' 
            ORDER BY cont_provee DESC
            LIMIT 15
        ");
    }

    $consulta->execute();
    $proveedores = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($proveedores); // Devolver resultados en formato JSON

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al buscar proveedores: ' . $e->getMessage()]);
}
?>
