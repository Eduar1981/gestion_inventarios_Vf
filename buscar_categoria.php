<?php
require 'pdo.php';

header('Content-Type: application/json'); // Asegura que la respuesta se interprete como JSON

if (isset($_GET['q'])) {
    $searchTerm = $_GET['q'];
    try {
        // Consulta para buscar categorias que coincidan con el término
        $consulta = $pdo->prepare("
            SELECT contador_categoria, codigo, nombre 
            FROM categorias 
            WHERE estado = 'activo'
            AND (LOWER(nombre) LIKE LOWER(:search) OR LOWER(codigo) LIKE LOWER(:search))
            LIMIT 15
        ");
$consulta->bindValue(':search', '%' . $searchTerm . '%');

        $consulta->execute();
        $categorias = $consulta->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($categorias);  // Retorna las categorias en formato JSON
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al buscar la categoria: ' . $e->getMessage()]);
    }
}
?>
