<?php
require 'pdo.php';
header('Content-Type: application/json');

$searchTerm = $_GET['q'] ?? '';

try {
    if (strlen($searchTerm) >= 3) {
        $consulta = $pdo->prepare("
            SELECT contador_usuarios, CONCAT(nombre, ' ', apellido) AS nombre, celular, correo 
            FROM usuarios 
            WHERE estado = 'activo' 
              AND (LOWER(nombre) LIKE LOWER(:search) OR LOWER(apellido) LIKE LOWER(:search) OR LOWER(documento) LIKE LOWER(:search))
            ORDER BY contador_usuarios DESC
            LIMIT 15
        ");
        $consulta->bindValue(':search', '%' . $searchTerm . '%');
    } else {
        // Sin búsqueda: usuarios iniciales como al cargar la página
        $consulta = $pdo->prepare("
            SELECT contador_usuarios, CONCAT(nombre, ' ', apellido) AS nombre, celular, correo 
            FROM usuarios 
            WHERE estado = 'activo' 
            ORDER BY contador_usuarios DESC
            LIMIT 15
        ");
    }

    $consulta->execute();
    $usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al buscar el usuario: ' . $e->getMessage()]);
}
?>

