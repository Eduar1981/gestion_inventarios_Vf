<?php
require 'pdo.php';
header('Content-Type: application/json');

$searchTerm = $_GET['q'] ?? '';

try {
    if (strlen($searchTerm) >= 3) {
        $consulta = $pdo->prepare("
            SELECT contador_clientes, CONCAT(nombre, ' ', apellido) AS nombre, celular, correo 
            FROM clientes 
            WHERE estado = 'activo' 
              AND (LOWER(nombre) LIKE LOWER(:search) OR LOWER(apellido) LIKE LOWER(:search) OR LOWER(documento) LIKE LOWER(:search))
            ORDER BY contador_clientes DESC
            LIMIT 15
        ");
        $consulta->bindValue(':search', '%' . $searchTerm . '%');
    } else {
        // Sin búsqueda: clientes iniciales como al cargar la página
        $consulta = $pdo->prepare("
            SELECT contador_clientes, CONCAT(nombre, ' ', apellido) AS nombre, celular, correo 
            FROM clientes 
            WHERE estado = 'activo' 
            ORDER BY contador_clientes DESC
            LIMIT 15
        ");
    }

    $consulta->execute();
    $clientes = $consulta->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($clientes);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al buscar clientes: ' . $e->getMessage()]);
}
?>