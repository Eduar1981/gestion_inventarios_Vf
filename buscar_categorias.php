<?php
require 'pdo.php';

$termino = $_GET['q'] ?? '';

if (strlen($termino) >= 3) {
    $stmt = $pdo->prepare("
        SELECT contador_categoria, codigo, nombre 
        FROM categorias 
        WHERE (LOWER(codigo) LIKE LOWER(:busqueda) OR LOWER(nombre) LIKE LOWER(:busqueda)) 
        AND estado = 'activo' 
        LIMIT 10
    ");
    $buscar = "%$termino%";
    $stmt->bindParam(':busqueda', $buscar);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($categorias);
}
?>

