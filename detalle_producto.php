<?php
require 'pdo.php';
session_start();

// Asegura que la salida sea JSON
header('Content-Type: application/json');

// Habilita la depuración (solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['cont_producto'])) {
    $cont_producto = $_POST['cont_producto'];
    
    try {
        // Consulta para obtener los detalles del producto específico
        $statement = $pdo->prepare("
            SELECT 
                productos.cont_producto, 
                productos.codigo_producto, 
                productos.referencia, 
                productos.nombre, 
                productos.descripcion, 
                categorias.nombre AS categoria, 
                productos.precio_compra,
                productos.con_iva,
                productos.precio_compra, 
                productos.porcentaje_ganancia, 
                productos.cantidad, 
                productos.cantidad_minima, 
                productos.tiempo_registro, 
                productos.documento_operador, 
                productos.estado,
                precio_venta
            FROM 
                productos
            JOIN 
                categorias ON productos.categoria = categorias.contador_categoria
            WHERE 
                productos.cont_producto = :cont_producto
        ");

        $statement->bindParam(':cont_producto', $cont_producto, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_OBJ);

        
        
        // Verifica si se encontró el producto
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'Producto no encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Faltan parámetros']);
}
?>
